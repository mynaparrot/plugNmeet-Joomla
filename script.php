<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

return new class () implements InstallerScriptInterface {
	private string $minimumJoomla = '5.2.0';
	private string $minimumPhp = '8.0.0';

	public function install(InstallerAdapter $adapter): bool
	{
		return true;
	}

	public function update(InstallerAdapter $adapter): bool
	{
		return true;
	}

	public function uninstall(InstallerAdapter $adapter): bool
	{
		return true;
	}

	public function preflight(string $type, InstallerAdapter $adapter): bool
	{
		if (version_compare(PHP_VERSION, $this->minimumPhp, '<'))
		{
			Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp), 'error');

			return false;
		}

		if (version_compare(JVERSION, $this->minimumJoomla, '<'))
		{
			Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla), 'error');

			return false;
		}

		return true;
	}

	public function postflight(string $type, InstallerAdapter $adapter): bool
	{
		if ($type === 'install')
		{
			/** @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('params') . ' = ' . $db->q('{"plugnmeet_server_url":"https:\/\/demo.plugnmeet.com","plugnmeet_api_key":"plugnmeet","plugnmeet_secret":"zumyyYWqv7KR2kUqvYdq4z4sXg7XTBD2ljT6","sef_ids":0}'))
				->where($db->qn('name') . ' = ' . $db->q("com_plugnmeet"))
				->where($db->qn('type') . ' = ' . $db->q("component"))
				->where($db->qn('element') . ' = ' . $db->q("com_plugnmeet"));
			$db->setQuery($query);

			return $db->execute();
		}

		return true;
	}
};
