<?php
/**
 * @package 	plugNmeet
 * @subpackage	room.php
 * @version		1.0.2
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Plugnmeet Room Controller
 */
class PlugnmeetControllerRoom extends JControllerForm
{
	/**
	 * Current or most recently performed task.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _task.
	 */
	protected $task;

	public function __construct($config = array())
	{
		$this->view_list = 'categories'; // safeguard for setting the return view listing to the default site view.
		parent::__construct($config);
	}


/***[JCBGUI.site_view.php_controller.28.$$$$]***/
    public function loginToPNM()
    {
        $output = new stdClass();
        $output->status = false;
        $output->msg = "error";
        $output->token = "";

        if (!JSession::checkToken()) {
            $output->msg = JText::_("COM_PLUGNMEET_JOOMLA_TOKEN_ERROR");
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($output);
            jexit();
        }

        $id = $this->input->get("id", 0);
        $name = $this->input->get("name", "");
        $password = $this->input->get("password", "");

        $output = $this->getModel('room')->doLoginToPNM($id, $name, $password);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
        jexit();
    }/***[/JCBGUI$$$$]***/


	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// to insure no other tampering
		return false;
	}

        /**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		// to insure no other tampering
		return false;
	}

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowSave($data, $key = 'id')
	{
		// to insure no other tampering
		return false;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
	}
}
