<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');



// Set the component css/js
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_plugnmeet/assets/css/site.css');
$document->addScript('components/com_plugnmeet/assets/js/site.js');

// Require helper files
JLoader::register('PlugnmeetHelper', __DIR__ . '/helpers/plugnmeet.php');
JLoader::register('PlugnmeetHelperRoute', __DIR__ . '/helpers/route.php');

// Get an instance of the controller prefixed by Plugnmeet
$controller = JControllerLegacy::getInstance('Plugnmeet');

// Perform the request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
