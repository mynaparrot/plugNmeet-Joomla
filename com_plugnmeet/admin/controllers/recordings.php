<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

/**
 * Recordings Admin Controller
 */
class PlugnmeetControllerRecordings extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_PLUGNMEET_RECORDINGS';

	/**
	 * Proxy for getModel.
	 * @since	2.5
	 */
	public function getModel($name = 'Recordings', $prefix = 'PlugnmeetModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	public function dashboard()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_plugnmeet', false));
		return;
	}


/***[JCBGUI.custom_admin_view.php_controller.1.$$$$]***/
    public function getRecordings()
    {
        $token = JSession::getFormToken();
        $input = $this->input;
        $call_token = $input->get('token', 0, 'ALNUM');

        $output = new stdClass();
        $output->status = false;
        $output->msg = JText::_("COM_PLUGNMEET_TOKEN_DID_NOT_MATCHED");
        $output->result = [];

        if ($token == $call_token) {
            $roomId = $input->get('roomId', "", 'RAW');
            $from = $input->get('from', 0, 'INT');
            $limit = $input->get('limit', 20, 'INT');
            $orderBy = $input->get('order_by', "DESC", 'STRING');

            $output = $this->getModel('recordings')->doGetRecordings($roomId, $from, $limit, $orderBy);
        }

        header('Content-Type: application/json');
        echo json_encode($output);
        jexit();
    }

    public function downloadRecording()
    {
        $token = JSession::getFormToken();
        $input = $this->input;
        $call_token = $input->get('token', 0, 'ALNUM');

        $output = new stdClass();
        $output->status = false;
        $output->msg = JText::_("COM_PLUGNMEET_TOKEN_DID_NOT_MATCHED");

        if ($token == $call_token) {
            $recordingId = $input->get('recordingId', "", 'RAW');
            $output = $this->getModel('recordings')->doDownloadRecording($recordingId);
        }

        if (!$output->status) {
            $this->setRedirect(JRoute::_('index.php?option=com_plugnmeet&view=recordings', false), $output->msg, "error");
        } else {
            $this->setRedirect($output->url);
        }
    }

    public function deleteRecording()
    {
        $token = JSession::getFormToken();
        $input = $this->input;
        $call_token = $input->get('token', 0, 'ALNUM');

        $output = new stdClass();
        $output->status = false;
        $output->msg = JText::_("COM_PLUGNMEET_TOKEN_DID_NOT_MATCHED");

        if ($token == $call_token) {
            $recordingId = $input->get('recordingId', "", 'RAW');
            $output = $this->getModel('recordings')->doDeleteRecording($recordingId);
        }

        $this->setRedirect(JRoute::_('index.php?option=com_plugnmeet&view=recordings', false), $output->msg, $output->status ? "success" : "error");
    }/***[/JCBGUI$$$$]***/

}
