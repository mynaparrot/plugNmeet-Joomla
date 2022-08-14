<?php
/**
 * @package 	plugNmeet
 * @subpackage	rooms.php
 * @version		1.0.8
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Rooms Form Field class for the Plugnmeet component
 */
class JFormFieldRooms extends JFormFieldList
{
	/**
	 * The rooms field type.
	 *
	 * @var		string
	 */
	public $type = 'rooms';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		// Get the user object.
		$user = JFactory::getUser();
		// Get the databse object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.room_title'),array('id','room_request_id_room_title')));
		$query->from($db->quoteName('#__plugnmeet_room', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order('a.room_title ASC');
		// Implement View Level Access (if set in table)
		if (!$user->authorise('core.options', 'com_plugnmeet'))
		{
			$columns = $db->getTableColumns('#__plugnmeet_room');
			if(isset($columns['access']))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}
		}
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = array();
		if ($items)
		{
			if ($this->multiple === false)
			{
				//$options[] = JHtml::_('select.option', '', JText::_('COM_PLUGNMEET_SELECT_AN_OPTION'));
			}
			foreach($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->id, $item->room_request_id_room_title);
			}
		}
		return $options;
	}
}
