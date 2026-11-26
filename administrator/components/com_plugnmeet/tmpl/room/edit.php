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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate')
        ->useStyle('com_plugnmeet.admin')
        ->useScript('com_plugnmeet.admin');

$params = ComponentHelper::getParams('com_plugnmeet');

$tabParams = array('active' => 'basic', 'recall' => true);

$isAiAgentEnabled = PlugnmeetHelper::isAiAgentEnabled();

// The form (not the database row) is the source of truth: loadFormData() binds the
// session state of a failed save on top of the stored item, so reading the form
// value keeps the selected room_mode after a validation error.
$formRoomMode = $this->form->getValue('room_mode');

if (empty($formRoomMode))
{
    $formRoomMode = $this->item->room_mode ?? 'conference';
}

$roomMode = PlugnmeetHelper::getRoomMode(array('room_mode' => $formRoomMode));

$this->getDocument()->addScriptOptions('plugnmeet.admin', array(
    'aiPresets'              => array_map(static fn($preset) => $preset['prompt'], PlugnmeetHelper::getAiAgentPresets()),
    'allowWebFetchGlobal'    => !empty($params->get('ai_allow_web_fetch_global', 0)),
    'avatarEnabledGlobal'    => !empty($params->get('ai_avatar_enabled_global', 0)),
    'hasMcpServers'          => PlugnmeetHelper::hasEnabledMcpServer(),
    'maxConcurrentLimit'     => max(1, (int) $params->get('ai_max_concurrent_sessions_limit', 10)),
    'maxSessionMinutesLimit' => min(120, max(1, (int) $params->get('ai_max_session_minutes_limit', 60))),
    'i18n'                   => array(
        'ai_agent_tab'    => Text::_('COM_PLUGNMEET_AI_AGENT_TAB'),
        'insights_tab'    => Text::_('COM_PLUGNMEET_INSIGHTS_TAB'),
        'replace_prompt'  => Text::_('COM_PLUGNMEET_AI_AGENT_REPLACE_PROMPT'),
    ),
));
?>

<form
        action="<?php echo Route::_('index.php?option=com_plugnmeet&layout=edit&id=' . (int) $this->item->id); ?>"
        method="post" enctype="multipart/form-data" name="adminForm" id="room-form"
        class="form-validate form-horizontal">

    <?php if ($isAiAgentEnabled) : ?>
        <div id="pnm-mode-switcher" class="pnm-mode-switcher" role="radiogroup"
             aria-label="<?php echo Text::_('COM_PLUGNMEET_ROOM_MODE_LABEL'); ?>">
            <label class="pnm-mode-option<?php echo $roomMode === 'conference' ? ' is-active' : ''; ?>">
                <input type="radio" name="jform[room_mode]" value="conference"
                        <?php echo $roomMode === 'conference' ? 'checked="checked"' : ''; ?>>
                <span class="pnm-mode-label"><?php echo Text::_('COM_PLUGNMEET_ROOM_MODE_CONFERENCE'); ?></span>
                <span class="pnm-mode-hint"><?php echo Text::_('COM_PLUGNMEET_ROOM_MODE_CONFERENCE_HINT'); ?></span>
            </label>
            <label class="pnm-mode-option<?php echo $roomMode === 'ai_agent' ? ' is-active' : ''; ?>">
                <input type="radio" name="jform[room_mode]" value="ai_agent"
                        <?php echo $roomMode === 'ai_agent' ? 'checked="checked"' : ''; ?>>
                <span class="pnm-mode-label"><?php echo Text::_('COM_PLUGNMEET_ROOM_MODE_AI_AGENT'); ?></span>
                <span class="pnm-mode-hint"><?php echo Text::_('COM_PLUGNMEET_ROOM_MODE_AI_AGENT_HINT'); ?></span>
            </label>
        </div>
    <?php else : ?>
        <input type="hidden" name="jform[room_mode]" value="<?php echo htmlspecialchars($roomMode, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.startTabSet', 'roomTab', $tabParams); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'basic', Text::_('COM_PLUGNMEET_ROOM_TAB_BASIC', true)); ?>
    <?php echo $this->form->renderFieldset("basic"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'roomFeatures', Text::_('COM_PLUGNMEET_ROOM_TAB_ROOM_FEATURES', true)); ?>
    <?php echo $this->form->renderFieldset("roomFeatures"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'otherFeatures', Text::_('COM_PLUGNMEET_ROOM_TAB_OTHER_FEATURES', true)); ?>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_RECORDING_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_RECORDING_DES'); ?></p>
    <?php echo $this->form->renderFieldset("recordingFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_CHAT_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_CHAT_DES'); ?></p>
    <?php echo $this->form->renderFieldset("chatFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_WAITING_ROOM_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_WAITING_ROOM_DES'); ?></p>
    <?php echo $this->form->renderFieldset("waitingRoomFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_POLLS_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_POLLS_DES'); ?></p>
    <?php echo $this->form->renderFieldset("pollsFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_NOTEPAD_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_NOTEPAD_DES'); ?></p>
    <?php echo $this->form->renderFieldset("sharedNotePadFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_WHITEBOARD_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_WHITEBOARD_DES'); ?></p>
    <?php echo $this->form->renderFieldset("whiteboardFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_BREAKOUT_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_BREAKOUT_DES'); ?></p>
    <?php echo $this->form->renderFieldset("breakoutRoomFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_MEDIA_PLAYER_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_MEDIA_PLAYER_DES'); ?></p>
    <?php echo $this->form->renderFieldset("externalMediaPlayerFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_EXTERNAL_LINK_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_EXTERNAL_LINK_DES'); ?></p>
    <?php echo $this->form->renderFieldset("displayExternalLinkFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_BROADCAST_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_BROADCAST_DES'); ?></p>
    <?php echo $this->form->renderFieldset("externalBroadcastingFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_INGRESS_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_INGRESS_DES'); ?></p>
    <?php echo $this->form->renderFieldset("ingressFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_SIP_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_SIP_DES'); ?></p>
    <?php echo $this->form->renderFieldset("sipDialInFeatures"); ?>
    <hr/>
    <h3><?php echo Text::_('COM_PLUGNMEET_SECTION_E2EE_LABEL'); ?></h3>
    <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_SECTION_E2EE_DES'); ?></p>
    <?php echo $this->form->renderFieldset("endToEndEncryptionFeatures"); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'roomTab', 'insightsFeatures', Text::_('COM_PLUGNMEET_ROOM_TAB_INSIGHTS_AI_FEATURES', true)); ?>
    <div id="pnm-insights-standard-card">
        <h3><?php echo Text::_('COM_PLUGNMEET_INSIGHTS_CARD_TITLE'); ?></h3>
        <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_INSIGHTS_CARD_DES'); ?></p>
        <?php echo $this->form->renderFieldset("insightsFeatures"); ?>
        <hr/>
    </div>

    <?php if ($isAiAgentEnabled) : ?>
        <div id="pnm-ai-agent-card">
            <h3><?php echo Text::_('COM_PLUGNMEET_AI_AGENT_CARD_TITLE'); ?></h3>
            <p class="text-muted"><?php echo Text::_('COM_PLUGNMEET_AI_AGENT_CARD_DES'); ?></p>
            <?php echo $this->form->renderFieldset("aiAgentFeatures"); ?>
        </div>
    <?php endif; ?>
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

