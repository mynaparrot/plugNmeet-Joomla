<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Plugnmeet Html View class for the Rooms
 */
class PlugnmeetViewRooms extends HtmlView
{
	/**
	 * Rooms view display method
	 * @return void
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			// Include helper submenu
			PlugnmeetHelper::addSubmenu('rooms');
		}

		// Assign data to the view
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->user = JFactory::getUser();
		// Load the filter form from xml.
		$this->filterForm = $this->get('FilterForm');
		// Load the active filters.
		$this->activeFilters = $this->get('ActiveFilters');
		// Add the list ordering clause.
		$this->listOrder = $this->escape($this->state->get('list.ordering', 'a.id'));
		$this->listDirn = $this->escape($this->state->get('list.direction', 'DESC'));
		$this->saveOrder = $this->listOrder == 'a.ordering';
		// set the return here value
		$this->return_here = urlencode(base64_encode((string) JUri::getInstance()));
		// get global action permissions
		$this->canDo = PlugnmeetHelper::getActions('room');
		$this->canEdit = $this->canDo->get('core.edit');
		$this->canState = $this->canDo->get('core.edit.state');
		$this->canCreate = $this->canDo->get('core.create');
		$this->canDelete = $this->canDo->get('core.delete');
		$this->canBatch = $this->canDo->get('core.batch');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
			// load the batch html
			if ($this->canCreate && $this->canEdit && $this->canState)
			{
				$this->batchDisplay = JHtmlBatch_::render();
			}
		}
		
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
		JToolBarHelper::title(JText::_('COM_PLUGNMEET_ROOMS'), 'joomla');
		JHtmlSidebar::setAction('index.php?option=com_plugnmeet&view=rooms');
		JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

		if ($this->canCreate)
		{
			JToolBarHelper::addNew('room.add');
		}

		// Only load if there are items
		if (PlugnmeetHelper::checkArray($this->items))
		{
			if ($this->canEdit)
			{
				JToolBarHelper::editList('room.edit');
			}

			if ($this->canState)
			{
				JToolBarHelper::publishList('rooms.publish');
				JToolBarHelper::unpublishList('rooms.unpublish');
				JToolBarHelper::archiveList('rooms.archive');

				if ($this->canDo->get('core.admin'))
				{
					JToolBarHelper::checkin('rooms.checkin');
				}
			}

			// Add a batch button
			if ($this->canBatch && $this->canCreate && $this->canEdit && $this->canState)
			{
				// Get the toolbar object instance
				$bar = JToolBar::getInstance('toolbar');
				// set the batch button name
				$title = JText::_('JTOOLBAR_BATCH');
				// Instantiate a new JLayoutFile instance and render the batch button
				$layout = new JLayoutFile('joomla.toolbar.batch');
				// add the button to the page
				$dhtml = $layout->render(array('title' => $title));
				$bar->appendButton('Custom', $dhtml, 'batch');
			}

			if ($this->state->get('filter.published') == -2 && ($this->canState && $this->canDelete))
			{
				JToolbarHelper::deleteList('', 'rooms.delete', 'JTOOLBAR_EMPTY_TRASH');
			}
			elseif ($this->canState && $this->canDelete)
			{
				JToolbarHelper::trash('rooms.trash');
			}

			if ($this->canDo->get('core.export') && $this->canDo->get('room.export'))
			{
				JToolBarHelper::custom('rooms.exportData', 'download', '', 'COM_PLUGNMEET_EXPORT_DATA', true);
			}
		}
		if ($this->user->authorise('room.update_client', 'com_plugnmeet'))
		{
			// add Update client button.
			JToolBarHelper::custom('rooms.updateClient', 'download custom-button-updateclient', '', 'COM_PLUGNMEET_UPDATE_CLIENT', false);
		}

		if ($this->canDo->get('core.import') && $this->canDo->get('room.import'))
		{
			JToolBarHelper::custom('rooms.importData', 'upload', '', 'COM_PLUGNMEET_IMPORT_DATA', false);
		}

		// set help url for this view if found
		$this->help_url = PlugnmeetHelper::getHelpUrl('rooms');
		if (PlugnmeetHelper::checkString($this->help_url))
		{
				JToolbarHelper::help('COM_PLUGNMEET_HELP_MANAGER', false, $this->help_url);
		}

		// add the options comp button
		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_plugnmeet');
		}

		// Only load published batch if state and batch is allowed
		if ($this->canState && $this->canBatch)
		{
			JHtmlBatch_::addListSelection(
				JText::_('COM_PLUGNMEET_KEEP_ORIGINAL_STATE'),
				'batch[published]',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('all' => false)), 'value', 'text', '', true)
			);
		}

		// Only load access batch if create, edit and batch is allowed
		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			JHtmlBatch_::addListSelection(
				JText::_('COM_PLUGNMEET_KEEP_ORIGINAL_ACCESS'),
				'batch[access]',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text')
			);
		}

		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			// Category Batch selection.
			JHtmlBatch_::addListSelection(
				JText::_('COM_PLUGNMEET_KEEP_ORIGINAL_CATEGORY'),
				'batch[category]',
				JHtml::_('select.options', JHtml::_('category.options', 'com_plugnmeet'), 'value', 'text')
			);
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		if (!isset($this->document))
		{
			$this->document = JFactory::getDocument();
		}
		$this->document->setTitle(JText::_('COM_PLUGNMEET_ROOMS'));
		$this->document->addStyleSheet(JURI::root() . "administrator/components/com_plugnmeet/assets/css/rooms.css", (PlugnmeetHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
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
		if(strlen($var) > 50)
		{
			// use the helper htmlEscape method instead and shorten the string
			return PlugnmeetHelper::htmlEscape($var, $this->_charset, true);
		}
		// use the helper htmlEscape method instead.
		return PlugnmeetHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.room_title' => JText::_('COM_PLUGNMEET_ROOM_ROOM_TITLE_LABEL'),
			'category_title' => JText::_('COM_PLUGNMEET_ROOM_ROOMS_CATEGORIES'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
