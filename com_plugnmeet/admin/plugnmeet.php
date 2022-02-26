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



// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_plugnmeet'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
};

// Add CSS file for all pages
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_plugnmeet/assets/css/admin.css');
$document->addScript('components/com_plugnmeet/assets/js/admin.js');

// require helper files
JLoader::register('PlugnmeetHelper', __DIR__ . '/helpers/plugnmeet.php');
JLoader::register('JHtmlBatch_', __DIR__ . '/helpers/html/batch_.php');

// Get an instance of the controller prefixed by Plugnmeet
$controller = JControllerLegacy::getInstance('Plugnmeet');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
