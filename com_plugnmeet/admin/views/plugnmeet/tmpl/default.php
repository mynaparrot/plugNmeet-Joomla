<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');



?>
<div id="j-main-container">
	<div class="span9">
		<?php echo JHtml::_('bootstrap.startAccordion', 'dashboard_left', array('active' => 'main')); ?>
			<?php echo JHtml::_('bootstrap.addSlide', 'dashboard_left', 'cPanel', 'main'); ?>
				<?php echo $this->loadTemplate('main');?>
			<?php echo JHtml::_('bootstrap.endSlide'); ?>
		<?php echo JHtml::_('bootstrap.endAccordion'); ?>
	</div>
	<div class="span3">
		<?php echo JHtml::_('bootstrap.startAccordion', 'dashboard_right', array('active' => 'vdm')); ?>
			<?php echo JHtml::_('bootstrap.addSlide', 'dashboard_right', 'MynaParrot', 'vdm'); ?>
				<?php echo $this->loadTemplate('vdm');?>
			<?php echo JHtml::_('bootstrap.endSlide'); ?>
		<?php echo JHtml::_('bootstrap.endAccordion'); ?>
	</div>
</div>