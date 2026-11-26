<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\Helper;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

class PlugnmeetHelper
{
	public static $roomMetadataItems = array("room_mode", "room_features", "recording_features", "chat_features", "shared_note_pad_features", "whiteboard_features", "external_media_player_features", "waiting_room_features", "breakout_room_features", "display_external_link_features", "ingress_features", "end_to_end_encryption_features", "insights_features", "polls_features", "sip_dial_in_features", "external_broadcasting_features", "default_lock_settings", "advanced_settings");

	public static function getActions()
	{
		$user   = Factory::getApplication()->getIdentity();
		$result = new Registry(null, "_");

		$assetName = 'com_plugnmeet';
		$actions   = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function secureRandomKey(int $length = 40): string
	{
		try
		{
			$keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$pieces   = [];
			$max      = mb_strlen($keyspace, '8bit') - 1;
			for ($i = 0; $i < $length; ++$i)
			{
				$pieces [] = $keyspace[random_int(0, $max)];
			}

			return implode('', $pieces);

		}
		catch (\Exception $exception)
		{

		}

		return UserHelper::genRandomPassword($length);
	}

	public static function getRoomMode(array $room_metadata): string
	{
		$mode = isset($room_metadata['room_mode']) ? (string) $room_metadata['room_mode'] : 'conference';

		if ($mode === 'ai_tutor')
		{
			return 'ai_agent';
		}

		return $mode === 'ai_agent' ? 'ai_agent' : 'conference';
	}

	public static function isAiAgentEnabled(): bool
	{
		$value = ComponentHelper::getParams('com_plugnmeet')->get('enable_ai_agent', 1);

		return '0' !== (string) $value;
	}

	public static function getAiAgentPresets(): array
	{
		return array(
			'custom' => array(
				'label'  => Text::_('COM_PLUGNMEET_AI_AGENT_PRESET_CUSTOM'),
				'prompt' => '',
			),
			'customer_support' => array(
				'label'  => Text::_('COM_PLUGNMEET_AI_AGENT_PRESET_CUSTOMER_SUPPORT'),
				'prompt' => 'You are a friendly customer support specialist for our product. Greet the customer and ask what they need help with. Answer in short, clear steps and use the shared notepad to list them. If you do not know an answer, say so and offer the next best step. Ask one question at a time and confirm at the end that the issue is resolved.',
			),
			'sales_demo' => array(
				'label'  => Text::_('COM_PLUGNMEET_AI_AGENT_PRESET_SALES_DEMO'),
				'prompt' => 'You are a product specialist giving an interactive demo. Start by asking about the visitor\'s goals, then walk through only the features that match those goals. Use the whiteboard to sketch the workflow (for example a poll with two options) and keep every step short. Invite questions after each step and finish with a clear recommendation and the next step to take.',
			),
			'tutor_whiteboard' => array(
				'label'  => Text::_('COM_PLUGNMEET_AI_AGENT_PRESET_TUTOR_WHITEBOARD'),
				'prompt' => 'You are a patient 1-on-1 tutor. Ask the learner what they want to understand, then explain the concept in small steps and use the whiteboard for diagrams and examples. Check understanding after each step with a short question, correct mistakes kindly, and stop when the learner can explain the concept back to you.',
			),
			'language_coach' => array(
				'label'  => Text::_('COM_PLUGNMEET_AI_AGENT_PRESET_LANGUAGE_COACH'),
				'prompt' => 'You are a friendly language coach running a conversation practice session. Speak in the target language at a level the learner can follow, keep the conversation going with open questions, and gently repeat the correct form after a mistake. Introduce one new phrase per topic and give short, encouraging feedback at the end.',
			),
			'interview_screener' => array(
				'label'  => Text::_('COM_PLUGNMEET_AI_AGENT_PRESET_INTERVIEW_SCREENER'),
				'prompt' => 'You are conducting a structured screening interview. Ask one question at a time about the candidate\'s experience and the skills required for the role, and ask a follow-up when an answer is vague. Take notes in the shared notepad. Close the session by summarizing the candidate\'s strengths and the open points the hiring team should verify.',
			),
		);
	}

	public static function hasEnabledMcpServer(?array $settings = null): bool
	{
		$settings = $settings ?? ComponentHelper::getParams('com_plugnmeet')->toArray();

		for ($i = 1; $i <= 2; $i++)
		{
			if (!empty($settings['ai_mcp_' . $i . '_enabled']))
			{
				return true;
			}
		}

		return false;
	}

	public static function sanitizeUserIds($ids): array
	{
		if (!is_array($ids))
		{
			return array();
		}

		$ids = array_map(static fn($id) => abs((int) $id), $ids);

		return array_slice(array_values(array_unique(array_filter($ids))), 0, 32);
	}

	public static function getUsersByIds(array $ids): array
	{
		$ids = self::sanitizeUserIds($ids);

		if (empty($ids))
		{
			return array();
		}

		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'name', 'username', 'email')))
			->from($db->quoteName('#__users'))
			->whereIn($db->quoteName('id'), $ids)
			->order($db->quoteName('name') . ' ASC');

		return $db->setQuery($query)->loadObjectList() ?: array();
	}

	public static function prepareAiAgentFeatures(array &$room_metadata, array $settings = []): void
	{
		if (empty($settings))
		{
			$settings = ComponentHelper::getParams('com_plugnmeet')->toArray();
		}

		$room_mode       = self::getRoomMode($room_metadata);
		$enable_ai_agent = !isset($settings['enable_ai_agent']) || '0' !== (string) $settings['enable_ai_agent'];

		unset($room_metadata['room_mode']);

		$insights = isset($room_metadata['insights_features']) && is_array($room_metadata['insights_features']) ? $room_metadata['insights_features'] : array();
		$ai       = isset($insights['ai_agent_features']) && is_array($insights['ai_agent_features']) ? $insights['ai_agent_features'] : array();

		$enable_mcp = !isset($ai['enable_mcp_servers']) || !empty($ai['enable_mcp_servers']);
		unset($ai['preset_template'], $ai['enable_mcp_servers'], $ai['is_enabled'], $ai['active_session_id']);

		$is_ai_agent_mode = $enable_ai_agent && 'ai_agent' === $room_mode;
		$is_conference_ai = $enable_ai_agent && !$is_ai_agent_mode && !empty($ai['is_allow']);

		if (!$is_ai_agent_mode && !$is_conference_ai)
		{
			$insights['ai_agent_features'] = array('is_allow' => false);

			if ($enable_mcp)
			{
				self::injectTextChatMcpServers($insights, $settings);
			}

			$room_metadata['insights_features'] = $insights;

			return;
		}

		// AI driven sessions always run with insights and breakout rooms enabled.
		if (!isset($room_metadata['room_features']) || !is_array($room_metadata['room_features']))
		{
			$room_metadata['room_features'] = array();
		}

		$room_metadata['room_features']['auto_gen_user_id'] = false;
		$room_metadata['room_features']['breakout_room_features']['is_allow'] = true;
		$room_metadata['room_features']['end_to_end_encryption_features']['is_enabled'] = false;
		$room_metadata['room_features']['end_to_end_encryption_features']['enabled_self_insert_encryption_key'] = false;
		$room_metadata['breakout_room_features']['is_allow'] = true;
		$room_metadata['end_to_end_encryption_features']['enabled_self_insert_encryption_key'] = false;
		$insights['is_allow'] = true;
		$ai['is_allow']       = true;

		if ($is_ai_agent_mode)
		{
			$room_metadata['room_features']['moderator_join_first'] = false;

			// Workspace tools the agent needs to run a session on its own.
			$room_metadata['whiteboard_features']['is_allow']       = true;
			$room_metadata['shared_note_pad_features']['is_allow']  = true;
			$room_metadata['polls_features']['is_allow']            = true;
			$room_metadata['chat_features']['is_allow']             = true;
			$room_metadata['room_features']['enable_analytics']     = true;

			// 1 = controlled session for selected users, 2 = open session per user.
			$ai['session_mode'] = 1 === (int) ($ai['session_mode'] ?? 2) ? 1 : 2;

			if (1 === $ai['session_mode'])
			{
				$allowed_ids = self::sanitizeUserIds($ai['auto_session_config']['allowed_participant_ids'] ?? array());

				$ai['auto_session_config'] = array(
					'allowed_participant_ids' => array_map('strval', $allowed_ids),
					'auto_start_agent'        => true,
				);
			}
			else
			{
				$limit          = max(1, (int) ($settings['ai_max_concurrent_sessions_limit'] ?? 10));
				$max_concurrent = (int) ($ai['auto_session_config']['max_concurrent_sessions'] ?? 1);

				$ai['auto_session_config'] = array(
					'max_concurrent_sessions' => max(1, min($limit, $max_concurrent)),
					'auto_start_agent'        => true,
				);
			}
		}
		else
		{
			// Normal room with conference AI: no autonomous session.
			$ai['session_mode'] = 0;
			unset($ai['auto_session_config']);
		}

		$ai['agent_display_name'] = trim((string) ($ai['agent_display_name'] ?? '')) ?: Text::_('COM_PLUGNMEET_AI_AGENT_DISPLAY_NAME_DEFAULT');
		$ai['topic']              = mb_substr(trim((string) ($ai['topic'] ?? '')), 0, 200);
		$ai['system_prompt']      = trim((string) ($ai['system_prompt'] ?? ''));
		$ai['allowed_subtopics']  = self::parseStringList($ai['allowed_subtopics'] ?? '', 64, 160);
		$ai['forbidden_topics']   = self::parseStringList($ai['forbidden_topics'] ?? '', 32, 160);

		// Proto3 drops false booleans, so always send them explicitly.
		foreach (array('strict_topic_enforcement', 'allow_whiteboard_write', 'allow_vision_capture', 'allow_polls', 'allow_notepad_write', 'allow_chat_message') as $key)
		{
			$ai[$key] = !isset($ai[$key]) || !empty($ai[$key]);
		}

		if (!empty($settings['ai_allow_web_fetch_global']) && !empty($ai['allow_web_fetch']))
		{
			$ai['allow_web_fetch']        = true;
			$ai['allowed_knowledge_urls'] = self::parseUrlList($ai['allowed_knowledge_urls'] ?? '');
		}
		else
		{
			$ai['allow_web_fetch']        = false;
			$ai['allowed_knowledge_urls'] = array();
		}

		$ai['topic_anchor_interval_turns'] = (int) max(3, min(12, (int) ($ai['topic_anchor_interval_turns'] ?? 6)));
		$speaking_rate                     = (float) ($ai['speaking_rate'] ?? 1.0);
		$ai['speaking_rate']               = max(0.5, min(2.0, $speaking_rate > 0 ? $speaking_rate : 1.0));

		// One duration in minutes drives the session, the AI audio budget and the avatar budget.
		$minutes_limit   = min(120, max(1, (int) ($settings['ai_max_session_minutes_limit'] ?? 60)));
		$default_minutes = max(1, min($minutes_limit, (int) ($settings['ai_default_max_session_minutes'] ?? 30)));

		if (isset($ai['max_session_minutes']))
		{
			$session_minutes = (int) $ai['max_session_minutes'];
		}
		elseif (isset($ai['max_session_seconds']))
		{
			$session_minutes = (int) round(((int) $ai['max_session_seconds']) / 60);
		}
		else
		{
			$session_minutes = $default_minutes;
		}

		$session_minutes = max(1, min($minutes_limit, $session_minutes));
		unset($ai['max_session_minutes']);
		$ai['max_session_seconds'] = $session_minutes * 60;
		$ai['max_audio_minutes']   = $session_minutes;

		if (!empty($settings['ai_avatar_enabled_global']) && is_array($ai['avatar'] ?? null) && !empty($ai['avatar']['is_enabled']))
		{
			$avatar    = array(
				'is_enabled'         => true,
				'max_avatar_minutes' => $session_minutes,
			);
			$providers = array('beyond_presence', 'anam', 'simli');
			$byok      = !empty($settings['ai_avatar_custom_enabled']);

			$avatar_provider = $byok ? (string) ($settings['ai_avatar_provider'] ?? '') : '';
			if (in_array($avatar_provider, $providers, true))
			{
				$avatar['provider'] = $avatar_provider;
			}

			if ($byok)
			{
				$avatar_id = mb_substr(trim((string) ($settings['ai_avatar_id'] ?? '')), 0, 256);

				if ('' !== $avatar_id)
				{
					$avatar['avatar_id'] = $avatar_id;
				}
			}

			if ($byok && '' !== trim((string) ($settings['ai_avatar_api_key'] ?? '')))
			{
				$avatar['api_key'] = trim((string) $settings['ai_avatar_api_key']);

				foreach (array('endpoint' => 'ai_avatar_endpoint', 'auth_header' => 'ai_avatar_auth_header') as $key => $setting_key)
				{
					$value = trim((string) ($settings[$setting_key] ?? ''));

					if ('' !== $value)
					{
						$avatar[$key] = $value;
					}
				}
			}

			$ai['avatar'] = $avatar;
		}
		else
		{
			unset($ai['avatar']);
		}

		// Activity quotas are meaningless without the matching tool.
		$rules         = isset($ai['activity_rules']) && is_array($ai['activity_rules']) ? $ai['activity_rules'] : array();
		$min_wb        = $ai['allow_whiteboard_write'] ? max(0, min(20, (int) ($rules['min_whiteboard_drawings'] ?? 0))) : 0;
		$min_quizzes   = $ai['allow_polls'] ? max(0, min(20, (int) ($rules['min_quizzes'] ?? 0))) : 0;
		$min_notepad   = $ai['allow_notepad_write'] ? max(0, min(20, (int) ($rules['min_notepad_entries'] ?? 0))) : 0;
		$min_exercises = max(0, min(20, (int) ($rules['min_participant_exercises'] ?? 0)));
		$milestones    = self::parseStringList($rules['required_milestones'] ?? '', 16, 240);

		if ($min_wb > 0 || $min_quizzes > 0 || $min_notepad > 0 || $min_exercises > 0 || !empty($milestones))
		{
			$ai['activity_rules'] = array(
				'min_whiteboard_drawings'   => $min_wb,
				'min_quizzes'               => $min_quizzes,
				'min_notepad_entries'       => $min_notepad,
				'min_participant_exercises' => $min_exercises,
				'required_milestones'       => $milestones,
			);
		}
		else
		{
			unset($ai['activity_rules']);
		}

		$custom_provider = self::buildCustomProviderConfig($settings);

		if ($custom_provider)
		{
			$ai['custom_provider'] = $custom_provider;
		}
		else
		{
			unset($ai['custom_provider']);
		}

		if ($enable_mcp)
		{
			$text_chat_enabled = !empty($insights['ai_features']['is_allow']) && !empty($insights['ai_features']['ai_text_chat_features']['is_allow']);

			foreach (self::buildMcpServersConfig($settings) as $name => $server)
			{
				if (in_array($server['scope'], array('ai_agent', 'shared'), true))
				{
					$ai['mcp_servers'][$name] = $server['config'];
				}

				if ($text_chat_enabled && in_array($server['scope'], array('text_chat', 'shared'), true))
				{
					$insights['ai_features']['ai_text_chat_features']['mcp_servers'][$name] = $server['config'];
				}
			}
		}

		$insights['ai_agent_features']      = $ai;
		$room_metadata['insights_features'] = $insights;
	}

	private static function injectTextChatMcpServers(array &$insights, array $settings): void
	{
		if (empty($insights['is_allow'])
			|| empty($insights['ai_features']['is_allow'])
			|| empty($insights['ai_features']['ai_text_chat_features']['is_allow']))
		{
			return;
		}

		foreach (self::buildMcpServersConfig($settings) as $name => $server)
		{
			if (in_array($server['scope'], array('text_chat', 'shared'), true))
			{
				$insights['ai_features']['ai_text_chat_features']['mcp_servers'][$name] = $server['config'];
			}
		}
	}

	private static function buildCustomProviderConfig(array $settings): array
	{
		$api_key = trim((string) ($settings['ai_custom_api_key'] ?? ''));

		if (empty($settings['ai_custom_provider_enabled']) || '' === $api_key)
		{
			return array();
		}

		$provider = (string) ($settings['ai_custom_provider'] ?? '');
		$config   = array(
			'provider' => in_array($provider, array('google', 'openai'), true) ? $provider : 'google',
			'api_key'  => $api_key,
		);

		$optionals = array(
			'endpoint'         => 'ai_custom_endpoint',
			'auth_header'      => 'ai_custom_auth_header',
			'realtime_model'   => 'ai_custom_realtime_model',
			'delegation_model' => 'ai_custom_delegation_model',
			'summarize_model'  => 'ai_custom_summarize_model',
		);

		foreach ($optionals as $key => $setting)
		{
			$value = trim((string) ($settings[$setting] ?? ''));

			if ('' !== $value)
			{
				$config[$key] = $value;
			}
		}

		return $config;
	}

	private static function buildMcpServersConfig(array $settings): array
	{
		$servers = array();

		for ($i = 1; $i <= 2; $i++)
		{
			if (empty($settings['ai_mcp_' . $i . '_enabled']))
			{
				continue;
			}

			$name = trim((string) ($settings['ai_mcp_' . $i . '_name'] ?? ''));
			$url  = trim((string) ($settings['ai_mcp_' . $i . '_url'] ?? ''));

			if ('' === $name || mb_strlen($name) > 64 || !str_starts_with($url, 'https://'))
			{
				continue;
			}

			$headers = array();
			$decoded = json_decode((string) ($settings['ai_mcp_' . $i . '_headers'] ?? ''), true);

			if (is_array($decoded))
			{
				foreach ($decoded as $header => $value)
				{
					if (count($headers) < 16 && is_scalar($value) && '' !== trim((string) $header))
					{
						$headers[(string) $header] = (string) $value;
					}
				}
			}

			$scope = (string) ($settings['ai_mcp_' . $i . '_scope'] ?? '');

			$servers[$name] = array(
				'scope'  => in_array($scope, array('ai_agent', 'text_chat', 'shared'), true) ? $scope : 'ai_agent',
				'config' => array(
					'url'           => $url,
					'headers'       => $headers,
					'allowed_tools' => self::parseStringList($settings['ai_mcp_' . $i . '_allowed_tools'] ?? '', 8),
				),
			);
		}

		return $servers;
	}

	private static function parseStringList($value, int $max_items, int $max_length = 0): array
	{
		$items = is_array($value) ? $value : preg_split('/[\r\n,]+/', (string) $value);
		$list  = array();

		foreach ($items as $item)
		{
			$item = trim((string) $item);

			if ('' === $item)
			{
				continue;
			}

			$list[] = $max_length > 0 ? mb_substr($item, 0, $max_length) : $item;
		}

		return array_slice(array_values(array_unique($list)), 0, $max_items);
	}

	private static function parseUrlList($value): array
	{
		$items = is_array($value) ? $value : preg_split('/[\r\n]+/', (string) $value);
		$urls  = array();

		foreach ($items as $item)
		{
			$item = trim((string) $item);

			if (str_starts_with($item, 'https://') && mb_strlen($item) <= 1024 && !in_array($item, $urls, true))
			{
				$urls[] = $item;
			}
		}

		return array_slice($urls, 0, 32);
	}
}
