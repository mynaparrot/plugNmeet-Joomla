<?php
/**
 * @package 	plugNmeet
 * @subpackage	category.php
 * @version		1.0.8
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Plugnmeet Component Category Tree
 */

//Insure this view category file is loaded.
$classname = 'PlugnmeetRoomCategories';
if (!class_exists($classname))
{
	$path = JPATH_SITE . '/components/com_plugnmeet/helpers/categoryroom.php';
	if (is_file($path))
	{
		include_once $path;
	}
}
