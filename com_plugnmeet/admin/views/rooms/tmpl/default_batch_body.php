<?php
/**
 * @package 	plugNmeet
 * @subpackage	default_batch_body.php
 * @version		1.0.5
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>

<p><?php echo JText::_('COM_PLUGNMEET_ROOMS_BATCH_TIP'); ?></p>
<?php echo $this->batchDisplay; ?>