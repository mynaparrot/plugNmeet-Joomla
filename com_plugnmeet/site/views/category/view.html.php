<?php
/**
 * @package 	plugNmeet
 * @subpackage	view.html.php
 * @version		1.0.7
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Plugnmeet View class for the Category
 */
class PlugnmeetViewCategory extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null)
	{		
		// get combined params of both component and menu
		$this->app = JFactory::getApplication();
		$this->params = $this->app->getParams();
		$this->menu = $this->app->getMenu()->getActive();
		// get the user object
		$this->user = JFactory::getUser();
		// Initialise variables.
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		
		/***[JCBGUI.site_view.php_jview_display.30.$$$$]***/
		$this->displayPageTitle();/***[/JCBGUI$$$$]***/
		

		// Set the toolbar
		$this->addToolBar();

		// set the document
		$this->_prepareDocument();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode(PHP_EOL, $errors), 500);
		}

		parent::display($tpl);
	}


/***[JCBGUI.site_view.php_jview.30.$$$$]***/
    public function displayPageTitle()
    {
        if (preg_match("/category/", $this->menu->link)) {
            // it's a menu item so don't need to do further
            return;
        }

        $db = JFactory::getDbo();
        $id = $this->app->input->getInt("id", 0);
        $query = $db->getQuery(true);
        $query->select(array('title'))
            ->from($db->qn('#__categories'))
            ->where("id = " . $db->q($id));
        $db->setQuery($query);

        $title = $db->loadResult();
        JFactory::getDocument()->setTitle($title);
    }/***[/JCBGUI$$$$]***/


	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{

		// always make sure jquery is loaded.
		JHtml::_('jquery.framework');
		// Load the header checker class.
		require_once( JPATH_COMPONENT_SITE.'/helpers/headercheck.php' );
		// Initialize the header checker.
		$HeaderCheck = new plugnmeetHeaderCheck;
		// load the meta description
		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		// load the key words if set
		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		// check the robot params
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		// add the document default css file
		$this->document->addStyleSheet(JURI::root(true) .'/components/com_plugnmeet/assets/css/category.css', (PlugnmeetHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		
		// set help url for this view if found
		$this->help_url = PlugnmeetHelper::getHelpUrl('category');
		if (PlugnmeetHelper::checkString($this->help_url))
		{
			JToolbarHelper::help('COM_PLUGNMEET_HELP_MANAGER', false, $this->help_url);
		}
		// now initiate the toolbar
		$this->toolbar = JToolbar::getInstance();
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var, $sorten = false, $length = 40)
	{
		// use the helper htmlEscape method instead.
		return PlugnmeetHelper::htmlEscape($var, $this->_charset, $sorten, $length);
	}
}
