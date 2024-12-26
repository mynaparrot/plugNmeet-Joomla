<?php

/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Site\Service;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;

/**
 * Class PlugnmeetRouter
 *
 */
class Router extends RouterView
{
	private $noIDs;
	/**
	 * The category factory
	 *
	 * @var    CategoryFactoryInterface
	 *
	 * @since  1.0.0
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params                = ComponentHelper::getParams('com_plugnmeet');
		$this->noIDs           = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;


		$rooms = new RouterViewConfiguration('rooms');
		$rooms->setKey('catid')->setNestable();
		$this->registerView($rooms);
		$ccRoom = new RouterViewConfiguration('room');
		$ccRoom->setKey('id')->setParent($rooms, 'catid');
		$this->registerView($ccRoom);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}


	/**
	 * Method to get the segment(s) for a category
	 *
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getRoomsSegment($id, $query)
	{
		$category = $this->getCategories(["access" => true])->get($id);

		if ($category)
		{
			$path    = array_reverse($category->getPath(), true);
			$path[0] = '1:root';

			if ($this->noIDs)
			{
				foreach ($path as &$segment)
				{
					list($id, $segment) = explode(':', $segment, 2);
				}
			}

			return $path;
		}

		return array();
	}

	/**
	 * Method to get the segment(s) for an room
	 *
	 * @param   string  $id     ID of the room to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getRoomSegment($id, $query)
	{
		if (!strpos($id, ':'))
		{
			$db      = Factory::getContainer()->get('DatabaseDriver');
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('alias'))
				->from($dbquery->qn('#__plugnmeet_rooms'))
				->where('id = ' . $dbquery->q($id));
			$db->setQuery($dbquery);

			$id .= ':' . $db->loadResult();
		}

		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);

			return array($void => $segment);
		}

		return array((int) $id => $id);
	}


	/**
	 * Method to get the id for a category
	 *
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getRoomsId($segment, $query)
	{
		if (isset($query['catid']))
		{
			$category = $this->getCategories(["access" => true])->get($query['catid']);

			if ($category)
			{
				foreach ($category->getChildren() as $child)
				{
					if ($this->noIDs)
					{
						if ($child->alias == $segment)
						{
							return $child->id;
						}
					}
					else
					{
						if ($child->id == (int) $segment)
						{
							return $child->id;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Method to get the segment(s) for an room
	 *
	 * @param   string  $segment  Segment of the room to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getRoomId($segment, $query)
	{
		if ($this->noIDs)
		{
			$db      = Factory::getContainer()->get('DatabaseDriver');
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('id'))
				->from($dbquery->qn('#__plugnmeet_rooms'))
				->where('alias = ' . $dbquery->q($segment));
			$dbquery->where($dbquery->qn('cat') . ' LIKE "%' . $query['catid'] . '%"');
			$db->setQuery($dbquery);

			return (int) $db->loadResult();
		}

		return (int) $segment;
	}

	/**
	 * Method to get categories from cache
	 *
	 * @param   array  $options  The options for retrieving categories
	 *
	 * @return  CategoryInterface  The object containing categories
	 *
	 * @since   1.0.0
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key]))
		{
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}
}
