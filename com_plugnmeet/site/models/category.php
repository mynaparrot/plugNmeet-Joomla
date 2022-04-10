<?php
/**
 * @package 	plugNmeet
 * @subpackage	category.php
 * @version		1.0.3
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
 * Plugnmeet Model for Category
 */
class PlugnmeetModelCategory extends JModelList
{
	/**
	 * Model user data.
	 *
	 * @var        strings
	 */
	protected $user;
	protected $userId;
	protected $guest;
	protected $groups;
	protected $levels;
	protected $app;
	protected $input;
	protected $uikitComp;

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$this->user = JFactory::getUser();
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->initSet = true; 
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__plugnmeet_room as a
		$query->select($db->quoteName(
			array('a.id','a.room_title','a.alias','a.description','a.created_by','a.created','a.modified','a.hits'),
			array('id','room_title','alias','description','created_by','created','modified','hits')));
		$query->from($db->quoteName('#__plugnmeet_room', 'a'));
		// Check if $this->input->getInt("id", 0) is a string or numeric value.
		$checkValue = $this->input->getInt("id", 0);
		if (isset($checkValue) && PlugnmeetHelper::checkString($checkValue))
		{
			$query->where('a.catid = ' . $db->quote($checkValue));
		}
		elseif (is_numeric($checkValue))
		{
			$query->where('a.catid = ' . $checkValue);
		}
		else
		{
			return false;
		}
		// Get where a.published is 1
		$query->where('a.published = 1');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$user = JFactory::getUser();
		// check if this user has permission to access item
		if (!$user->authorise('site.category.access', 'com_plugnmeet'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_PLUGNMEET_NOT_AUTHORISED_TO_VIEW_CATEGORY'), 'error');
			// redirect away to the default view if no access allowed.
			$app->redirect(JRoute::_('index.php?option=com_plugnmeet&view=categories'));
			return false;
		}
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = JComponentHelper::getParams('com_plugnmeet', true);

		// Insure all item fields are adapted where needed.
		if (PlugnmeetHelper::checkArray($items))
		{
			// Load the JEvent Dispatcher
			JPluginHelper::importPlugin('content');
			$this->_dispatcher = JFactory::getApplication();
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = (isset($item->alias) && isset($item->id)) ? $item->id.':'.$item->alias : $item->id;
				// Check if item has params, or pass whole item.
				$params = (isset($item->params) && PlugnmeetHelper::checkJson($item->params)) ? json_decode($item->params) : $item;
				// Make sure the content prepare plugins fire on description
				$_description = new stdClass();
				$_description->text =& $item->description; // value must be in text
				// Since all values are now in text (Joomla Limitation), we also add the field name (description) to context
				$this->_dispatcher->triggerEvent("onContentPrepare", array('com_plugnmeet.category.description', &$_description, &$params, 0));
			}
		}

		// return items
		return $items;
	}
}
