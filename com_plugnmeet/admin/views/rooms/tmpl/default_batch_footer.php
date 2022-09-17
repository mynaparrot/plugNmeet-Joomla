<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<!-- clear the batch values if cancel -->
<button class="btn" type="button" onclick="" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</button>
<!-- post the batch values if process -->
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('room.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>