<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');

$tabParams = array('active' => 'basic');
if ((int) $this->item->id > 0)
{
    $tabParams["recall"] = true;
}
?>

<form
        action="<?php echo Route::_('index.php?option=com_plugnmeet&layout=edit&id=' . (int) $this->item->id); ?>"
        method="post" enctype="multipart/form-data" name="adminForm" id="room-form"
        class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'roomTab', $tabParams); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'basic', Text::_('COM_PLUGNMEET_ROOM_TAB_BASIC', true)); ?>
    <?php echo $this->form->renderFieldset("basic"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'roomFeatures', Text::_('COM_PLUGNMEET_ROOM_TAB_ROOM_FEATURES', true)); ?>
    <?php echo $this->form->renderFieldset("roomFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("recordingFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("chatFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("sharedNotePadFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("whiteboardFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("externalMediaPlayerFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("displayExternalLinkFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("waitingRoomFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("breakoutRoomFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("ingressFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("pollsFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("sipDialInFeatures"); ?>
    <hr/>
    <?php echo $this->form->renderFieldset("endToEndEncryptionFeatures"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'insightsFeatures', Text::_('COM_PLUGNMEET_ROOM_TAB_INSIGHTS_AI_FEATURES', true)); ?>
    <?php echo $this->form->renderFieldset("insightsFeatures"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'lockSettings', Text::_('COM_PLUGNMEET_ROOM_TAB_LOCK_SETTINGS', true)); ?>
    <?php echo $this->form->renderFieldset("lockSettings"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'designCustomisation', Text::_('COM_PLUGNMEET_ROOM_TAB_DESIGN_CUSTOMISATION', true)); ?>
    <?php echo $this->form->renderFieldset("designCustomisation"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'advancedSettings', Text::_('COM_PLUGNMEET_ROOM_TAB_ADVANCED_SETTINGS', true)); ?>
    <?php echo $this->form->renderFieldset("advancedSettings"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'publishing', Text::_('OM_PNMMANAGER_TAB_PUBLISHING', true)); ?>
    <?php echo $this->form->renderFieldset("publishing"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'plugnmeet')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'accesscontrol', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
        <?php echo $this->form->renderFieldset("accesscontrol"); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id ?? ''; ?>"/>
    <input type="hidden" name="jform[state]" value="<?php echo $this->item->state ?? ''; ?>"/>
    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
