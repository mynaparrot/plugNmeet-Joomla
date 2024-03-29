<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Plugnmeet Import Html View
 */
class PlugnmeetViewImport extends HtmlView
{
	protected $headerList;
	protected $hasPackage = false;
	protected $headers;
	protected $hasHeader = 0;
	protected $dataType;

	public function display($tpl = null)
	{		
		if ($this->getLayout() !== 'modal')
		{
			// Include helper submenu
			PlugnmeetHelper::addSubmenu('import');
		}

		$paths = new stdClass;
		$paths->first = '';
		$state = $this->get('state');

		$this->paths = &$paths;
		$this->state = &$state;
                // get global action permissions
		$this->canDo = PlugnmeetHelper::getActions('import');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}

		// get the session object
		$session = JFactory::getSession();
		// check if it has package
		$this->hasPackage 	= $session->get('hasPackage', false);
		$this->dataType 	= $session->get('dataType', false);
		if($this->hasPackage && $this->dataType)
		{
			$this->headerList 	= json_decode($session->get($this->dataType.'_VDM_IMPORTHEADERS', false),true);
			$this->headers 		= PlugnmeetHelper::getFileHeaders($this->dataType);
			// clear the data type
			$session->clear('dataType');
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_PLUGNMEET_IMPORT_TITLE'), 'upload');
		JHtmlSidebar::setAction('index.php?option=com_plugnmeet&view=import');

		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_plugnmeet');
		}

		// set help url for this view if found
		$this->help_url = PlugnmeetHelper::getHelpUrl('import');
		if (PlugnmeetHelper::checkString($this->help_url))
		{
			   JToolbarHelper::help('COM_PLUGNMEET_HELP_MANAGER', false, $this->help_url);
		}
	}
}
