<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * Class PlugnmeetFrontendHelper
 *
 * @since  1.0.0
 */
class PlugnmeetHelper
{

	/**
	 * Get category name using category ID
	 *
	 * @param   integer  $category_id  Category ID
	 *
	 * @return mixed category name if the category was found, null otherwise
	 */
	public static function getCategoryNameByCategoryId($category_id)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . intval($category_id));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets the edit permission for an user
	 *
	 * @param   mixed  $item  The item
	 *
	 * @return  bool
	 */
	public static function canUserEdit($item)
	{
		$permission = false;
		$user       = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_plugnmeet') || (isset($item->created_by) && $user->authorise('core.edit.own', 'com_plugnmeet') && $item->created_by == $user->id) || $user->authorise('core.create', 'com_plugnmeet'))
		{
			$permission = true;
		}

		return $permission;
	}

	/**
	 * Returns an associative array of object properties.
	 *
	 * @param   boolean  $public          If true, returns only the public properties.
	 * @param   boolean  $stdClassObject  If true, returns std class.
	 *
	 * @return  array|object
	 */
	public static function getProperties(Table $table, bool $public = true, bool $stdClassObject = true)
	{
		$vars = get_object_vars($table);

		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if (str_starts_with($key, '_'))
				{
					unset($vars[$key]);
				}
			}
		}

		if ($stdClassObject)
		{
			return ArrayHelper::toObject($vars, \stdClass::class);
		}

		return $vars;
	}
}
