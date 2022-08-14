<?php
/**
 * @package 	plugNmeet
 * @subpackage	view.html.php
 * @version		1.0.8
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Plugnmeet Html View class for the Room
 */
class PlugnmeetViewRoom extends HtmlView
{
	// Overwriting JView display method
	function display($tpl = null)
	{		
		// get combined params of both component and menu
		$this->app = JFactory::getApplication();
		$this->params = $this->app->getParams();
		$this->menu = $this->app->getMenu()->getActive();
		// get the user object
		$this->user = JFactory::getUser();
		// Initialise variables.
		$this->item = $this->get('Item');
		
		/***[JCBGUI.site_view.php_jview_display.28.$$$$]***/
		$this->displayPageTitle();/***[/JCBGUI$$$$]***/
		

		// Set the toolbar
		$this->addToolBar();

		// set the document
		$this->_prepareDocument();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode(PHP_EOL, $errors), 500);
		}

		parent::display($tpl);
	}


/***[JCBGUI.site_view.php_jview.28.$$$$]***/
    public function displayPageTitle()
    {
        if (preg_match("/room/", $this->menu->link)) {
            // it's a menu item so don't need to do further
            return;
        }

        JFactory::getDocument()->setTitle($this->item->room_title);
    }

    public function getGlobalVariables()
    {
        $params = JComponentHelper::getParams("com_plugnmeet");
        if ($params->get("client_load", 1) == 1) {
            $path = $params->get("plugnmeet_server_url") . "/assets";
        } else {
            $path = JUri::root() . "components/com_plugnmeet/assets/client/dist/assets";
        }

        $js = 'window.PLUG_N_MEET_SERVER_URL = "' . $params->get("plugnmeet_server_url") . '";';
        $js .= 'window.STATIC_ASSETS_PATH = "' . $path . '";';

        $js .= 'Window.ENABLE_DYNACAST = ' . filter_var($params->get("enable_dynacast"), FILTER_VALIDATE_BOOLEAN) . ';';
        $js .= 'window.ENABLE_SIMULCAST = ' . filter_var($params->get("enable_simulcast"), FILTER_VALIDATE_BOOLEAN) . ';';
        $js .= 'window.VIDEO_CODEC = "' . $params->get("video_codec", "vp8") . '";';
        $js .= 'window.DEFAULT_WEBCAM_RESOLUTION = "' . $params->get("default_webcam_resolution", "h720") . '";';
        $js .= 'window.DEFAULT_SCREEN_SHARE_RESOLUTION = "' . $params->get("default_screen_share_resolution", "h1080fps15") . '";';
        $js .= 'window.STOP_MIC_TRACK_ON_MUTE = ' . filter_var($params->get("stop_mic_track_on_mute"), FILTER_VALIDATE_BOOLEAN) . ';';

        $room_metadata = json_decode($this->item->room_metadata, true);
        $custom_designs = [];
        foreach ($room_metadata["custom_design"] as $key => $val) {
            if (empty($val)) {
                $custom_designs[$key] = $params->get($key);
            } else {
                $custom_designs[$key] = $val;
            }
        }

        if (!empty($custom_designs['logo'])) {
            $js .= 'window.CUSTOM_LOGO = "' . JUri::root() . $custom_designs['logo'] . '";';
        } else if ($params->get("logo")) {
            $js .= 'window.CUSTOM_LOGO = "' . JUri::root() . $params->get("logo") . '";';
        }

        $custom_design_items = [];
        if (!empty($custom_designs['primary_color'])) {
            $custom_design_items['primary_color'] = $custom_designs['primary_color'];
        }
        if (!empty($custom_designs['secondary_color'])) {
            $custom_design_items['secondary_color'] = $custom_designs['secondary_color'];
        }
        if (!empty($custom_designs['background_color'])) {
            $custom_design_items['background_color'] = $custom_designs['background_color'];
        }
        if (!empty($custom_designs['background_image'])) {
            $custom_design_items['background_image'] = JUri::root() . "/" . $custom_designs['background_image'];
        }
        if (!empty($custom_designs['header_color'])) {
            $custom_design_items['header_bg_color'] = $custom_designs['header_color'];
        }
        if (!empty($custom_designs['footer_color'])) {
            $custom_design_items['footer_bg_color'] = $custom_designs['footer_color'];
        }
        if (!empty($custom_designs['left_color'])) {
            $custom_design_items['left_side_bg_color'] = $custom_designs['left_color'];
        }
        if (!empty($custom_designs['right_color'])) {
            $custom_design_items['right_side_bg_color'] = $custom_designs['right_color'];
        }
        if (!empty($custom_designs['custom_css_url'])) {
            $custom_design_items['custom_css_url'] = $custom_designs['custom_css_url'];
        }

        if (count($custom_design_items) > 0) {
            $js .= 'window.DESIGN_CUSTOMIZATION = `' . json_encode($custom_design_items) . '`;';
        }

        $js = str_replace(";", ";\n\t", $js);
        $script = "<script type=\"text/javascript\">\n\t" . $js . "</script>\n";

        return $script;
    }/***[/JCBGUI$$$$]***/


	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{

		// always make sure jquery is loaded.
		JHtml::_('jquery.framework');
		// Load the header checker class.
		require_once( JPATH_COMPONENT_SITE.'/helpers/headercheck.php' );
		// Initialize the header checker.
		$HeaderCheck = new plugnmeetHeaderCheck;
		// load the meta description
		if (isset($this->item->metadesc) && $this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		// load the key words if set
		if (isset($this->item->metakey) && $this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		// check the robot params
		if (isset($this->item->robots) && $this->item->robots)
		{
			$this->document->setMetadata('robots', $this->item->robots);
		}
		elseif ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		// check if autor is to be set
		if (isset($this->item->created_by) && $this->params->get('MetaAuthor') == '1')
		{
			$this->document->setMetaData('author', $this->item->created_by);
		}
		// check if metadata is available
		if (isset($this->item->metadata) && $this->item->metadata)
		{
			$mdata = json_decode($this->item->metadata,true);
			foreach ($mdata as $k => $v)
			{
				if ($v)
				{
					$this->document->setMetadata($k, $v);
				}
			}
		} 
		// add the document default css file
		$this->document->addStyleSheet(JURI::root(true) .'/components/com_plugnmeet/assets/css/room.css', (PlugnmeetHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{

		// set help url for this view if found
		$this->help_url = PlugnmeetHelper::getHelpUrl('room');
		if (PlugnmeetHelper::checkString($this->help_url))
		{
			JToolbarHelper::help('COM_PLUGNMEET_HELP_MANAGER', false, $this->help_url);
		}
		// now initiate the toolbar
		$this->toolbar = JToolbar::getInstance();
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var, $sorten = false, $length = 40)
	{
		// use the helper htmlEscape method instead.
		return PlugnmeetHelper::htmlEscape($var, $this->_charset, $sorten, $length);
	}
}
