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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\plugNmeetConnect;
use Mynaparrot\Component\Plugnmeet\Site\Helper\PlugnmeetHelper;

/**
 * Plugnmeet model.
 *
 * @since  1.0.0
 */
class RoomModel extends ItemModel
{
	public $_item;


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   1.0.0
	 *
	 */
	protected function populateState()
	{
		parent::populateState();

		$app = Factory::getApplication();
		$id  = $app->getInput()->getInt('id');
		$this->setState('room.id', $id);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws \Exception
	 */
	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$roomId = $id ?: $this->getState('room.id');
			$table  = $this->getTable();
			if ($table && $table->load($roomId) && $table->state === 1)
			{
				$this->_item = PlugnmeetHelper::getProperties($table);
			}
		}

		return $this->_item;
	}


	/**
	 * Get an instance of Table class
	 *
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Room', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Publish the element
	 **
	 * @throws \Exception
	 */
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
		$userId        = $user->id;

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

		if (!$isRoomActive)
		{
			try
			{
				$xml        = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_plugnmeet/plugnmeet.xml');
				$extra_data = json_encode(array(
					"platform"          => "joomla-" . JVERSION,
					"php-version"       => phpversion(),
					"component-version" => (string) $xml->version
				));
				$logoutUrl  = Uri::getInstance();
				$logoutUrl->setQuery("returned=true");

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
				$res = $connect->getJoinToken($room_id, $name, $userId, $isAdmin);
				if (!$res->getStatus())
				{
					$output->msg = $res->getMsg();

					return $output;
				}
				$output->status = true;
				$output->msg    = "success";
				$output->url    = Route::_('index.php?option=com_plugnmeet&view=room&layout=conference&access_token=' . $res->getToken() . '&id=' . $id, false, 0, true);

				return $output;
			}
			catch (\Exception $e)
			{
				$output->msg = $e->getMessage();
			}
		}

		return $output;
	}

	/**
	 * @throws \Exception
	 */
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

	/**
	 * @throws \Exception
	 */
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
		// verify to check if request was for correct room
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
		$output->url    = sprintf("%s/download/recording/%s", $params->get("plugnmeet_server_url"), $res->getToken());

		return $output;
	}

	/**
	 * @throws \Exception
	 */
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
		// verify to check if request was for correct room
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
