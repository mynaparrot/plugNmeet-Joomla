<?php


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
