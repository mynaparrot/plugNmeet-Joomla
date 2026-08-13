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
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\AnalyticsHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\plugNmeetConnect;
use Mynaparrot\PlugnmeetProto\RoomArtifactType;

/**
 * Methods supporting a list of Artifacts.
 *
 * @since  1.0.0
 */
class ArtifactsModel extends BaseDatabaseModel
{
	/**
	 * Get the list of published rooms to select from.
	 *
	 * @return  array  An array of room objects on success, false on failure.
	 *
	 * @since   1.0.0
	 */
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

	/**
	 * Fetch the artifacts of a room.
	 *
	 * @param   string  $roomId   The room id.
	 * @param   int     $from     The starting index for pagination.
	 * @param   int     $limit    The maximum number of artifacts to return.
	 * @param   string  $orderBy  The order in which to sort the artifacts (e.g. "DESC").
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
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
				$resultArtifacts[] = array(
					'artifact_id' => $artifact->getArtifactId(),
					'type'        => $this->formatTypeName($artifact->getType()),
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

	/**
	 * Get the download link of an artifact.
	 *
	 * @param   string  $artifactId  The artifact id.
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
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
			$params      = ComponentHelper::getParams('com_plugnmeet');
			$output->url = rtrim($params->get('plugnmeet_server_url'), '/') . '/download/artifact/' . $res->getToken();
		}

		return $output;
	}

	/**
	 * Delete an artifact.
	 *
	 * @param   string  $artifactId  The artifact id.
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
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

	/**
	 * Get the details of an artifact.
	 *
	 * @param   string  $artifactId  The artifact id.
	 * @param   string  $roomId      The room id.
	 * @param   int     $paged       The current page number.
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
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

		$output->status          = true;
		$output->msg             = $res->getMsg();
		$output->artifactInfo    = $info;
		$output->isFileBased     = $metadata !== null && $metadata->hasFileInfo();
		$output->isAnalytics     = $info->getType() === RoomArtifactType::MEETING_ANALYTICS;
		$output->isMeetingSummary = $info->getType() === RoomArtifactType::MEETING_SUMMARY;

		// Build the details table.
		$details   = array();
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACTS_ARTIFACT_ID'), 'value' => $info->getArtifactId());
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_ID'), 'value' => $info->getRoomId());
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACTS_TYPE'), 'value' => $this->formatTypeName($info->getType()));
		$details[] = array('label' => Text::_('COM_PLUGNMEET_ARTIFACTS_CREATED'), 'value' => gmdate('Y-m-d H:i:s', strtotime($info->getCreated())));

		if ($metadata !== null)
		{
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

		return $output;
	}

	/**
	 * Generate the analytics Excel file for an artifact.
	 *
	 * @param   string  $artifactId  The artifact id.
	 *
	 * @return  array  An array containing the generated file path and name.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
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

	/**
	 * Format an artifact type into a human readable name.
	 *
	 * @param   int  $type  The artifact type value.
	 *
	 * @return  string  The human readable type name.
	 *
	 * @since   1.0.0
	 */
	private function formatTypeName(int $type)
	{
		$name = RoomArtifactType::name($type);

		return ucwords(strtolower(str_replace('_', ' ', $name)));
	}

	/**
	 * Fetch and clean the meeting summary content of an artifact.
	 *
	 * @param   string  $artifactId  The artifact id.
	 *
	 * @return  string  The meeting summary HTML.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	private function getMeetingSummaryContent(string $artifactId)
	{
		$connect = new plugNmeetConnect();
		$res     = $connect->getArtifactDownloadToken($artifactId);

		if (!$res->getStatus())
		{
			return '';
		}

		$params = ComponentHelper::getParams('com_plugnmeet');
		$url    = rtrim($params->get('plugnmeet_server_url'), '/') . '/download/artifact/' . $res->getToken();

		$response = HttpFactory::getHttp()->get($url, array(), 60);
		$body     = $response->body;

		$body = preg_replace('~^Meeting Summary for:.*\R+\R+---\R+\R+~s', '', $body);
		$body = str_replace('h3>', 'h6>', $body);

		return $body;
	}
}
