<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\View\Recordings;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Model\RecordingsModel;

/**
 * View class for a list of Recordings.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $rooms;
	protected $selectedRoomId;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$input                = Factory::getApplication()->getInput();
		$this->selectedRoomId = $input->getString('room_id', '');
		if (empty($this->selectedRoomId))
		{
			$this->selectedRoomId = $input->getString('roomId', '');
		}

		/** @var RecordingsModel $model */
		$model       = $this->getModel();
		$this->rooms = $model->getRooms();

		$wa = $this->getDocument()->getWebAssetManager();
		$wa->useStyle('com_plugnmeet.recordings');
		$wa->useScript('com_plugnmeet.recordings');

		$user = Factory::getApplication()->getIdentity();
		$this->getDocument()->addScriptOptions(
			'plugnmeet.recordings',
			array(
				'baseUrl'       => Uri::base(),
				'canDelete'     => $user->authorise('core.delete', 'com_plugnmeet'),
				'initialRoomId' => $this->selectedRoomId,
				'i18n'          => array(
					'confirmDelete'      => Text::_('COM_PLUGNMEET_RECORDINGS_CONFIRM_DELETE'),
					'download'           => Text::_('COM_PLUGNMEET_RECORDINGS_DOWNLOAD'),
					'delete'             => Text::_('COM_PLUGNMEET_RECORDINGS_DELETE'),
					'totalRecordings'    => Text::_('COM_PLUGNMEET_RECORDINGS_TOTAL'),
					'page'               => Text::_('COM_PLUGNMEET_RECORDINGS_PAGE'),
					'noRecordings'       => Text::_('COM_PLUGNMEET_RECORDINGS_NO_RECORDINGS'),
					'mergeTitle'         => Text::_('COM_PLUGNMEET_RECORDINGS_CONFIRM_MERGE_TITLE'),
					'mergeMsg'           => Text::_('COM_PLUGNMEET_RECORDINGS_CONFIRM_MERGE_MSG'),
					'cancel'             => Text::_('COM_PLUGNMEET_RECORDINGS_CANCEL'),
					'confirmMerge'       => Text::_('COM_PLUGNMEET_RECORDINGS_CONFIRM_MERGE'),
					'selectRoomRequired' => Text::_('COM_PLUGNMEET_RECORDINGS_SELECT_ROOM_REQUIRED')
				)
			)
		);

		$this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$canDo = PlugnmeetHelper::getActions();
		ToolbarHelper::title(Text::_('COM_PLUGNMEET_TITLE_RECORDINGS'), 'generic');
		/** @var  $toolbar Toolbar */
		$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');

		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_plugnmeet');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_plugnmeet&view=recordings');
	}
}
