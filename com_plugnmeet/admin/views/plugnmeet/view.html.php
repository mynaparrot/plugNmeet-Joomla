<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');



/**
 * Plugnmeet View class
 */
class PlugnmeetViewPlugnmeet extends JViewLegacy
{
	/**
	 * View display method
	 * @return void
	 */
	function display($tpl = null)
	{
		// Assign data to the view
		$this->icons			= $this->get('Icons');
		$this->contributors		= PlugnmeetHelper::getContributors();
		
		// get the manifest details of the component
		$this->manifest = PlugnmeetHelper::manifest();
		
		// Set the toolbar
		$this->addToolBar();
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		$canDo = PlugnmeetHelper::getActions('plugnmeet');
		JToolBarHelper::title(JText::_('COM_PLUGNMEET_DASHBOARD'), 'grid-2');

		// set help url for this view if found
		$this->help_url = PlugnmeetHelper::getHelpUrl('plugnmeet');
		if (PlugnmeetHelper::checkString($this->help_url))
		{
			JToolbarHelper::help('COM_PLUGNMEET_HELP_MANAGER', false, $this->help_url);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_plugnmeet');
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		
		// add dashboard style sheets
		$document->addStyleSheet(JURI::root() . "administrator/components/com_plugnmeet/assets/css/dashboard.css");
		
		// set page title
		$document->setTitle(JText::_('COM_PLUGNMEET_DASHBOARD'));
		
		// add manifest to page JavaScript
		$document->addScriptDeclaration("var manifest = jQuery.parseJSON('" . json_encode($this->manifest) . "');", "text/javascript");
	}
}
