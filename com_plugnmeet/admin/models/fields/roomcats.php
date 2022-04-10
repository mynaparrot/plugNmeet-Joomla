<?php
/**
 * @package 	plugNmeet
 * @subpackage	roomcats.php
 * @version		1.0.3
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
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
 * Roomcats Form Field class for the Plugnmeet component
 */
class JFormFieldRoomcats extends JFormFieldList
{
	/**
	 * The roomcats field type.
	 *
	 * @var		string
	 */
	public $type = 'roomcats';

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
		$query->select($db->quoteName(array('a.id','a.title'),array('id','category_request_id_title')));
		$query->from($db->quoteName('#__categories', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.extension') . ' = "com_plugnmeet"');
		$query->order('a.title ASC');
		// Implement View Level Access (if set in table)
		if (!$user->authorise('core.options', 'com_categories'))
		{
			$columns = $db->getTableColumns('#__categories');
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
				$options[] = JHtml::_('select.option', '', JText::_('COM_PLUGNMEET_SELECT_A_CATEGORY'));
			}
			foreach($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->id, $item->category_request_id_title);
			}
		}
		return $options;
	}
}
