<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Mynaparrot\Component\Plugnmeet\Administrator\Model\RecordingsModel;

/**
 * Recordings list controller class.
 *
 * @since  1.0.0
 */
class RecordingsController extends BaseController
{
	/**
	 * Fetch recordings for a given room.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function getRecordings()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken('post', false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$roomId  = $this->input->getString('roomId');
		$from    = $this->input->getUint('from', 0);
		$limit   = $this->input->getUint('limit', 20);
		$orderBy = $this->input->getString('order_by', 'DESC');

		try
		{
			/** @var RecordingsModel $model */
			$model  = $this->getModel('Recordings', 'Administrator');
			$output = $model->getRecordings($roomId, $from, $limit, $orderBy);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	/**
	 * Get the download link for a recording.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function downloadRecording()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken('post', false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$recordingId = $this->input->getString('recordingId');

		try
		{
			/** @var RecordingsModel $model */
			$model  = $this->getModel('Recordings', 'Administrator');
			$output = $model->downloadRecording($recordingId);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	/**
	 * Delete a recording.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function deleteRecording()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken('post', false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$recordingId = $this->input->getString('recordingId');

		try
		{
			/** @var RecordingsModel $model */
			$model  = $this->getModel('Recordings', 'Administrator');
			$output = $model->deleteRecording($recordingId);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	/**
	 * Merge selected recordings into a single recording.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function mergeRecordings()
	{
		$output         = new \stdClass();
		$output->status = false;
		$output->msg    = Text::_('COM_PLUGNMEET_SOMETHING_WENT_WRONG');

		$ok = $this->checkToken('post', false);
		if (!$ok)
		{
			$output->msg = Text::_('JINVALID_TOKEN_NOTICE');
			$this->commonJsonOutput($output);
		}

		$roomId     = $this->input->getString('roomId');
		$recordings = $this->input->get('recordings', array(), 'array');

		try
		{
			/** @var RecordingsModel $model */
			$model  = $this->getModel('Recordings', 'Administrator');
			$output = $model->mergeRecordings($roomId, $recordings);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	/**
	 * Send the output as JSON and close the application.
	 *
	 * @param   object  $output  The output object.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function commonJsonOutput(object $output)
	{
		$this->app->setHeader('Content-Type', 'application/json');
		$this->app->sendHeaders();
		echo json_encode($output);
		$this->app->close();
	}
}
