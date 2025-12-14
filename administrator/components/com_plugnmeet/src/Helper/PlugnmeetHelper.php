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

use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;

/**
 * Plugnmeet helper.
 *
 * @since  1.0.0
 */
class PlugnmeetHelper
{
	public static $roomMetadataItems = array("room_features", "recording_features", "chat_features", "shared_note_pad_features", "whiteboard_features", "external_media_player_features", "waiting_room_features", "breakout_room_features", "display_external_link_features", "ingress_features", "end_to_end_encryption_features", "insights_features", "polls_features", "default_lock_settings", "advanced_settings");

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  Registry
	 *
	 * @since   1.0.0
	 */
	public static function getActions()
	{
		$user              = Factory::getApplication()->getIdentity();
		$result            = new Registry();
		$result->separator = "_";

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

	/**
	 * @param   int  $length
	 *
	 * @return string
	 *
	 * @since version
	 */
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
}

