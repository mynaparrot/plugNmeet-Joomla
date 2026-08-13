<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\View\Artifacts;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;
use Mynaparrot\Component\Plugnmeet\Administrator\Model\ArtifactsModel;

/**
 * View class for a list of Artifacts.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $rooms;
	protected $artifact;
	protected $roomId;
	protected $paged;
	protected $selectedRoomId;
	protected $backUrl;

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
		/** @var ArtifactsModel $model */
		$model  = $this->getModel();
		$layout = $this->getLayout();

		$this->selectedRoomId = '';
		$this->paged          = 1;

		if ($layout === 'details')
		{
			$input      = Factory::getApplication()->getInput();
			$artifactId = $input->getString('artifact_id');
			$roomId     = $input->getString('room_id');
			$paged      = $input->getInt('paged', 1);

			$this->artifact = $model->getArtifactDetails($artifactId, $roomId, $paged);
			$this->roomId   = $roomId;
			$this->paged    = $paged;
			$this->backUrl  = Route::_('index.php?option=com_plugnmeet&view=artifacts&room_id=' . urlencode($roomId) . '&paged=' . $paged);
		}
		else
		{
			$input                = Factory::getApplication()->getInput();
			$this->selectedRoomId = $input->getString('room_id', '');
			$this->paged          = $input->getInt('paged', 1);
			$this->rooms          = $model->getRooms();
		}

		$wa = $this->getDocument()->getWebAssetManager();
		$wa->useStyle('com_plugnmeet.artifacts');
		$wa->useScript('com_plugnmeet.artifacts');

		$user    = Factory::getApplication()->getIdentity();
		$options = array(
			'baseUrl'       => Uri::base(),
			'canDelete'     => $user->authorise('core.delete', 'com_plugnmeet'),
			'initialRoomId' => $this->selectedRoomId,
			'initialPage'   => $this->paged,
			'i18n'          => array(
				'view'               => Text::_('COM_PLUGNMEET_ARTIFACTS_VIEW'),
				'confirmDelete'      => Text::_('COM_PLUGNMEET_ARTIFACT_CONFIRM_DELETE'),
				'totalArtifacts'     => Text::_('COM_PLUGNMEET_ARTIFACTS_TOTAL'),
				'page'               => Text::_('COM_PLUGNMEET_ARTIFACTS_PAGE'),
				'noArtifacts'        => Text::_('COM_PLUGNMEET_ARTIFACTS_NO_ARTIFACTS'),
				'selectRoomRequired' => Text::_('COM_PLUGNMEET_ARTIFACTS_SELECT_ROOM_REQUIRED')
			)
		);

		if ($layout === 'details')
		{
			$options['listUrl'] = $this->backUrl;
		}

		$this->getDocument()->addScriptOptions('plugnmeet.artifacts', $options);

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

		if ($this->getLayout() === 'details')
		{
			ToolbarHelper::title(Text::_('COM_PLUGNMEET_ARTIFACT_DETAILS'), 'generic');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_PLUGNMEET_TITLE_ARTIFACTS'), 'generic');
		}

		/** @var  $toolbar Toolbar */
		$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');

		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_plugnmeet');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_plugnmeet&view=artifacts');
	}
}
