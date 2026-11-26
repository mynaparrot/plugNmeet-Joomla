<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;

defined('_JEXEC') or die;

$user      = Factory::getApplication()->getIdentity();
$assetName = sprintf("com_plugnmeet.room.%d", $this->item->id);

$roomMetadata  = json_decode((string) ($this->item->room_metadata ?? ''), true);
$roomMetadata  = is_array($roomMetadata) ? $roomMetadata : [];
$roomMode      = PlugnmeetHelper::getRoomMode($roomMetadata);
$enableAiAgent = PlugnmeetHelper::isAiAgentEnabled();
$aiAgent       = isset($roomMetadata['insights_features']['ai_agent_features']) && is_array($roomMetadata['insights_features']['ai_agent_features'])
	? $roomMetadata['insights_features']['ai_agent_features']
	: [];
$isAiAgentMode = $enableAiAgent && $roomMode === 'ai_agent';
$isAiEnabled   = $isAiAgentMode || ($enableAiAgent && !empty($aiAgent['is_allow']));

// Used by the login template.
$this->isAiAgentMode = $isAiAgentMode;
$this->aiAgent       = $aiAgent;

$aiAgentName  = trim((string) ($aiAgent['agent_display_name'] ?? ''));
$aiAgentTopic = trim((string) ($aiAgent['topic'] ?? ''));

$canViewRecording = $user->authorise("recording.view", $assetName);
$canPlayRecording = $user->authorise("recording.play", $assetName);

Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('com_plugnmeet.room');
?>

<div class="pnm-room mb-3">
	<?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
	<?php endif; ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
				<?php echo $this->item->room_title; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="card-text">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-8">
							<?php if ($isAiEnabled) : ?>
                                <div class="pnm-ai-banner<?php echo $isAiAgentMode ? ' pnm-ai-banner-autonomous' : ' pnm-ai-banner-conference'; ?>">
                                    <div class="pnm-ai-banner-text">
                                        <span class="pnm-ai-banner-name">
											<?php echo $this->escape($aiAgentName !== '' ? $aiAgentName : Text::_('COM_PLUGNMEET_AI_AGENT_DISPLAY_NAME_DEFAULT')); ?>
                                        </span>
										<?php if ($aiAgentTopic !== '') : ?>
                                            <span class="pnm-ai-banner-topic"><?php echo $this->escape($aiAgentTopic); ?></span>
										<?php endif; ?>
                                    </div>
									<?php if ($isAiAgentMode) : ?>
                                        <span class="pnm-ai-banner-badge"><?php echo Text::_('COM_PLUGNMEET_AI_AUTONOMOUS_SESSION_BADGE'); ?></span>
									<?php endif; ?>
                                </div>
							<?php endif; ?>
							<?php echo $this->item->description; ?>
                        </div>
                        <div class="col-sm-4">
							<?php echo $this->loadTemplate("login"); ?>
                        </div>
                    </div>
					<?php
					// Personal 1-on-1 AI sessions have no recordings to display.
					if (!$isAiAgentMode && ($canViewRecording || $canPlayRecording))
					{
						if ($canViewRecording)
						{
							echo $this->loadTemplate("recordings");
						}
						if ($canPlayRecording)
						{
							echo $this->loadTemplate("play_modal");
						}
					}
					?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // check if returned from conference
    const params = new URLSearchParams(document.location.search);
    if (params.has("returned", "true")) {
        // this will only work if link opened with window.open()
        window.close();
    }
</script>
