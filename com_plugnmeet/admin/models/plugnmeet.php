<?php
/**
 * @package 	plugNmeet
 * @subpackage	plugnmeet.php
 * @version		1.0.0
 * @build		26th February, 2022
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');



/**
 * Plugnmeet Model
 */
class PlugnmeetModelPlugnmeet extends JModelList
{
	public function getIcons()
	{
		// load user for access menus
		$user = JFactory::getUser();
		// reset icon array
		$icons  = array();
		// view groups array
		$viewGroups = array(
			'main' => array('png.room.add', 'png.rooms', 'png.rooms.catid_qpo0O0oqp_com_plugnmeet', 'png.recordings')
		);
		// view access array
		$viewAccess = array(
			'recordings.submenu' => 'recordings.submenu',
			'recordings.dashboard_list' => 'recordings.dashboard_list',
			'rooms.access' => 'room.access',
			'room.access' => 'room.access',
			'rooms.submenu' => 'room.submenu',
			'rooms.dashboard_list' => 'room.dashboard_list',
			'room.dashboard_add' => 'room.dashboard_add');
		// loop over the $views
		foreach($viewGroups as $group => $views)
		{
			$i = 0;
			if (PlugnmeetHelper::checkArray($views))
			{
				foreach($views as $view)
				{
					$add = false;
					// external views (links)
					if (strpos($view,'||') !== false)
					{
						$dwd = explode('||', $view);
						if (count($dwd) == 3)
						{
							list($type, $name, $url) = $dwd;
							$viewName 	= $name;
							$alt 		= $name;
							$url 		= $url;
							$image 		= $name . '.' . $type;
							$name 		= 'COM_PLUGNMEET_DASHBOARD_' . PlugnmeetHelper::safeString($name,'U');
						}
					}
					// internal views
					elseif (strpos($view,'.') !== false)
					{
						$dwd = explode('.', $view);
						if (count($dwd) == 3)
						{
							list($type, $name, $action) = $dwd;
						}
						elseif (count($dwd) == 2)
						{
							list($type, $name) = $dwd;
							$action = false;
						}
						if ($action)
						{
							$viewName = $name;
							switch($action)
							{
								case 'add':
									$url	= 'index.php?option=com_plugnmeet&view=' . $name . '&layout=edit';
									$image	= $name . '_' . $action.  '.' . $type;
									$alt	= $name . '&nbsp;' . $action;
									$name	= 'COM_PLUGNMEET_DASHBOARD_'.PlugnmeetHelper::safeString($name,'U').'_ADD';
									$add	= true;
								break;
								default:
									// check for new convention (more stable)
									if (strpos($action, '_qpo0O0oqp_') !== false)
									{
										list($action, $extension) = (array) explode('_qpo0O0oqp_', $action);
										$extension = str_replace('_po0O0oq_', '.', $extension);
									}
									else
									{
										$extension = 'com_plugnmeet.' . $name;
									}
									$url	= 'index.php?option=com_categories&view=categories&extension=' . $extension;
									$image	= $name . '_' . $action . '.' . $type;
									$alt	= $viewName . '&nbsp;' . $action;
									$name	= 'COM_PLUGNMEET_DASHBOARD_' . PlugnmeetHelper::safeString($name,'U') . '_' . PlugnmeetHelper::safeString($action,'U');
								break;
							}
						}
						else
						{
							$viewName 	= $name;
							$alt 		= $name;
							$url 		= 'index.php?option=com_plugnmeet&view=' . $name;
							$image 		= $name . '.' . $type;
							$name 		= 'COM_PLUGNMEET_DASHBOARD_' . PlugnmeetHelper::safeString($name,'U');
							$hover		= false;
						}
					}
					else
					{
						$viewName 	= $view;
						$alt 		= $view;
						$url 		= 'index.php?option=com_plugnmeet&view=' . $view;
						$image 		= $view . '.png';
						$name 		= ucwords($view).'<br /><br />';
						$hover		= false;
					}
					// first make sure the view access is set
					if (PlugnmeetHelper::checkArray($viewAccess))
					{
						// setup some defaults
						$dashboard_add = false;
						$dashboard_list = false;
						$accessTo = '';
						$accessAdd = '';
						// access checking start
						$accessCreate = (isset($viewAccess[$viewName.'.create'])) ? PlugnmeetHelper::checkString($viewAccess[$viewName.'.create']):false;
						$accessAccess = (isset($viewAccess[$viewName.'.access'])) ? PlugnmeetHelper::checkString($viewAccess[$viewName.'.access']):false;
						// set main controllers
						$accessDashboard_add = (isset($viewAccess[$viewName.'.dashboard_add'])) ? PlugnmeetHelper::checkString($viewAccess[$viewName.'.dashboard_add']):false;
						$accessDashboard_list = (isset($viewAccess[$viewName.'.dashboard_list'])) ? PlugnmeetHelper::checkString($viewAccess[$viewName.'.dashboard_list']):false;
						// check for adding access
						if ($add && $accessCreate)
						{
							$accessAdd = $viewAccess[$viewName.'.create'];
						}
						elseif ($add)
						{
							$accessAdd = 'core.create';
						}
						// check if access to view is set
						if ($accessAccess)
						{
							$accessTo = $viewAccess[$viewName.'.access'];
						}
						// set main access controllers
						if ($accessDashboard_add)
						{
							$dashboard_add	= $user->authorise($viewAccess[$viewName.'.dashboard_add'], 'com_plugnmeet');
						}
						if ($accessDashboard_list)
						{
							$dashboard_list = $user->authorise($viewAccess[$viewName.'.dashboard_list'], 'com_plugnmeet');
						}
						if (PlugnmeetHelper::checkString($accessAdd) && PlugnmeetHelper::checkString($accessTo))
						{
							// check access
							if($user->authorise($accessAdd, 'com_plugnmeet') && $user->authorise($accessTo, 'com_plugnmeet') && $dashboard_add)
							{
								$icons[$group][$i]			= new StdClass;
								$icons[$group][$i]->url 	= $url;
								$icons[$group][$i]->name 	= $name;
								$icons[$group][$i]->image 	= $image;
								$icons[$group][$i]->alt 	= $alt;
							}
						}
						elseif (PlugnmeetHelper::checkString($accessTo))
						{
							// check access
							if($user->authorise($accessTo, 'com_plugnmeet') && $dashboard_list)
							{
								$icons[$group][$i]			= new StdClass;
								$icons[$group][$i]->url 	= $url;
								$icons[$group][$i]->name 	= $name;
								$icons[$group][$i]->image 	= $image;
								$icons[$group][$i]->alt 	= $alt;
							}
						}
						elseif (PlugnmeetHelper::checkString($accessAdd))
						{
							// check access
							if($user->authorise($accessAdd, 'com_plugnmeet') && $dashboard_add)
							{
								$icons[$group][$i]			= new StdClass;
								$icons[$group][$i]->url 	= $url;
								$icons[$group][$i]->name 	= $name;
								$icons[$group][$i]->image 	= $image;
								$icons[$group][$i]->alt 	= $alt;
							}
						}
						else
						{
							$icons[$group][$i]			= new StdClass;
							$icons[$group][$i]->url 	= $url;
							$icons[$group][$i]->name 	= $name;
							$icons[$group][$i]->image 	= $image;
							$icons[$group][$i]->alt 	= $alt;
						}
					}
					else
					{
						$icons[$group][$i]			= new StdClass;
						$icons[$group][$i]->url 	= $url;
						$icons[$group][$i]->name 	= $name;
						$icons[$group][$i]->image 	= $image;
						$icons[$group][$i]->alt 	= $alt;
					}
					$i++;
				}
			}
			else
			{
					$icons[$group][$i] = false;
			}
		}
		return $icons;
	}
}
