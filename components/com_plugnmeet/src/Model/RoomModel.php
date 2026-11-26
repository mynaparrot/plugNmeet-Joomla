<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\plugNmeetConnect;
use Mynaparrot\Component\Plugnmeet\Site\Helper\PlugnmeetHelper as PlugnmeetSiteHelper;
use Mynaparrot\PlugnmeetProto\UserMetadata;

class RoomModel extends ItemModel
{
	public $_item;

	protected function populateState()
	{
		parent::populateState();

		$app = Factory::getApplication();
		$id  = $app->getInput()->getInt('id');
		$this->setState('room.id', $id);
	}

	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$roomId = $id ?: $this->getState('room.id');
			$table  = $this->getTable();
			if ($table && $table->load($roomId) && $table->state === 1)
			{
				$this->_item = PlugnmeetSiteHelper::getProperties($table);
			}
		}

		return $this->_item;
	}

	public function getTable($type = 'Room', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	public function login(int $id, string $name, string|null $password)
	{
		$user           = Factory::getApplication()->getIdentity();
		$output         = new \stdClass();
		$output->status = false;
		$output->url    = "";

		$assetName = sprintf("com_plugnmeet.room.%d", $id);
		if (empty($name))
		{
			$output->msg = Text::sprintf("COM_PLUGNMEET_ERROR_MESSAGE_REQUIRED_FIELD_MISSING", Text::_("COM_PLUGNMEET_FULL_NAME"));

			return $output;
		}
		if (empty($password) && !$user->authorise('join.passwordless', $assetName))
		{
			$output->msg = Text::sprintf("COM_PLUGNMEET_ERROR_MESSAGE_REQUIRED_FIELD_MISSING", Text::_("JGLOBAL_PASSWORD"));

			return $output;
		}

		if (!$this->_item)
		{
			$this->getItem($id);
		}

		if (!$this->_item)
		{
			$output->msg = Text::_('COM_PLUGNMEET_ITEM_DOESNT_EXIST');

			return $output;
		}

		if (strcmp($password, $this->_item->moderator_pass) === 0)
		{
			$isAdmin = true;
		}
		elseif (strcmp($password, $this->_item->attendee_pass) === 0)
		{
			$isAdmin = false;
		}
		elseif (empty($password))
		{
			// user don't need password, so will check the access level
			$isAdmin = $user->authorise('join.admin', $assetName);
		}
		else
		{
			$output->msg = Text::_("COM_PLUGNMEET_PASSWORD_WRONG");

			return $output;
		}
		$room_id       = $this->_item->room_id;
		$room_metadata = json_decode($this->_item->room_metadata, true);
		if (!is_array($room_metadata))
		{
			$room_metadata = array();
		}
		if (!isset($room_metadata['room_features']) || !is_array($room_metadata['room_features']))
		{
			$room_metadata['room_features'] = array();
		}

		$room_mode        = PlugnmeetHelper::getRoomMode($room_metadata);
		$enable_ai_agent  = PlugnmeetHelper::isAiAgentEnabled();
		$is_ai_agent_mode = $enable_ai_agent && $room_mode === 'ai_agent';

		// A controlled AI session is reserved for the selected registered users only.
		if ($is_ai_agent_mode)
		{
			$ai_agent     = isset($room_metadata['insights_features']['ai_agent_features']) && is_array($room_metadata['insights_features']['ai_agent_features'])
				? $room_metadata['insights_features']['ai_agent_features']
				: array();
			$session_mode = (int) ($ai_agent['session_mode'] ?? 2);

			if ($session_mode === 1)
			{
				if ((int) $user->id <= 0)
				{
					$output->msg = Text::_('COM_PLUGNMEET_AI_CONTROLLED_LOGIN_REQUIRED');

					return $output;
				}

				if (!$isAdmin)
				{
					$auto_session_config = isset($ai_agent['auto_session_config']) && is_array($ai_agent['auto_session_config'])
						? $ai_agent['auto_session_config']
						: array();
					$allowed_ids         = array_map(
						'strval',
						PlugnmeetHelper::sanitizeUserIds($auto_session_config['allowed_participant_ids'] ?? array())
					);

					if (!in_array((string) $user->id, $allowed_ids, true))
					{
						$output->msg = Text::_('COM_PLUGNMEET_AI_CONTROLLED_NOT_ALLOWED');

						return $output;
					}
				}
			}
		}

		$userId = $user->id;

		if (!$userId)
		{
			/** @var Session $session */
			$session = Factory::getContainer()->get(Session::class);
			$userId  = $session->getId();
		}

		try
		{
			$connect = new plugNmeetConnect();
			$res     = $connect->isRoomActive($room_id);
			if (!$res->getStatus())
			{
				$output->msg = $res->getMsg();

				return $output;
			}
			$isRoomActive = $res->getIsActive();
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();

			return $output;
		}

		// An autonomous AI session starts with the first allowed user, no human moderator needed.
		if (
			!$isRoomActive &&
			!$isAdmin &&
			!$is_ai_agent_mode &&
			isset($room_metadata['room_features']['moderator_join_first']) &&
			$room_metadata['room_features']['moderator_join_first'] == 1
		)
		{
			$output->msg = Text::_('COM_PLUGNMEET_MEETING_NOT_STARTED');

			return $output;
		}

		if (!$isRoomActive)
		{
			try
			{
				$xml        = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_plugnmeet/plugnmeet.xml');
				$extra_data = array(
					"platform"          => "joomla-" . JVERSION,
					"php-version"       => phpversion(),
					"component-version" => (string) $xml->version
				);
				$logoutUrl  = Uri::getInstance();
				$logoutUrl->setQuery("returned=true");

				$params = ComponentHelper::getParams('com_plugnmeet');
				if (!isset($room_metadata['copyright_conf']))
				{
					$copyrightDisplay = (int) $params->get('copyright_display', 0);
					$copyrightText    = trim((string) $params->get('copyright_text', ''));
					if ($copyrightDisplay === 1 && $copyrightText !== '')
					{
						$room_metadata['copyright_conf'] = array('display' => true, 'text' => $copyrightText);
					}
				}

				PlugnmeetHelper::prepareAiAgentFeatures($room_metadata, $params->toArray());

				$res = $connect->createRoom($room_id, $this->_item->room_title, $room_metadata, $this->_item->welcome_message, $logoutUrl->toString(), "", $this->_item->max_participants, 0, $extra_data);

				$isRoomActive = $res->getStatus();
				if (!$res->getStatus())
				{
					$output->msg = $res->getMsg();

					return $output;
				}
			}
			catch (\Exception $e)
			{
				$output->msg = $e->getMessage();

				return $output;
			}
		}

		if ($isRoomActive)
		{
			try
			{
				$userMetadata = null;
				if ((int) $user->id > 0)
				{
					// Links the plugNmeet user with the Joomla account (needed by AI agent sessions).
					$userMetadata = new UserMetadata();
					$userMetadata->setExUserId((string) $user->id);
				}

				$res = $connect->getJoinToken(
					$this->_item->room_id,
					$name,
					(string) $userId,
					$isAdmin,
					false,
					$userMetadata
				);
				if (!$res->getStatus())
				{
					$output->msg = $res->getMsg();

					return $output;
				}
				$output->status = true;
				$output->msg    = "success";
				$params         = ComponentHelper::getParams('com_plugnmeet');
				if ($params->get('client_load', 'remote') === 'redirect')
				{
					$serverUrl   = rtrim(trim((string) $params->get('plugnmeet_server_url', '')), '/');
					$output->url = $serverUrl . '/?access_token=' . $res->getToken();
				}
				else
				{
					$output->url = Route::_('index.php?option=com_plugnmeet&view=room&layout=conference&access_token=' . $res->getToken() . '&id=' . $id, false, 0, true);
				}

				return $output;
			}
			catch (\Exception $e)
			{
				$output->msg = $e->getMessage();
			}
		}

		return $output;
	}

	public function getRecordings($id, $from, $limit, $orderBy)
	{
		$output         = new \stdClass();
		$output->status = false;

		$assetName = sprintf("com_plugnmeet.room.%d", $id);
		$user      = Factory::getApplication()->getIdentity();
		if (!$user->authorise('recording.view', $assetName))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		if (!$this->_item)
		{
			$this->getItem($id);
		}

		if (!$this->_item)
		{
			$output->msg = Text::_('COM_PLUGNMEET_ITEM_DOESNT_EXIST');

			return $output;
		}
		try
		{
			$connect = new plugNmeetConnect();
			$res     = $connect->getRecordings(array($this->_item->room_id), null, $from, $limit, $orderBy);

			$output->status = $res->getStatus();
			$output->msg    = $res->getMsg();
			if ($output->status)
			{
				$output->result = $res->getResult()->serializeToJsonString();
			}
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		return $output;
	}

	public function getRecordingLink($id, $recordingId, $access)
	{
		$output         = new \stdClass();
		$output->status = false;

		$assetName = sprintf("com_plugnmeet.room.%d", $id);
		$user      = Factory::getApplication()->getIdentity();
		if (!$user->authorise($access, $assetName))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		if (!$this->_item)
		{
			$this->getItem($id);
		}

		if (!$this->_item)
		{
			$output->msg = Text::_('COM_PLUGNMEET_ITEM_DOESNT_EXIST');

			return $output;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->getRecordingInfo($recordingId);
		if (!$res->getStatus())
		{
			$output->msg = $res->getMsg();

			return $output;
		}

		// Ensure the recording belongs to the requested room.
		if ($res->getRecordingInfo()->getRoomId() !== $this->_item->room_id)
		{
			$output->msg = Text::_("COM_PLUGNMEET_RECORDING_NOT_MATCH_WITH_ROOM");

			return $output;
		}

		$res = $connect->getRecordingDownloadLink($recordingId);
		if (!$res->getStatus())
		{
			$output->msg = $res->getMsg();

			return $output;
		}

		$params         = ComponentHelper::getParams('com_plugnmeet');
		$output->status = $res->getStatus();
		$output->msg    = $res->getMsg();
		$serverUrl      = rtrim(trim((string) $params->get('plugnmeet_server_url', '')), '/');
		$output->url    = sprintf('%s/download/recording/%s', $serverUrl, $res->getToken());

		return $output;
	}

	public function deleteRecording($id, $recordingId)
	{
		$output         = new \stdClass();
		$output->status = false;

		$assetName = sprintf("com_plugnmeet.room.%d", $id);
		$user      = Factory::getApplication()->getIdentity();
		if (!$user->authorise("recording.delete", $assetName))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		if (!$this->_item)
		{
			$this->getItem($id);
		}

		if (!$this->_item)
		{
			$output->msg = Text::_('COM_PLUGNMEET_ITEM_DOESNT_EXIST');

			return $output;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->getRecordingInfo($recordingId);
		if (!$res->getStatus())
		{
			$output->msg = $res->getMsg();

			return $output;
		}

		// Ensure the recording belongs to the requested room.
		if ($res->getRecordingInfo()->getRoomId() !== $this->_item->room_id)
		{
			$output->msg = Text::_("COM_PLUGNMEET_RECORDING_NOT_MATCH_WITH_ROOM");

			return $output;
		}

		$res = $connect->deleteRecording($recordingId);
		if (!$res->getStatus())
		{
			$output->msg = $res->getMsg();

			return $output;
		}
		$output->status = true;
		$output->msg    = Text::_("COM_PLUGNMEET_ITEM_DELETED_SUCCESSFULLY");

		return $output;
	}
}
