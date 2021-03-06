/**
 * @package 	plugNmeet
 * @subpackage	submitbutton.js
 * @version		1.0.7
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.org>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

Joomla.submitbutton = function(task)
{
	if (task == ''){
		return false;
	} else { 
		var action = task.split('.');
		if (action[1] == 'cancel' || action[1] == 'close' || document.formvalidator.isValid(document.getElementById("adminForm"))){
			Joomla.submitform(task, document.getElementById("adminForm"));
			return true;
		} else {
			alert(Joomla.JText._('room, some values are not acceptable.','Some values are unacceptable'));
			return false;
		}
	}
}