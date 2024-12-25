<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\Table;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table as Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;


/**
 * Room table
 *
 * @since 1.0.0
 */
class RoomTable extends Table
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	protected $_jsonEncode = ['room_metadata', 'design_customisation', 'params'];

	/**
	 * Check if a field is unique
	 *
	 * @param   string  $field  Name of the field
	 *
	 * @return bool True if unique
	 */
	private function isUnique($field)
	{
		$db    = $this->_db;
		$query = $db->getQuery(true);

		$categories        = explode(',', $this->cat);
		$andWhereCondition = array();
		foreach ($categories as $categoryid)
		{
			$andWhereCondition[] = $db->quoteName('cat') . ' like "%' . $categoryid . '%"';
		}

		$query
			->select($db->quoteName($field))
			->from($db->quoteName($this->_tbl))
			->where($db->quoteName($field) . ' = ' . $db->quote($this->$field))
			->where($db->quoteName('id') . ' <> ' . (int) $this->{$this->_tbl_key});

		if (!empty($andWhereCondition))
		{
			$query->andWhere($andWhereCondition);
		}

		$db->setQuery($query);
		$db->execute();

		return $db->getNumRows() == 0;
	}

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  &$db  A database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_plugnmeet.room';
		parent::__construct('#__plugnmeet_rooms', 'id', $db);
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   1.0.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  \InvalidArgumentException
	 * @since   1.0.0
	 * @see     Table:bind
	 */
	public function bind($array, $ignore = '')
	{
		$user = Factory::getApplication()->getIdentity();

		$input = Factory::getApplication()->input;
		$task  = $input->getString('task', '');

		if ($array['id'] == 0 && empty($array['created_by']))
		{
			$array['created_by'] = $user->id;
		}

		if ($array['id'] == 0 && empty($array['modified_by']))
		{
			$array['modified_by'] = $user->id;
		}

		if ($task == 'apply' || $task == 'save')
		{
			$array['modified_by'] = $user->id;
		}

		// Support for multiple field: cat
		if (isset($array['cat']))
		{
			if (is_array($array['cat']))
			{
				$array['cat'] = implode(',', $array['cat']);
			}
			elseif (strpos($array['cat'], ','))
			{
				$array['cat'] = explode(',', $array['cat']);
			}
			elseif (strlen($array['cat']) == 0)
			{
				$array['cat'] = '';
			}
		}
		else
		{
			$array['cat'] = '';
		}

		// Support for alias field: alias
		if (empty($array['alias']))
		{
			if (empty($array['room_title']))
			{
				$array['alias'] = OutputFilter::stringURLSafe(date('Y-m-d H:i:s'));
			}
			else
			{
				if (Factory::getApplication()->getConfig()->get('unicodeslugs') == 1)
				{
					$array['alias'] = OutputFilter::stringURLUnicodeSlug(trim($array['room_title']));
				}
				else
				{
					$array['alias'] = OutputFilter::stringURLSafe(trim($array['room_title']));
				}
			}
		}


		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (!$user->authorise('core.admin', 'com_plugnmeet.room.' . $array['id']))
		{
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_plugnmeet/access.xml',
				"/access/section[@name='room']/"
			);
			$default_actions = Access::getAssetRules('com_plugnmeet.room.' . $array['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action)
			{
				if (key_exists($action->name, $default_actions))
				{
					$array_jaccess[$action->name] = $default_actions[$action->name];
				}
			}

			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function store($updateNulls = true)
	{
		return parent::store($updateNulls);
	}

	/**
	 * This function convert an array of Access objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of Access objects.
	 *
	 * @return  array
	 */
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess)
		{
			$actions = array();

			if ($jaccess)
			{
				foreach ($jaccess->getData() as $group => $allow)
				{
					$actions[$group] = ((bool) $allow);
				}
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		// Check if alias is unique
		if (!$this->isUnique('alias'))
		{
			$count        = 0;
			$currentAlias = $this->alias;
			while (!$this->isUnique('alias'))
			{
				$this->alias = $currentAlias . '-' . $count++;
			}
		}


		return parent::check();
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see Table::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return $this->typeAlias . '.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   Table    $table  Table name
	 * @param   integer  $id     Id
	 *
	 * @return mixed The id on success, false on failure.
	 * @see Table::_getAssetParentId
	 *
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = new Asset($this->getDbo(), $this->getDispatcher());

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_plugnmeet');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}


	/**
	 * Delete a record by id
	 *
	 * @param   mixed  $pk  Primary key value to delete. Optional
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		$this->load($pk);

		return parent::delete($pk);
	}
}
