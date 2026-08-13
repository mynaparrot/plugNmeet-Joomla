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
use Mynaparrot\Component\Plugnmeet\Administrator\Model\ArtifactsModel;

/**
 * Artifacts list controller class.
 *
 * @since  1.0.0
 */
class ArtifactsController extends BaseController
{
	/**
	 * Fetch artifacts for a given room.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function getArtifacts()
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
			/** @var ArtifactsModel $model */
			$model  = $this->getModel('Artifacts', 'Administrator');
			$output = $model->getArtifacts($roomId, $from, $limit, $orderBy);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	/**
	 * Get the download link for an artifact.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function downloadArtifact()
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

		$artifactId = $this->input->getString('artifact_id');

		try
		{
			/** @var ArtifactsModel $model */
			$model  = $this->getModel('Artifacts', 'Administrator');
			$output = $model->downloadArtifact($artifactId);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	/**
	 * Delete an artifact.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function deleteArtifact()
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

		$artifactId = $this->input->getString('artifact_id');

		try
		{
			/** @var ArtifactsModel $model */
			$model  = $this->getModel('Artifacts', 'Administrator');
			$output = $model->deleteArtifact($artifactId);
		}
		catch (\Exception $e)
		{
			$output->msg = $e->getMessage();
		}

		$this->commonJsonOutput($output);
	}

	/**
	 * Generate and stream the analytics Excel file for an artifact.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function downloadAnalytics()
	{
		if (!$this->checkToken())
		{
			return;
		}

		$artifactId = $this->input->getString('artifact_id');

		try
		{
			/** @var ArtifactsModel $model */
			$model = $this->getModel('Artifacts', 'Administrator');
			$file  = $model->generateAnalyticsFile($artifactId);

			$this->app->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$this->app->setHeader('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"');
			$this->app->setHeader('Content-Length', filesize($file['path']));
			$this->app->sendHeaders();

			readfile($file['path']);
			@unlink($file['path']);
			$this->app->close();
		}
		catch (\Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect('index.php?option=com_plugnmeet&view=artifacts');

			return;
		}
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
