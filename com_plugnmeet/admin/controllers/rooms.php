<?php
/**
 * @package 	plugNmeet
 * @subpackage	rooms.php
 * @version		1.0.0
 * @build		26th February, 2022
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
 * Rooms Controller
 */
class PlugnmeetControllerRooms extends JControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_PLUGNMEET_ROOMS';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Room', $prefix = 'PlugnmeetModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function exportData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if export is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('room.export', 'com_plugnmeet') && $user->authorise('core.export', 'com_plugnmeet'))
		{
			// Get the input
			$input = JFactory::getApplication()->input;
			$pks = $input->post->get('cid', array(), 'array');
			// Sanitize the input
			$pks = ArrayHelper::toInteger($pks);
			// Get the model
			$model = $this->getModel('Rooms');
			// get the data to export
			$data = $model->getExportData($pks);
			if (PlugnmeetHelper::checkArray($data))
			{
				// now set the data to the spreadsheet
				$date = JFactory::getDate();
				PlugnmeetHelper::xls($data,'Rooms_'.$date->format('jS_F_Y'),'Rooms exported ('.$date->format('jS F, Y').')','rooms');
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_PLUGNMEET_EXPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_plugnmeet&view=rooms', false), $message, 'error');
		return;
	}


	public function importData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if import is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('room.import', 'com_plugnmeet') && $user->authorise('core.import', 'com_plugnmeet'))
		{
			// Get the import model
			$model = $this->getModel('Rooms');
			// get the headers to import
			$headers = $model->getExImPortHeaders();
			if (PlugnmeetHelper::checkObject($headers))
			{
				// Load headers to session.
				$session = JFactory::getSession();
				$headers = json_encode($headers);
				$session->set('room_VDM_IMPORTHEADERS', $headers);
				$session->set('backto_VDM_IMPORT', 'rooms');
				$session->set('dataType_VDM_IMPORTINTO', 'room');
				// Redirect to import view.
				$message = JText::_('COM_PLUGNMEET_IMPORT_SELECT_FILE_FOR_ROOMS');
				$this->setRedirect(JRoute::_('index.php?option=com_plugnmeet&view=import', false), $message);
				return;
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_PLUGNMEET_IMPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_plugnmeet&view=rooms', false), $message, 'error');
		return;
	}


/***[JCBGUI.admin_view.php_controller_list.110.$$$$]***/
    public function updateClient()
    {
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
        $output = $this->getModel('rooms')->doUpdateClient();

        $this->setRedirect(JRoute::_('index.php?option=com_plugnmeet&view=rooms', false), $output->msg, $output->status ? "success" : "error");
    }/***[/JCBGUI$$$$]***/

}
