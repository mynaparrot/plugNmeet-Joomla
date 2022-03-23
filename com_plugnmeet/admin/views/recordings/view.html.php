<?php
/**
 * @package 	plugNmeet
 * @subpackage	view.html.php
 * @version		1.0.1
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Plugnmeet View class for the Recordings
 */
class PlugnmeetViewRecordings extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null)
	{
		// get component params
		$this->params = JComponentHelper::getParams('com_plugnmeet');
		// get the application
		$this->app = JFactory::getApplication();
		// get the user object
		$this->user	= JFactory::getUser();
		// get global action permissions
		$this->canDo = PlugnmeetHelper::getActions('recordings');
		// Initialise variables.
		$this->items = $this->get('Items');
		
		/***[JCBGUI.custom_admin_view.php_jview_display.1.$$$$]***/
		        PlugnmeetHelper::addSubmenu('recordings');
		        $this->sidebar = JHtmlSidebar::render();/***[/JCBGUI$$$$]***/
		

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			// add the tool bar
			$this->addToolBar();
		}

		// set the document
		$this->setDocument();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode(PHP_EOL, $errors), 500);
		}

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function setDocument()
	{

		// always make sure jquery is loaded.
		JHtml::_('jquery.framework');
		// Load the header checker class.
		require_once( JPATH_COMPONENT_ADMINISTRATOR.'/helpers/headercheck.php' );
		// Initialize the header checker.
		$HeaderCheck = new plugnmeetHeaderCheck;
		// add the document default css file
		$this->document->addStyleSheet(JURI::root(true) .'/administrator/components/com_plugnmeet/assets/css/recordings.css', (PlugnmeetHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// hide the main menu
		$this->app->input->set('hidemainmenu', true);
		// add title to the page
		JToolbarHelper::title(JText::_('COM_PLUGNMEET_RECORDINGS'),'joomla');
		// add cpanel button
		JToolBarHelper::custom('recordings.dashboard', 'grid-2', '', 'COM_PLUGNMEET_DASH', false);

		// set help url for this view if found
		$help_url = PlugnmeetHelper::getHelpUrl('recordings');
		if (PlugnmeetHelper::checkString($help_url))
		{
			JToolbarHelper::help('COM_PLUGNMEET_HELP_MANAGER', false, $help_url);
		}

		// add the options comp button
		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_plugnmeet');
		}
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		// use the helper htmlEscape method instead.
		return PlugnmeetHelper::htmlEscape($var, $this->_charset);
	}
}
