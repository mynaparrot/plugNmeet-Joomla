<?php

/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Site\View\Room;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use JUri;
use Mynaparrot\Component\Plugnmeet\Site\Model\RoomModel;

/**
 * View class for a list of Plugnmeet.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function display($tpl = null)
	{
		/** @var RoomModel $model */
		$model       = $this->getModel();
		$this->state = $model->getState();
		$this->item  = $model->getItem();

		$app          = Factory::getApplication();
		$this->params = $app->getParams('com_plugnmeet');

		if (empty($this->item))
		{
			throw new \Exception("item not found", 404);
		}
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$title = $this->item ? $this->item->room_title : $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->getDocument()->setTitle($title);
	}

	public function getGlobalVariables()
	{
		$path                  = $this->params->get("plugnmeet_server_url") . "/assets";
		$room_metadata         = json_decode($this->item->room_metadata, true);
		$enableDynacast        = 1;
		$enableSimulcast       = 1;
		$webcamResolution      = "h720";
		$screenShareResolution = "h1080fps15";
		$audioPreset           = "music";
		$stopMicTrackOnMute    = 1;
		$videoCodec            = "vp8";

		if (isset($room_metadata["advanced_settings"]))
		{
			$advancedSettings      = $room_metadata["advanced_settings"];
			$enableSimulcast       = $advancedSettings["enable_simulcast"];
			$enableDynacast        = $advancedSettings["enable_dynacast"];
			$webcamResolution      = $advancedSettings["default_webcam_resolution"];
			$screenShareResolution = $advancedSettings["default_screen_share_resolution"];
			$stopMicTrackOnMute    = $advancedSettings["stop_mic_track_on_mute"];
			$videoCodec            = $advancedSettings["video_codec"];
			$audioPreset           = $advancedSettings["default_audio_preset"];
		}

		$js = 'window.PLUG_N_MEET_SERVER_URL = "' . $this->params->get("plugnmeet_server_url") . '";';
		$js .= 'window.STATIC_ASSETS_PATH = "' . $path . '";';

		$js .= 'Window.ENABLE_DYNACAST = ' . filter_var($enableDynacast, FILTER_VALIDATE_BOOLEAN) . ';';
		$js .= 'window.ENABLE_SIMULCAST = ' . filter_var($enableSimulcast, FILTER_VALIDATE_BOOLEAN) . ';';
		$js .= 'window.VIDEO_CODEC = "' . $videoCodec . '";';
		$js .= 'window.DEFAULT_WEBCAM_RESOLUTION = "' . $webcamResolution . '";';
		$js .= 'window.DEFAULT_SCREEN_SHARE_RESOLUTION = "' . $screenShareResolution . '";';
		$js .= 'window.DEFAULT_AUDIO_PRESET = "' . $audioPreset . '";';
		$js .= 'window.STOP_MIC_TRACK_ON_MUTE = ' . filter_var($stopMicTrackOnMute, FILTER_VALIDATE_BOOLEAN) . ';';

		$custom_designs = json_decode($this->item->design_customisation, true);

		if (!empty($custom_designs['design_logo']))
		{
			$js .= 'window.CUSTOM_LOGO = "' . JUri::root() . strstr($custom_designs['design_logo'], "#", true) . '";';
		}

		$custom_design_items = [];
		if (!empty($custom_designs['primary_color']))
		{
			$custom_design_items['primary_color'] = $custom_designs['primary_color'];
		}
		if (!empty($custom_designs['secondary_color']))
		{
			$custom_design_items['secondary_color'] = $custom_designs['secondary_color'];
		}
		if (!empty($custom_designs['background_color']))
		{
			$custom_design_items['background_color'] = $custom_designs['background_color'];
		}
		if (!empty($custom_designs['design_background_image']))
		{
			$custom_design_items['background_image'] = JUri::root() . strstr($custom_designs['design_background_image'], "#", true);
		}
		if (!empty($custom_designs['header_color']))
		{
			$custom_design_items['header_bg_color'] = $custom_designs['header_color'];
		}
		if (!empty($custom_designs['footer_color']))
		{
			$custom_design_items['footer_bg_color'] = $custom_designs['footer_color'];
		}
		if (!empty($custom_designs['left_color']))
		{
			$custom_design_items['left_side_bg_color'] = $custom_designs['left_color'];
		}
		if (!empty($custom_designs['right_color']))
		{
			$custom_design_items['right_side_bg_color'] = $custom_designs['right_color'];
		}
		if (!empty($custom_designs['custom_css_url']))
		{
			$custom_design_items['custom_css_url'] = $custom_designs['custom_css_url'];
		}

		if (count($custom_design_items) > 0)
		{
			$js .= 'window.DESIGN_CUSTOMIZATION = `' . json_encode($custom_design_items) . '`;';
		}

		$js = str_replace(";", ";\n\t", $js);

		return "<script type=\"text/javascript\">\n\t" . $js . "</script>\n";
	}
}
