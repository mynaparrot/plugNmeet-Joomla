<?php
/**
 * @package 	plugNmeet
 * @subpackage	metadata.php
 * @version		1.0.2
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$form = $displayData->getForm();

// JLayout for standard handling of metadata fields in the administrator content edit screens.
$fieldSets = $form->getFieldsets('metadata');
?>

<?php foreach ($fieldSets as $name => $fieldSet) : ?>
	<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
		<p class="alert alert-info"><?php echo $this->escape(JText::_($fieldSet->description)); ?></p>
	<?php endif; ?>

	<?php
	// Include the real fields in this panel.
	if ($name == 'vdmmetadata')
	{
		echo $form->renderField('metadesc');
		echo $form->renderField('metakey');
	}

	foreach ($form->getFieldset($name) as $field)
	{
		if ($field->name != 'jform[metadata][tags][]')
		{
			echo $field->renderField();
		}
	} ?>
<?php endforeach; ?>
