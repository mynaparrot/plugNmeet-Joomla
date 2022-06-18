<?php
/**
 * @package 	plugNmeet
 * @subpackage	edit.php
 * @version		1.0.6
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
$componentParams = $this->params; // will be removed just use $this->params instead
?>
<script type="text/javascript">
	// waiting spinner
	var outerDiv = jQuery('body');
	jQuery('<div id="loading"></div>')
		.css("background", "rgba(255, 255, 255, .8) url('components/com_plugnmeet/assets/images/import.gif') 50% 15% no-repeat")
		.css("top", outerDiv.position().top - jQuery(window).scrollTop())
		.css("left", outerDiv.position().left - jQuery(window).scrollLeft())
		.css("width", outerDiv.width())
		.css("height", outerDiv.height())
		.css("position", "fixed")
		.css("opacity", "0.80")
		.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
		.css("filter", "alpha(opacity = 80)")
		.css("display", "none")
		.appendTo(outerDiv);
	jQuery('#loading').show();
	// when page is ready remove and show
	jQuery(window).load(function() {
		jQuery('#plugnmeet_loader').fadeIn('fast');
		jQuery('#loading').hide();
	});
</script>
<div id="plugnmeet_loader" style="display: none;">
<form action="<?php echo JRoute::_('index.php?option=com_plugnmeet&layout=edit&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<div class="form-horizontal">

	<?php echo JHtml::_('bootstrap.startTabSet', 'roomTab', array('active' => 'details')); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'roomTab', 'details', JText::_('COM_PLUGNMEET_ROOM_DETAILS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo JLayoutHelper::render('room.details_left', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'roomTab', 'room_features', JText::_('COM_PLUGNMEET_ROOM_ROOM_FEATURES', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo $this->get("RoomFeatures"); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'roomTab', 'other_features', JText::_('COM_PLUGNMEET_ROOM_OTHER_FEATURES', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo $this->get("ChatFeatures"); ?>
				<hr />
								<?php echo $this->get("SharedNotePadFeatures"); ?>
				<hr />
								<?php echo $this->get("WhiteboardFeatures"); ?>
				<hr />
								<?php echo $this->get("ExternalMediaPlayerFeatures"); ?>
				<hr />
								<?php echo $this->get("WaitingRoomFeatures"); ?>
				<hr />
								<?php echo $this->get("BreakoutRoomFeatures"); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'roomTab', 'lock_settings', JText::_('COM_PLUGNMEET_ROOM_LOCK_SETTINGS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo $this->get("DefaultLockSettings"); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'roomTab', 'design_customization', JText::_('COM_PLUGNMEET_ROOM_DESIGN_CUSTOMIZATION', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo $this->get("DesignCustomization"); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'roomTab'; ?>
	<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('core.edit.created_by') || $this->canDo->get('core.edit.created') || $this->canDo->get('core.edit.state') || ($this->canDo->get('core.delete') && $this->canDo->get('core.edit.state'))) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'roomTab', 'publishing', JText::_('COM_PLUGNMEET_ROOM_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('room.publishing', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('room.metadata', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<div>
		<input type="hidden" name="task" value="room.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>
</div>
