<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\AiSummaryHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\AnalyticsHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\plugNmeetConnect;
use Mynaparrot\PlugnmeetProto\RoomArtifactType;

class ArtifactsModel extends BaseDatabaseModel
{
	public function getRooms()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'room_id', 'room_title')))
			->from($db->quoteName('#__plugnmeet_rooms'))
			->where($db->quoteName('state') . ' = 1')
			->order($db->quoteName('room_title') . ' ASC');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getArtifacts(string $roomId, int $from, int $limit, string $orderBy)
	{
		$output         = new \stdClass();
		$output->status = false;

		$user = Factory::getApplication()->getIdentity();
		if (!$user->authorise('core.manage', 'com_plugnmeet'))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		if (empty($roomId))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ARTIFACTS_SELECT_ROOM_REQUIRED');

			return $output;
		}

		if ($limit < 1)
		{
			$limit = 20;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->getArtifacts(array($roomId), null, null, $from, $limit, $orderBy);

		$output->status = $res->getStatus();
		$output->msg    = $res->getMsg();
		if ($output->status)
		{
			$resultObj       = new \stdClass();
			$resultArtifacts = array();
			$page            = intdiv($from, $limit) + 1;

			foreach ($res->getResult()->getArtifactsList() as $artifact)
			{
				$userName = trim((string) ($artifact->getMetadata()?->getUserInfo()?->getName() ?? ''));
				$typeHtml = '<div class="pnm-artifact-type"><span>' . htmlspecialchars($this->formatTypeName($artifact->getType()), ENT_QUOTES, 'UTF-8') . '</span>';

				if ($userName !== '')
				{
					$typeHtml .= '<span class="pnm-artifact-user">' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . '</span>';
				}

				$typeHtml .= '</div>';

				$resultArtifacts[] = array(
					'artifact_id' => $artifact->getArtifactId(),
					'type'        => $typeHtml,
					'created'     => gmdate('Y-m-d H:i:s', strtotime($artifact->getCreated())),
					'view_url'    => Route::_('index.php?option=com_plugnmeet&view=artifacts&layout=details&artifact_id=' . urlencode($artifact->getArtifactId()) . '&room_id=' . urlencode($roomId) . '&paged=' . $page)
				);
			}

			$resultObj->artifactsList  = $resultArtifacts;
			$resultObj->totalArtifacts = $res->getResult()->getTotalArtifacts();

			$output->result = json_encode($resultObj);
		}

		return $output;
	}

	public function downloadArtifact(string $artifactId)
	{
		$output         = new \stdClass();
		$output->status = false;

		$user = Factory::getApplication()->getIdentity();
		if (!$user->authorise('core.manage', 'com_plugnmeet'))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->getArtifactDownloadToken($artifactId);

		$output->status = $res->getStatus();
		$output->msg    = $res->getMsg();
		if ($res->getStatus())
		{
			$params    = ComponentHelper::getParams('com_plugnmeet');
			$serverUrl = rtrim(trim((string) $params->get('plugnmeet_server_url', '')), '/');

			$output->url = $serverUrl . '/download/artifact/' . $res->getToken();
		}

		return $output;
	}

	public function deleteArtifact(string $artifactId)
	{
		$output         = new \stdClass();
		$output->status = false;

		$user = Factory::getApplication()->getIdentity();
		if (!$user->authorise('core.delete', 'com_plugnmeet'))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->deleteArtifact($artifactId);
		if (!$res->getStatus())
		{
			$output->msg = $res->getMsg();

			return $output;
		}

		$output->status = true;
		$output->msg    = Text::_('COM_PLUGNMEET_ITEM_DELETED_SUCCESSFULLY');

		return $output;
	}

	public function getArtifactDetails(string $artifactId, string $roomId, int $paged)
	{
		$output                         = new \stdClass();
		$output->status                 = false;
		$output->msg                    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');
		$output->artifactInfo           = null;
		$output->details                = array();
		$output->isFileBased            = false;
		$output->isAnalytics            = false;
		$output->isMeetingSummary       = false;
		$output->isAiSessionSummary     = false;
		$output->aiSummary              = null;
		$output->meetingSummaryContent  = '';
		$output->analytics              = null;
		$output->analyticsError         = null;
		$output->roomId                 = $roomId;
		$output->paged                  = $paged;
		$output->artifactId             = $artifactId;

		$user = Factory::getApplication()->getIdentity();
		if (!$user->authorise('core.manage', 'com_plugnmeet'))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->getArtifactInfo($artifactId);

		if (!$res->getStatus())
		{
			$output->msg = $res->getMsg();

			return $output;
		}

		/** @var \Mynaparrot\PlugnmeetProto\ArtifactInfo $info */
		$info     = $res->getArtifactInfo();
		$metadata = $info->getMetadata();

		$isAiSessionSummary = $info->getType() === RoomArtifactType::AI_AGENT_SESSION_SUMMARY;

		$output->status             = true;
		$output->msg                = $res->getMsg();
		$output->artifactInfo       = $info;
		$output->isFileBased        = $metadata !== null && $metadata->hasFileInfo();
		$output->isAnalytics        = $info->getType() === RoomArtifactType::MEETING_ANALYTICS;
		$output->isMeetingSummary   = $info->getType() === RoomArtifactType::MEETING_SUMMARY;
		$output->isAiSessionSummary = $isAiSessionSummary;

		$details   = array();
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACTS_ARTIFACT_ID'), 'value' => $info->getArtifactId());
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_ID'), 'value' => $info->getRoomId());
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACTS_TYPE'), 'value' => $this->formatTypeName($info->getType()));
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACTS_CREATED'), 'value' => gmdate('Y-m-d H:i:s', strtotime($info->getCreated())));

		if ($metadata !== null)
		{
			if ($metadata->hasUserInfo())
			{
				$userInfo = $metadata->getUserInfo();
				$userName = trim((string) $userInfo->getName());

				if ($userName !== '')
				{
					$details[] = array('label' => Text::_('JGLOBAL_FIELD_CREATED_BY_LABEL'), 'value' => $userName);
				}

				$userId = $userInfo->getExUserId() ?: $userInfo->getUserId();

				if ($userId !== '')
				{
					$details[] = array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_USER_ID'), 'value' => $userId);
				}
			}

			if ($metadata->hasFileInfo())
			{
				$fileInfo  = $metadata->getFileInfo();
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_FILE_SIZE'), 'value' => round($fileInfo->getFileSize() / 1024, 2) . ' KB');
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_MIME_TYPE'), 'value' => $fileInfo->getMimeType());
			}

			if ($metadata->hasTokenUsage())
			{
				$usage     = $metadata->getTokenUsage();
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_TOKEN_USAGE'), 'value' => $usage->getTotalTokens());
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_ESTIMATED_COST'), 'value' => number_format($usage->getTotalTokensEstimatedCost(), 4));
			}

			if ($metadata->hasDurationUsage())
			{
				$usage     = $metadata->getDurationUsage();
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_DURATION_USAGE'), 'value' => $usage->getDurationSec() . 's');
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_ESTIMATED_COST'), 'value' => number_format($usage->getDurationSecEstimatedCost(), 4));
			}

			if ($metadata->hasCharacterCountUsage())
			{
				$usage     = $metadata->getCharacterCountUsage();
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_CHARACTER_COUNT_USAGE'), 'value' => $usage->getTotalCharacters());
				$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACT_ESTIMATED_COST'), 'value' => number_format($usage->getTotalCharactersEstimatedCost(), 4));
			}
		}

		$output->details = $details;

		if ($output->isAnalytics)
		{
			try
			{
				$helper            = new AnalyticsHelper($artifactId);
				$output->analytics = $helper->getContextData();
			}
			catch (\Exception $e)
			{
				$output->analytics      = null;
				$output->analyticsError = $e->getMessage();
			}
		}

		if ($output->isMeetingSummary)
		{
			$output->meetingSummaryContent = $this->getMeetingSummaryContent($artifactId);
		}

		if ($isAiSessionSummary)
		{
			$output->aiSummary = $this->getAiSessionSummary($artifactId);
		}

		return $output;
	}

	private function getAiSessionSummary(string $artifactId): ?array
	{
		try
		{
			$helper = new AiSummaryHelper($artifactId);

			return $helper->getSummaryData();
		}
		catch (\Exception $e)
		{
			return null;
		}
	}

	public function generateAnalyticsFile(string $artifactId)
	{
		$user = Factory::getApplication()->getIdentity();
		if (!$user->authorise('core.manage', 'com_plugnmeet'))
		{
			throw new \Exception(Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED'));
		}

		$helper = new AnalyticsHelper($artifactId);

		return $helper->generateXlsxFile();
	}

	private function formatTypeName(int $type)
	{
		$name = RoomArtifactType::name($type);

		return ucwords(strtolower(str_replace('_', ' ', $name)));
	}

	private function getMeetingSummaryContent(string $artifactId)
	{
		$connect = new plugNmeetConnect();
		$res     = $connect->getArtifactDownloadToken($artifactId);

		if (!$res->getStatus())
		{
			return '';
		}

		$params    = ComponentHelper::getParams('com_plugnmeet');
		$serverUrl = rtrim(trim((string) $params->get('plugnmeet_server_url', '')), '/');
		$url       = $serverUrl . '/download/artifact/' . $res->getToken();

		$response = HttpFactory::getHttp()->get($url, array(), 60);
		$body     = $response->body;

		$body = preg_replace('~^Meeting Summary for:.*\R+\R+---\R+\R+~s', '', $body);
		$body = str_replace('h3>', 'h6>', $body);

		return $body;
	}
}
