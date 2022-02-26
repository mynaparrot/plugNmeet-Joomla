<?php
/**
 * @package 	plugNmeet
 * @subpackage	default_vdm.php
 * @version		1.0.0
 * @build		26th February, 2022
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<img alt="<?php echo JText::_('COM_PLUGNMEET'); ?>" src="components/com_plugnmeet/assets/images/vdm-component.png">
<ul class="list-striped">
	<li><b><?php echo JText::_('COM_PLUGNMEET_VERSION'); ?>:</b> <?php echo $this->manifest->version; ?>&nbsp;&nbsp;<span class="update-notice"></span></li>
	<li><b><?php echo JText::_('COM_PLUGNMEET_DATE'); ?>:</b> <?php echo $this->manifest->creationDate; ?></li>
	<li><b><?php echo JText::_('COM_PLUGNMEET_AUTHOR'); ?>:</b> <a href="mailto:<?php echo $this->manifest->authorEmail; ?>"><?php echo $this->manifest->author; ?></a></li>
	<li><b><?php echo JText::_('COM_PLUGNMEET_WEBSITE'); ?>:</b> <a href="<?php echo $this->manifest->authorUrl; ?>" target="_blank"><?php echo $this->manifest->authorUrl; ?></a></li>
	<li><b><?php echo JText::_('COM_PLUGNMEET_LICENSE'); ?>:</b> <?php echo $this->manifest->license; ?></li>
	<li><b><?php echo $this->manifest->copyright; ?></b></li>
</ul>
<div class="clearfix"></div>
<?php if(PlugnmeetHelper::checkArray($this->contributors)): ?>
	<?php if(count($this->contributors) > 1): ?>
		<h3><?php echo JText::_('COM_PLUGNMEET_CONTRIBUTORS'); ?></h3>
	<?php else: ?>
		<h3><?php echo JText::_('COM_PLUGNMEET_CONTRIBUTOR'); ?></h3>
	<?php endif; ?>
	<ul class="list-striped">
		<?php foreach($this->contributors as $contributor): ?>
		<li><b><?php echo $contributor['title']; ?>:</b> <?php echo $contributor['name']; ?></li>
		<?php endforeach; ?>
	</ul>
	<div class="clearfix"></div>
<?php endif; ?>