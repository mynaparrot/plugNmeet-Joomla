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
use Joomla\CMS\Uri\Uri;
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
		$config        = $this->params;
		$assetsPath    = $config->get("plugnmeet_server_url") . "/assets";
		$room_metadata = json_decode($this->item->room_metadata, true);

		// Start with default values
		$plugNmeetConfig = [
			'serverUrl'                    => $config->get('plugnmeet_server_url'),
			'staticAssetsPath'             => $assetsPath,
			'enableDynacast'               => true,
			'enableSimulcast'              => true,
			'videoCodec'                   => 'vp8',
			'defaultWebcamResolution'      => 'h720',
			'defaultScreenShareResolution' => 'h1080fps15',
			'defaultAudioPreset'           => 'music',
			'stopMicTrackOnMute'           => true,
			'focusActiveSpeakerWebcam'     => true,
		];

		// Override with advanced settings from the database if they exist
		if (isset($room_metadata["advanced_settings"]))
		{
			$advancedSettings = $room_metadata["advanced_settings"];
			if (isset($advancedSettings["enable_simulcast"]))
			{
				$plugNmeetConfig['enableSimulcast'] = filter_var($advancedSettings["enable_simulcast"], FILTER_VALIDATE_BOOLEAN);
			}
			if (isset($advancedSettings["enable_dynacast"]))
			{
				$plugNmeetConfig['enableDynacast'] = filter_var($advancedSettings["enable_dynacast"], FILTER_VALIDATE_BOOLEAN);
			}
			if (isset($advancedSettings["default_webcam_resolution"]))
			{
				$plugNmeetConfig['defaultWebcamResolution'] = $advancedSettings["default_webcam_resolution"];
			}
			if (isset($advancedSettings["default_screen_share_resolution"]))
			{
				$plugNmeetConfig['defaultScreenShareResolution'] = $advancedSettings["default_screen_share_resolution"];
			}
			if (isset($advancedSettings["stop_mic_track_on_mute"]))
			{
				$plugNmeetConfig['stopMicTrackOnMute'] = filter_var($advancedSettings["stop_mic_track_on_mute"], FILTER_VALIDATE_BOOLEAN);
			}
			if (isset($advancedSettings["video_codec"]))
			{
				$plugNmeetConfig['videoCodec'] = $advancedSettings["video_codec"];
			}
			if (isset($advancedSettings["default_audio_preset"]))
			{
				$plugNmeetConfig['defaultAudioPreset'] = $advancedSettings["default_audio_preset"];
			}
		}

		// Handle design customizations
		$custom_designs      = json_decode($this->item->design_customisation, true);
		$designCustomization = [];

		if (!empty($custom_designs['primary_color']))
		{
			$designCustomization['primary_color'] = $custom_designs['primary_color'];
		}
		if (!empty($custom_designs['secondary_color']))
		{
			$designCustomization['secondary_color'] = $custom_designs['secondary_color'];
		}
		if (!empty($custom_designs['background_color']))
		{
			$designCustomization['background_color'] = $custom_designs['background_color'];
		}
		if (!empty($custom_designs['design_background_image']))
		{
			$designCustomization['background_image'] = Uri::root() . strstr($custom_designs['design_background_image'], "#", true);
		}
		if (!empty($custom_designs['header_color']))
		{
			$designCustomization['header_bg_color'] = $custom_designs['header_color'];
		}
		if (!empty($custom_designs['footer_color']))
		{
			$designCustomization['footer_bg_color'] = $custom_designs['footer_color'];
		}
		if (!empty($custom_designs['right_color']))
		{
			$designCustomization['right_panel_bg_color'] = $custom_designs['right_color'];
		}
		if (!empty($custom_designs['custom_css_url']))
		{
			$designCustomization['custom_css_url'] = $custom_designs['custom_css_url'];
		}
		if (!empty($custom_designs['design_logo']))
		{
			$designCustomization['custom_logo'] = Uri::root() . strstr($custom_designs['design_logo'], "#", true);
		}

		if (!empty($designCustomization))
		{
			$plugNmeetConfig['designCustomization'] = $designCustomization;
		}

		$jsonConfig = json_encode($plugNmeetConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		$js         = "window.plugNmeetConfig = JSON.parse(`" . addslashes($jsonConfig) . "`);";
		$cnfScript  = "<script type=\"text/javascript\">\n" . $js . "\n</script>\n";

		return $cnfScript;
	}
}
