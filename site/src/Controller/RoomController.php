<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Mynaparrot\Component\Plugnmeet\Site\Model\RoomModel;

/**
 * Room class.
 *
 * @since  1.6.0
 */
class RoomController extends BaseController
{
	/**
	 * @throws \Exception
	 */
	public function join()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken("post", false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$id       = $this->input->getInt("id", 0);
		$name     = $this->input->getString("name");
		$password = $this->input->getString("password");

		try
		{
			/** @var RoomModel $model */
			$model  = $this->getModel('Room', 'Site');
			$output = $model->login($id, $name, $password);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	public function getRecordings()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken("post", false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$id      = $this->input->getInt("id", 0);
		$from    = $this->input->getUint("from", 0);
		$limit   = $this->input->getUint("limit", 0);
		$orderBy = $this->input->getString("orderBy", "DESC");

		try
		{
			/** @var RoomModel $model */
			$model  = $this->getModel('Room', 'Site');
			$output = $model->getRecordings($id, $from, $limit, $orderBy);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	public function getRecordingLink()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken("post", false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$id          = $this->input->getInt("id", 0);
		$recordingId = $this->input->getString("recordingId");
		$access      = $this->input->getString("access", "recording.play");

		try
		{
			/** @var RoomModel $model */
			$model  = $this->getModel('Room', 'Site');
			$output = $model->getRecordingLink($id, $recordingId, $access);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	public function deleteRecording()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken("post", false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$id          = $this->input->getInt("id", 0);
		$recordingId = $this->input->getString("recordingId");

		try
		{
			/** @var RoomModel $model */
			$model  = $this->getModel('Room', 'Site');
			$output = $model->deleteRecording($id, $recordingId);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	private function commonJsonOutput(object $output)
	{
		header("Content-type:application/json");
		echo json_encode($output);
		jexit();
	}
}
