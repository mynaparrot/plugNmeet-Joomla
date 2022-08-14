<?php
/**
 * @package 	plugNmeet
 * @subpackage	room.php
 * @version		1.0.8
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Plugnmeet Room Admin Model
 */
class PlugnmeetModelRoom extends AdminModel
{
	/**
	 * The tab layout fields array.
	 *
	 * @var      array
	 */
	protected $tabLayoutFields = array(
		'details' => array(
			'left' => array(
				'room_title',
				'alias',
				'catid',
				'description',
				'moderator_pass',
				'attendee_pass',
				'welcome_message',
				'max_participants',
				'room_metadata',
				'room_id'
			)
		)
	);

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_PLUGNMEET';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_plugnmeet.room';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'room', $prefix = 'PlugnmeetTable', $config = array())
	{
		// add table path for when model gets used from other component
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_plugnmeet/tables');
		// get instance of the table
		return JTable::getInstance($type, $prefix, $config);
	}


/***[JCBGUI.admin_view.php_model.110.$$$$]***/
    private function formatHtml($items, $fieldName, $data)
    {
        $html = "";
        foreach ($items as $key => $item) {
            if ($item["type"] === "select") {
                $html .= "<div class=\"control-group\" >";
                $html .= "<div class=\"control-label\">";
                $html .= "<label class=\"hasPopover\" data-content=\"{$item['des']}\" data-original-title=\"{$item['label']}\">{$item['label']}</label>";
                $html .= "</div>";

                $html .= "<div class=\"control-label\">";
                $html .= "<select name=\"jform[{$fieldName}][{$key}]\" class=\"list_class\">";

                $value = $item["selected"];
                if (isset($data[$key])) {
                    $value = $data[$key];
                }

                foreach ($item["options"] as $option) {
                    $selected = "";
                    if ($option['value'] == $value) {
                        $selected = "selected";
                    }
                    $html .= "<option value=\"{$option['value']}\" {$selected}>{$option['label']}</option>";
                }

                $html .= "</select></div></div>";
            } elseif ($item["type"] === "text" || $item["type"] === "number") {
                $value = $item["default"];
                if (isset($data[$key])) {
                    $value = $data[$key];
                }

                $html .= '<div class="control-group control-wrapper-' . $key . '">';

                $html .= '<div class="control-label">';
                $html .= '<label class="hasPopover" for="jform_' . $key . '" title="" data-content="' . $item['des'] . '" data-original-title="' . $item['label'] . '">' . $item['label'] . '</label>';
                $html .= '</div>';

                $html .= '<div class="list_class">';
                $html .= '<input type="' . $item["type"] . '" name="jform[' . $fieldName . '][' . $key . ']" id="jform_' . $key . '" value="' . $value . '" class="text_area" size="10" maxlength="50" autocomplete="off" aria-invalid="false">';
                $html .= '</div>';

                $html .= '</div>';
            } elseif ($item["type"] === "textarea") {
                $value = $item["default"];
                if (isset($data[$key])) {
                    $value = $data[$key];
                }

                $html .= '<div class="control-group control-wrapper-' . $key . '">';

                $html .= '<div class="control-label">';
                $html .= '<label class="hasPopover" for="jform_' . $key . '" title="" data-content="' . $item['des'] . '" data-original-title="' . $item['label'] . '">' . $item['label'] . '</label>';
                $html .= '</div>';

                $html .= '<div class="list_class">';
                $html .= '<textarea name="jform[' . $fieldName . '][' . $key . ']" id="jform_' . $key . '" cols="4" rows="7" class="text_area" aria-invalid="false">' . $value . '</textarea>';
                $html .= '</div>';

                $html .= '</div>';
            }
        }

        return $html;
    }

    public function getRoomFeatures()
    {
        $roomFeatures = array(
            "allow_webcams" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_WEBCAMS"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_WEBCAMS_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "mute_on_start" => array(
                "label" => JText::_("COM_PLUGNMEET_MUTE_ON_START"),
                "des" => JText::_("COM_PLUGNMEET_MUTE_ON_START_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "allow_screen_share" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_SCREEN_SHARING"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_SCREEN_SHARING_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "allow_recording" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_RECORDING"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_RECORDING_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "allow_rtmp" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_RTMP"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_RTMP_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "allow_view_other_webcams" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_VIEW_OTHER_WEBCAMS"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_VIEW_OTHER_WEBCAMS_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "allow_view_other_users_list" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_VIEW_OTHER_USERS"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_VIEW_OTHER_USERS_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "admin_only_webcams" => array(
                "label" => JText::_("COM_PLUGNMEET_ADMIN_ONLY_WEBCAMS"),
                "des" => JText::_("COM_PLUGNMEET_ADMIN_ONLY_WEBCAMS_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "allow_polls" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_POLLS"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_POLLS_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "room_duration" => array(
                "label" => JText::_("COM_PLUGNMEET_ROOM_DURATION"),
                "des" => JText::_("COM_PLUGNMEET_ROOM_DURATION_DES"),
                "default" => 0,
                "type" => "number"
            )
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->room_features)) {
            $data = (array)$item->room_metadata->room_features;
        }

        return $this->formatHtml($roomFeatures, "room_features", $data);
    }

    public function getChatFeatures()
    {
        $chatFeatures = array(
            "allow_chat" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_CHAT"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_CHAT_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "allow_file_upload" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_FILE_UPLOAD"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_FILE_UPLOAD_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->chat_features)) {
            $data = (array)$item->room_metadata->chat_features;
        }

        return $this->formatHtml($chatFeatures, "chat_features", $data);
    }

    public function getSharedNotePadFeatures()
    {
        $sharedNotePadFeatures = array(
            "allowed_shared_note_pad" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_SHARED_NOTEPAD"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_SHARED_NOTEPAD_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->shared_note_pad_features)) {
            $data = (array)$item->room_metadata->shared_note_pad_features;
        }

        return $this->formatHtml($sharedNotePadFeatures, "shared_note_pad_features", $data);
    }

    public function getWhiteboardFeatures()
    {
        $whiteboardFeatures = array(
            "allowed_whiteboard" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_WHITEBOARD"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_WHITEBOARD_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->whiteboard_features)) {
            $data = (array)$item->room_metadata->whiteboard_features;
        }

        return $this->formatHtml($whiteboardFeatures, "whiteboard_features", $data);
    }

    public function getExternalMediaPlayerFeatures()
    {
        $externalMediaPlayerFeatures = array(
            "allowed_external_media_player" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_EXTERNAL_MEDIAL_PLAYER"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_EXTERNAL_MEDIAL_PLAYER_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->external_media_player_features)) {
            $data = (array)$item->room_metadata->external_media_player_features;
        }

        return $this->formatHtml($externalMediaPlayerFeatures, "external_media_player_features", $data);
    }

    public function getWaitingRoomFeatures()
    {
        $waitingRoomFeatures = array(
            "is_active" => array(
                "label" => JText::_("COM_PLUGNMEET_ACTIVATE_WAITING_ROOM"),
                "des" => JText::_("COM_PLUGNMEET_ACTIVATE_WAITING_ROOM_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "waiting_room_msg" => array(
                "label" => JText::_("COM_PLUGNMEET_WAITING_ROOM_MESSAGE"),
                "des" => JText::_("COM_PLUGNMEET_WAITING_ROOM_MESSAGE_DES"),
                "default" => "",
                "type" => "textarea"
            )
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->waiting_room_features)) {
            $data = (array)$item->room_metadata->waiting_room_features;
        }

        return $this->formatHtml($waitingRoomFeatures, "waiting_room_features", $data);
    }

    public function getBreakoutRoomFeatures()
    {
        $breakoutRoomFeatures = array(
            "is_allow" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_BREAKOUT_ROOMS"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_BREAKOUT_ROOMS_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "allowed_number_rooms" => array(
                "label" => JText::_("COM_PLUGNMEET_NUMBER_OF_ROOMS"),
                "des" => JText::_("COM_PLUGNMEET_NUMBER_OF_ROOMS_DES"),
                "default" => 6,
                "type" => "number"
            )
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->breakout_room_features)) {
            $data = (array)$item->room_metadata->breakout_room_features;
        }

        return $this->formatHtml($breakoutRoomFeatures, "breakout_room_features", $data);
    }

    public function getDisplayExternalLinkFeatures()
    {
        $displayExternalLinkFeatures = array(
            "is_allow" => array(
                "label" => JText::_("COM_PLUGNMEET_ALLOW_DISPLAY_EXTERNAL_LINK"),
                "des" => JText::_("COM_PLUGNMEET_ALLOW_DISPLAY_EXTERNAL_LINK_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            )
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->display_external_link_features)) {
            $data = (array)$item->room_metadata->display_external_link_features;
        }

        return $this->formatHtml($displayExternalLinkFeatures, "display_external_link_features", $data);
    }

    public function getDefaultLockSettings()
    {
        $defaultLockSettings = array(
            "lock_microphone" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_MICROPHONE"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_MICROPHONE_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "lock_webcam" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_WEBCAM"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_WEBCAM_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "lock_screen_sharing" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_SCREEN_SHARING"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_SCREEN_SHARING_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "lock_whiteboard" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_WHITEBOARD"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_WHITEBOARD_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "lock_shared_notepad" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_SHARED_NOTEPAD"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_SHARED_NOTEPAD_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 1,
                "type" => "select"
            ),
            "lock_chat" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_CHAT"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_CHAT_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "lock_chat_send_message" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_CHAT_SEND_MESSAGE"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_CHAT_SEND_MESSAGE_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "lock_chat_file_share" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_CHAT_SEND_FILE"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_CHAT_SEND_FILE_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
            "lock_chat_file_share" => array(
                "label" => JText::_("COM_PLUGNMEET_LOCK_PRIVATE_CHAT"),
                "des" => JText::_("COM_PLUGNMEET_LOCK_PRIVATE_CHAT_DES"),
                "options" => array(
                    array(
                        "label" => JText::_("COM_PLUGNMEET_YES"),
                        "value" => 1
                    ), array(
                        "label" => JText::_("COM_PLUGNMEET_NO"),
                        "value" => 0
                    )),
                "selected" => 0,
                "type" => "select"
            ),
        );

        $item = $this->getItem();
        $data = [];
        if (isset($item->room_metadata->default_lock_settings)) {
            $data = (array)$item->room_metadata->default_lock_settings;
        }

        return $this->formatHtml($defaultLockSettings, "default_lock_settings", $data);
    }

    public function getDesignCustomization()
    {
        $item = $this->getItem();
        $form = JForm::getInstance("custom_design", JPATH_ADMINISTRATOR . "/components/com_plugnmeet/models/forms/design_fields.xml", array("control" => "jform"));

        if (isset($item->room_metadata->custom_design)) {
            $form->bind((array)$item->room_metadata->custom_design);
        }

        return $form->renderFieldset('plugnmeet_design_customization');
    }/***[/JCBGUI$$$$]***/

    
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->params) && !is_array($item->params))
			{
				// Convert the params field to an array.
				$registry = new Registry;
				$registry->loadString($item->params);
				$item->params = $registry->toArray();
			}

			if (!empty($item->metadata))
			{
				// Convert the metadata field to an array.
				$registry = new Registry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			if (!empty($item->room_metadata))
			{
				// JSON Decode room_metadata.
				$item->room_metadata = json_decode($item->room_metadata);
			}


/***[JCBGUI.admin_view.php_getitem.110.$$$$]***/
            if (empty($item->moderator_pass)) {
                $item->moderator_pass = PlugnmeetHelper::secureRandomKey(8);
            }
            if (empty($item->attendee_pass)) {
                $item->attendee_pass = PlugnmeetHelper::secureRandomKey(8);
            }
            if (empty($item->room_id) || $item->room_id == 1) {
                if (!class_exists("plugNmeetConnect")) {
                    include JPATH_ROOT . "/administrator/components/com_plugnmeet/helpers/plugNmeetConnect.php";
                }
                $connect = new plugNmeetConnect();
                $item->room_id = $connect->getUUID();
            }/***[/JCBGUI$$$$]***/

		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   array    $options   Optional array of options for the form creation.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true, $options = array('control' => 'jform'))
	{
		// set load data option
		$options['load_data'] = $loadData;
		// check if xpath was set in options
		$xpath = false;
		if (isset($options['xpath']))
		{
			$xpath = $options['xpath'];
			unset($options['xpath']);
		}
		// check if clear form was set in options
		$clear = false;
		if (isset($options['clear']))
		{
			$clear = $options['clear'];
			unset($options['clear']);
		}

		// Get the form.
		$form = $this->loadForm('com_plugnmeet.room', 'room', $options, $clear, $xpath);

		if (empty($form))
		{
			return false;
		}

		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0, 'INT');
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0, 'INT');
		}

		$user = JFactory::getUser();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_plugnmeet.room.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_plugnmeet')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		// If this is a new item insure the greated by is set.
		if (0 == $id)
		{
			// Set the created_by to this user
			$form->setValue('created_by', null, $user->id);
		}
		// Modify the form based on Edit Creaded By access controls.
		if (!$user->authorise('core.edit.created_by', 'com_plugnmeet'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'readonly', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		// Modify the form based on Edit Creaded Date access controls.
		if (!$user->authorise('core.edit.created', 'com_plugnmeet'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created', 'filter', 'unset');
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// Set redirected view name
			$redirectedView = $jinput->get('ref', null, 'STRING');
			// Set field name (or fall back to view name)
			$redirectedField = $jinput->get('field', $redirectedView, 'STRING');
			// Set redirected view id
			$redirectedId = $jinput->get('refid', 0, 'INT');
			// Set field id (or fall back to redirected view id)
			$redirectedValue = $jinput->get('field_id', $redirectedId, 'INT');
			if (0 != $redirectedValue && $redirectedField)
			{
				// Now set the local-redirected field default value
				$form->setValue($redirectedField, null, $redirectedValue);
			}
		}
		return $form;
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	script files
	 */
	public function getScript()
	{
		return 'media/com_plugnmeet/js/room.js';
	}
    
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->published != -2)
			{
				return;
			}

			$user = JFactory::getUser();
			// The record has been set. Check the record permissions.
			return $user->authorise('core.delete', 'com_plugnmeet.room.' . (int) $record->id);
		}
		return false;
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		$recordId = (!empty($record->id)) ? $record->id : 0;

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('core.edit.state', 'com_plugnmeet.room.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absense of better information, revert to the component permissions.
		return parent::canEditState($record);
	}
    
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	2.5
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.

		return JFactory::getUser()->authorise('core.edit', 'com_plugnmeet.room.'. ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (isset($table->name))
		{
			$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
		}
		
		if (isset($table->alias) && empty($table->alias))
		{
			$table->generateAlias();
		}
		
		if (empty($table->id))
		{
			$table->created = $date->toSql();
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__plugnmeet_room'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql();
			$table->modified_by = $user->id;
		}
        
		if (!empty($table->id))
		{
			// Increment the items version number.
			$table->version++;
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_plugnmeet.edit.room.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
			// run the perprocess of the data
			$this->preprocessData('com_plugnmeet.room', $data);
		}

		return $data;
	}

	/**
	 * Method to get the unique fields of this table.
	 *
	 * @return  mixed  An array of field names, boolean false if none is set.
	 *
	 * @since   3.0
	 */
	protected function getUniqueFields()
	{
		return array('room_id');
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!parent::delete($pks))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}
		
		return true;
        }
    
	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize ids.
		$pks = array_unique($pks);
		ArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		// Set some needed variables.
		$this->user			= JFactory::getUser();
		$this->table			= $this->getTable();
		$this->tableClassName		= get_class($this->table);
		$this->contentType		= new JUcmType;
		$this->type			= $this->contentType->getTypeByTable($this->tableClassName);
		$this->canDo			= PlugnmeetHelper::getActions('room');
		$this->batchSet			= true;

		if (!$this->canDo->get('core.batch'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}
        
		if ($this->type == false)
		{
			$type = new JUcmType;
			$this->type = $type->getTypeByAlias($this->typeAlias);
		}

		$this->tagsObserver = $this->table->getObserverOfClass('JTableObserverTags');

		if (!empty($commands['move_copy']))
		{
			$cmd = ArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands, $pks, $contexts);

				if (is_array($result))
				{
					foreach ($result as $old => $new)
					{
						$contexts[$new] = $contexts[$old];
					}
					$pks = array_values($result);
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands, $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $values    The new values.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since 12.2
	 */
	protected function batchCopy($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user 		= JFactory::getUser();
			$this->table 		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= PlugnmeetHelper::getActions('room');
		}

		if (!$this->canDo->get('core.create') || !$this->canDo->get('core.batch'))
		{
			return false;
		}

		// get list of unique fields
		$uniqueFields = $this->getUniqueFields();
		// remove move_copy from array
		unset($values['move_copy']);

		// make sure published is set
		if (!isset($values['published']))
		{
			$values['published'] = 0;
		}
		elseif (isset($values['published']) && !$this->canDo->get('core.edit.state'))
		{
				$values['published'] = 0;
		}

		if (isset($values['category']) && (int) $values['category'] > 0 && !static::checkCategoryId($values['category']))
		{
			return false;
		}
		elseif (isset($values['category']) && (int) $values['category'] > 0)
		{
			// move the category value to correct field name
			$values['catid'] = $values['category'];
			unset($values['category']);
		}
		elseif (isset($values['category']))
		{
			unset($values['category']);
		}

		$newIds = array();
		// Parent exists so let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// only allow copy if user may edit this item.
			if (!$this->user->authorise('core.edit', $contexts[$pk]))
			{
				// Not fatal error
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
				continue;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			if (isset($values['catid']))
			{
				list($this->table->room_title, $this->table->alias) = $this->generateNewTitle($values['catid'], $this->table->alias, $this->table->room_title);
			}
			else
			{
				list($this->table->room_title, $this->table->alias) = $this->generateNewTitle($this->table->catid, $this->table->alias, $this->table->room_title);
			}

			// insert all set values
			if (PlugnmeetHelper::checkArray($values))
			{
				foreach ($values as $key => $value)
				{
					if (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}

			// update all unique fields
			if (PlugnmeetHelper::checkArray($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					$this->table->$uniqueField = $this->generateUnique($uniqueField,$this->table->$uniqueField);
				}
			}

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// TODO: Deal with ordering?
			// $this->table->ordering = 1;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$pk] = $newId;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch move items to a new category
	 *
	 * @param   integer  $value     The new category ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since 12.2
	 */
	protected function batchMove($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user		= JFactory::getUser();
			$this->table		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= PlugnmeetHelper::getActions('room');
		}

		if (!$this->canDo->get('core.edit') && !$this->canDo->get('core.batch'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
			return false;
		}

		// make sure published only updates if user has the permission.
		if (isset($values['published']) && !$this->canDo->get('core.edit.state'))
		{
			unset($values['published']);
		}
		// remove move_copy from array
		unset($values['move_copy']);

		if (isset($values['category']) && (int) $values['category'] > 0 && !static::checkCategoryId($values['category']))
		{
			return false;
		}
		elseif (isset($values['category']) && (int) $values['category'] > 0)
		{
			// move the category value to correct field name
			$values['catid'] = $values['category'];
			unset($values['category']);
		}
		elseif (isset($values['category']))
		{
			unset($values['category']);
		}


		// Parent exists so we proceed
		foreach ($pks as $pk)
		{
			if (!$this->user->authorise('core.edit', $contexts[$pk]))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// insert all set values.
			if (PlugnmeetHelper::checkArray($values))
			{
				foreach ($values as $key => $value)
				{
					// Do special action for access.
					if ('access' === $key && strlen($value) > 0)
					{
						$this->table->$key = $value;
					}
					elseif (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}


			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
	
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input	= JFactory::getApplication()->input;
		$filter	= JFilterInput::getInstance();
        
		// set the metadata to the Item Data
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
            
			$metadata = new JRegistry;
			$metadata->loadArray($data['metadata']);
			$data['metadata'] = (string) $metadata;
		}


/***[JCBGUI.admin_view.php_before_save.110.$$$$]***/
        $jform = $input->post->get("jform", array(), 'array');
        $data['room_metadata'] = array();

        if (isset($jform['room_features'])) {
            $data['room_metadata']['room_features'] = $jform['room_features'];
        }
        if (isset($jform['chat_features'])) {
            $data['room_metadata']['chat_features'] = $jform['chat_features'];
        }
        if (isset($jform['shared_note_pad_features'])) {
            $data['room_metadata']['shared_note_pad_features'] = $jform['shared_note_pad_features'];
        }
        if (isset($jform['whiteboard_features'])) {
            $data['room_metadata']['whiteboard_features'] = $jform['whiteboard_features'];
        }
        if (isset($jform['external_media_player_features'])) {
            $data['room_metadata']['external_media_player_features'] = $jform['external_media_player_features'];
        }
        if (isset($jform['waiting_room_features'])) {
            $data['room_metadata']['waiting_room_features'] = $jform['waiting_room_features'];
        }
        if (isset($jform['breakout_room_features'])) {
            $data['room_metadata']['breakout_room_features'] = $jform['breakout_room_features'];
        }
        if (isset($jform['display_external_link_features'])) {
            $data['room_metadata']['display_external_link_features'] = $jform['display_external_link_features'];
        }
        if (isset($jform['default_lock_settings'])) {
            $data['room_metadata']['default_lock_settings'] = $jform['default_lock_settings'];
        }

        $data['room_metadata']['custom_design'] = array(
            'custom_css_url' => $jform['custom_css_url'],
            'primary_color' => $jform['primary_color'],
            'secondary_color' => $jform['secondary_color'],
            'background_color' => $jform['background_color'],
            'background_image' => $jform['background_image'],
            'logo' => $jform['logo'],
            'header_color' => $jform['header_color'],
            'footer_color' => $jform['footer_color'],
            'left_color' => $jform['left_color'],
            'right_color' => $jform['right_color'],
        );

        if ($data['moderator_pass'] === $data['attendee_pass']) {
            $msg = JText::_("COM_PLUGNMEET_MODERATOR_AND_ATTENDEE_PASSWORD_CANT_BE_SAME");
            JFactory::getApplication()->enqueueMessage($msg, 'warning');
            return false;
        }/***[/JCBGUI$$$$]***/


		// Set the room_metadata string to JSON string.
		if (isset($data['room_metadata']))
		{
			$data['room_metadata'] = (string) json_encode($data['room_metadata']);
		}
        
		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new JRegistry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
		}

		// Alter the room_title for save as copy
		if ($input->get('task') === 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['room_title'] == $origTable->room_title)
			{
				list($room_title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['room_title']);
				$data['room_title'] = $room_title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['published'] = 0;
		}

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (int) $input->get('id') == 0)
		{
			if ($data['alias'] == null || empty($data['alias']))
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['room_title']);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($data['room_title']);
				}

				$table = JTable::getInstance('room', 'plugnmeetTable');

				if ($table->load(array('alias' => $data['alias'], 'catid' => $data['catid'])) && ($table->id != $data['id'] || $data['id'] == 0))
				{
					$msg = JText::_('COM_PLUGNMEET_ROOM_SAVE_WARNING');
				}

				list($room_title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['room_title']);
				$data['alias'] = $alias;

				if (isset($msg))
				{
					JFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		// Alter the unique field for save as copy
		if ($input->get('task') === 'save2copy')
		{
			// Automatic handling of other unique fields
			$uniqueFields = $this->getUniqueFields();
			if (PlugnmeetHelper::checkArray($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					$data[$uniqueField] = $this->generateUnique($uniqueField,$data[$uniqueField]);
				}
			}
		}
		
		if (parent::save($data))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Method to generate a unique value.
	 *
	 * @param   string  $field name.
	 * @param   string  $value data.
	 *
	 * @return  string  New value.
	 *
	 * @since   3.0
	 */
	protected function generateUnique($field,$value)
	{

		// set field value unique
		$table = $this->getTable();

		while ($table->load(array($field => $value)))
		{
			$value = StringHelper::increment($value);
		}

		return $value;
	}

	/**
	 * Method to change the title/s & alias.
	 *
	 * @param   string         $alias        The alias.
	 * @param   string/array   $title        The title.
	 *
	 * @return	array/string  Contains the modified title/s and/or alias.
	 *
	 */
	protected function _generateNewTitle($alias, $title = null)
	{

		// Alter the title/s & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias)))
		{
			// Check if this is an array of titles
			if (PlugnmeetHelper::checkArray($title))
			{
				foreach($title as $nr => &$_title)
				{
					$_title = StringHelper::increment($_title);
				}
			}
			// Make sure we have a title
			elseif ($title)
			{
				$title = StringHelper::increment($title);
			}
			$alias = StringHelper::increment($alias, 'dash');
		}
		// Check if this is an array of titles
		if (PlugnmeetHelper::checkArray($title))
		{
			$title[] = $alias;
			return $title;
		}
		// Make sure we have a title
		elseif ($title)
		{
			return array($title, $alias);
		}
		// We only had an alias
		return $alias;
	}
}
