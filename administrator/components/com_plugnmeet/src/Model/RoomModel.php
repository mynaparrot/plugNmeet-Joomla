<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;

class RoomModel extends AdminModel
{
	protected $text_prefix = 'COM_PLUGNMEET';

	public $typeAlias = 'com_plugnmeet.room';

	protected $item = null;

	public function getTable($type = 'Room', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_plugnmeet.room',
			'room',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	public function save($data)
	{
		if (!$this->checkRequiredFields($data))
		{
			return false;
		}

		$app = Factory::getApplication();
		if ($data['alias'] == null)
		{
			if ($app->get('unicodeslugs') == 1)
			{
				$data['alias'] = OutputFilter::stringUrlUnicodeSlug($data['title']);
			}
			else
			{
				$data['alias'] = OutputFilter::stringURLSafe($data['title']);
			}
		}
		else
		{
			if ($app->get('unicodeslugs') == 1)
			{
				$data['alias'] = OutputFilter::stringUrlUnicodeSlug($data['alias']);
			}
			else
			{
				$data['alias'] = OutputFilter::stringURLSafe($data['alias']);
			}
		}

		// Keep the normalized mode in a local variable: the loop below unsets $data['room_mode']
		// (it is part of PlugnmeetHelper::$roomMetadataItems) so it is no longer available afterwards.
		$room_mode         = (!empty($data['room_mode']) && $data['room_mode'] === 'ai_agent') ? 'ai_agent' : 'conference';
		$data['room_mode'] = $room_mode;

		$room_metadata = [];
		foreach (PlugnmeetHelper::$roomMetadataItems as $metadataItem)
		{
			if (isset($data[$metadataItem]))
			{
				$room_metadata[$metadataItem] = $data[$metadataItem];
				unset($data[$metadataItem]);
			}
		}

		if (!$this->validateAiAgentMetadata($room_metadata, $room_mode))
		{
			return false;
		}

		$data["room_metadata"] = $room_metadata;

		return parent::save($data);
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_plugnmeet.edit.room.data', array());

		// Ignore leftovers from another room (abandoned or failed save) so that its
		// values - including room_mode - are never applied to the current room.
		if (!empty($data) && isset($data['id'])
			&& (int) $data['id'] !== Factory::getApplication()->getInput()->getInt('id', 0))
		{
			$data = array();
		}

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			$room_metadata = array();

			if (!empty($item->room_metadata))
			{
				$room_metadata = json_decode($item->room_metadata, true) ?: array();

				foreach (PlugnmeetHelper::$roomMetadataItems as $metadataItem)
				{
					if (isset($room_metadata[$metadataItem]))
					{
						$item->$metadataItem = $room_metadata[$metadataItem];
					}
				}
			}

			$item->room_metadata     = $room_metadata;
			$item->room_mode         = PlugnmeetHelper::getRoomMode($room_metadata);
			$item->insights_features = $this->prepareInsightsFeatures($room_metadata);

			if (!empty($item->design_customisation))
			{
				$item->design_customisation = json_decode($item->design_customisation);
			}
		}

		return $item;
	}

	public function duplicate(&$pks)
	{
		throw new \Exception("not allow");
	}

	private function prepareInsightsFeatures(array $room_metadata): array
	{
		$insights = isset($room_metadata['insights_features']) && is_array($room_metadata['insights_features'])
			? $room_metadata['insights_features']
			: array();
		$ai       = isset($insights['ai_agent_features']) && is_array($insights['ai_agent_features'])
			? $insights['ai_agent_features']
			: array();

		// A room that never saved the AI agent settings must bind an explicit "No".
		// JForm::bind() would otherwise leave the value null and the browser falls back
		// to the first <option> of the is_allow select.
		if (!isset($ai['is_allow']))
		{
			$ai['is_allow'] = 0;
		}

		foreach (array('allowed_subtopics', 'forbidden_topics', 'allowed_knowledge_urls') as $key)
		{
			if (isset($ai[$key]) && is_array($ai[$key]))
			{
				$ai[$key] = implode("\n", $ai[$key]);
			}
		}

		if (isset($ai['activity_rules']['required_milestones']) && is_array($ai['activity_rules']['required_milestones']))
		{
			$ai['activity_rules']['required_milestones'] = implode("\n", $ai['activity_rules']['required_milestones']);
		}

		if (!isset($ai['auto_session_config']) || !is_array($ai['auto_session_config']))
		{
			$ai['auto_session_config'] = array();
		}

		// Joomla's native sql field pre-selects the saved users from this value.
		$ai['auto_session_config']['allowed_participant_ids'] = array_map(
			'strval',
			PlugnmeetHelper::sanitizeUserIds($ai['auto_session_config']['allowed_participant_ids'] ?? array())
		);

		$params          = ComponentHelper::getParams('com_plugnmeet');
		$minutes_limit   = min(120, max(1, (int) $params->get('ai_max_session_minutes_limit', 60)));
		$default_minutes = max(1, min($minutes_limit, (int) $params->get('ai_default_max_session_minutes', 30)));

		if (!isset($ai['session_mode']))
		{
			$ai['session_mode'] = 2;
		}

		if (!isset($ai['max_session_minutes']))
		{
			$ai['max_session_minutes'] = empty($ai['max_session_seconds'])
				? $default_minutes
				: max(1, min($minutes_limit, (int) round(((int) $ai['max_session_seconds']) / 60)));
		}

		if (!isset($ai['auto_session_config']['max_concurrent_sessions']))
		{
			$ai['auto_session_config']['max_concurrent_sessions'] = max(1, (int) $params->get('ai_max_concurrent_sessions_limit', 10));
		}

		$insights['ai_agent_features'] = $ai;

		return $insights;
	}

