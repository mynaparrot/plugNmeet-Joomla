<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\View\Room;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\plugNmeetConnect;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Model\RoomModel;

/**
 * View class for a single Room.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

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
		$this->form  = $model->getForm();

		if (!$this->item->id)
		{
			$this->form->setValue("room_id", null, plugNmeetConnect::generateUuid4());
			$this->form->setValue("moderator_pass", null, PlugnmeetHelper::secureRandomKey(10));
			$this->form->setValue("attendee_pass", null, PlugnmeetHelper::secureRandomKey(10));
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}
		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$user = Factory::getApplication()->getIdentity();

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = PlugnmeetHelper::getActions();

		ToolbarHelper::title(Text::_('COM_PLUGNMEET_TITLE_ROOM'), "generic");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolbarHelper::apply('room.apply');
			ToolbarHelper::save('room.save');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			ToolbarHelper::custom('room.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('room.cancel');
		}
		else
		{
			ToolbarHelper::cancel('room.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
