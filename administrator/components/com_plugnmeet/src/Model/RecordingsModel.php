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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\plugNmeetConnect;

/**
 * Methods supporting a list of Recordings.
 *
 * @since  1.0.0
 */
class RecordingsModel extends BaseDatabaseModel
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
	 * Fetch the recordings of a room.
	 *
	 * @param   string  $roomId   The room id.
	 * @param   int     $from     The starting index for pagination.
	 * @param   int     $limit    The maximum number of recordings to return.
	 * @param   string  $orderBy  The order in which to sort the recordings (e.g. "DESC").
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	public function getRecordings(string $roomId, int $from, int $limit, string $orderBy)
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
			$output->msg = Text::_('COM_PLUGNMEET_RECORDINGS_SELECT_ROOM_REQUIRED');

			return $output;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->getRecordings(array($roomId), null, $from, $limit, $orderBy);

		$output->status = $res->getStatus();
		$output->msg    = $res->getMsg();
		if ($output->status)
		{
			$output->result = $res->getResult()->serializeToJsonString();
		}

		return $output;
	}

	/**
	 * Get the download link of a recording.
	 *
	 * @param   string  $recordingId  The recording id.
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	public function downloadRecording(string $recordingId)
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
		$res     = $connect->getRecordingDownloadLink($recordingId);

		$output->status = $res->getStatus();
		$output->msg    = $res->getMsg();
		if ($res->getStatus())
		{
			$params = ComponentHelper::getParams('com_plugnmeet');
			$output->url = rtrim($params->get('plugnmeet_server_url'), '/') . '/download/recording/' . $res->getToken();
		}

		return $output;
	}

	/**
	 * Delete a recording.
	 *
	 * @param   string  $recordingId  The recording id.
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	public function deleteRecording(string $recordingId)
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
		$res     = $connect->deleteRecording($recordingId);
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
	 * Merge selected recordings into a single recording.
	 *
	 * @param   string  $roomId      The room id.
	 * @param   array   $recordings  The list of recording ids to merge.
	 *
	 * @return  \stdClass  The output object.
	 *
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	public function mergeRecordings(string $roomId, array $recordings)
	{
		$output         = new \stdClass();
		$output->status = false;

		$user = Factory::getApplication()->getIdentity();
		if (!$user->authorise('core.manage', 'com_plugnmeet'))
		{
			$output->msg = Text::_('COM_PLUGNMEET_ERROR_MESSAGE_NOT_AUTHORISED');

			return $output;
		}

		if (count($recordings) < 2)
		{
			$output->msg = Text::_('COM_PLUGNMEET_RECORDINGS_MERGE_MIN');

			return $output;
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->mergeRecordings($roomId, $recordings);

		$output->status = $res->getStatus();
		$output->msg    = $res->getMsg();
		if ($res->getStatus())
		{
			$output->msg = !empty($res->getMsg()) ? $res->getMsg() : Text::_('COM_PLUGNMEET_RECORDINGS_MERGE_SUCCESS');
		}

		return $output;
	}
}