	private function validateAiAgentMetadata(array &$room_metadata, string $room_mode = 'conference'): bool
	{
		if (!PlugnmeetHelper::isAiAgentEnabled())
		{
			return true;
		}

		$insights = isset($room_metadata['insights_features']) && is_array($room_metadata['insights_features'])
			? $room_metadata['insights_features']
			: array();
		$ai       = isset($insights['ai_agent_features']) && is_array($insights['ai_agent_features'])
			? $insights['ai_agent_features']
			: array();

		if ('ai_agent' !== $room_mode && empty($ai['is_allow']))
		{
			return true;
		}

		$params = ComponentHelper::getParams('com_plugnmeet');
		$auto   = isset($ai['auto_session_config']) && is_array($ai['auto_session_config']) ? $ai['auto_session_config'] : array();
		$avatar = isset($ai['avatar']) && is_array($ai['avatar']) ? $ai['avatar'] : array();
		$rules  = isset($ai['activity_rules']) && is_array($ai['activity_rules']) ? $ai['activity_rules'] : array();

		if ('ai_agent' === $room_mode)
		{
			$ai['is_allow']       = 1;
			$insights['is_allow'] = 1;
		}

		$topic = mb_substr(trim((string) ($ai['topic'] ?? '')), 0, 200);
		if ('' === $topic)
		{
			$this->setError(Text::_('COM_PLUGNMEET_ERR_AI_TOPIC_REQUIRED'));

			return false;
		}

		$prompt = trim((string) ($ai['system_prompt'] ?? ''));
		if ('' === $prompt)
		{
			$this->setError(Text::_('COM_PLUGNMEET_ERR_AI_SYSTEM_PROMPT_REQUIRED'));

			return false;
		}

		$ai['topic']         = $topic;
		$ai['system_prompt'] = $prompt;

		$session_mode = 1 === (int) ($ai['session_mode'] ?? 2) ? 1 : 2;
		$participants = PlugnmeetHelper::sanitizeUserIds($auto['allowed_participant_ids'] ?? array());

		if ('ai_agent' === $room_mode && 1 === $session_mode && empty($participants))
		{
			$this->setError(Text::_('COM_PLUGNMEET_ERR_AI_ALLOWED_USERS_REQUIRED'));

			return false;
		}

		$auto['max_concurrent_sessions'] = max(1, min((int) $params->get('ai_max_concurrent_sessions_limit', 10), (int) ($auto['max_concurrent_sessions'] ?? 10)));
		$auto['allowed_participant_ids'] = array_map('strval', $participants);

		$ai['session_mode']        = $session_mode;
		$ai['auto_session_config'] = $auto;

		$minutes_limit             = min(120, max(1, (int) $params->get('ai_max_session_minutes_limit', 60)));
		$default_minutes           = max(1, min($minutes_limit, (int) $params->get('ai_default_max_session_minutes', 30)));
		$ai['max_session_minutes'] = max(1, min($minutes_limit, (int) ($ai['max_session_minutes'] ?? $default_minutes)));

		unset($ai['max_session_seconds'], $ai['max_audio_minutes']);

		if (empty($params->get('ai_avatar_enabled_global', 0)))
		{
			unset($ai['avatar']);
		}
		else
		{
			$ai['avatar'] = array(
				'is_enabled'         => empty($avatar['is_enabled']) ? 0 : 1,
				'max_avatar_minutes' => max(0, min(120, (int) ($avatar['max_avatar_minutes'] ?? 0))),
			);
		}

		$ai['topic_anchor_interval_turns'] = max(3, min(12, (int) ($ai['topic_anchor_interval_turns'] ?? 5)));
		$ai['speaking_rate']               = round(max(0.5, min(2.0, (float) ($ai['speaking_rate'] ?? 1.0))), 1);

		foreach (array('min_whiteboard_drawings', 'min_quizzes', 'min_notepad_entries', 'min_participant_exercises') as $rule)
		{
			$rules[$rule] = max(0, min(20, (int) ($rules[$rule] ?? 0)));
		}

		$ai['activity_rules'] = $rules;

		if (empty($params->get('ai_allow_web_fetch_global', 0)))
		{
			$ai['allow_web_fetch'] = 0;
			unset($ai['allowed_knowledge_urls']);
		}

		$insights['ai_agent_features']      = $ai;
		$room_metadata['insights_features'] = $insights;

		return true;
	}

	private function checkRequiredFields(array $data)
	{
		$requiredFields = array(
			"room_title"     => "COM_PLUGNMEET_ROOM_TITLE",
			"moderator_pass" => "COM_PLUGNMEET_ROOM_MODERATOR_PASS",
			"attendee_pass"  => "COM_PLUGNMEET_ROOM_ATTENDEE_PASS"
		);
		$app            = Factory::getApplication();
		foreach ($requiredFields as $key => $label)
		{
			if (empty($data[$key]))
			{
				$app->enqueueMessage(Text::sprintf("COM_PLUGNMEET_ERROR_MESSAGE_REQUIRED_FIELD_MISSING", Text::_($label)), 'error');

				return false;
			}
		}

		if ($data["moderator_pass"] == $data["attendee_pass"])
		{
			$app->enqueueMessage(Text::_("COM_PLUGNMEET_ERROR_MESSAGE_SAME_PASS"), 'error');

			return false;
		}

		return true;
	}
}
