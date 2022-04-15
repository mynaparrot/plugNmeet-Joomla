<?php
/**
 * @package 	plugNmeet
 * @subpackage	default.php
 * @version		1.0.4
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


?>
<form action="<?php echo JRoute::_('index.php?option=com_plugnmeet'); ?>" method="post" name="adminForm" id="adminForm">

<!--[JCBGUI.site_view.default.30.$$$$]-->
    <?php
    $config = JFactory::getConfig();
    $isActiveSEF = $config->get('sef');
    $itemId = $this->app->input->get("Itemid", 0);

    $chunks = array_chunk($this->items, 2);
    ?>
    <?php foreach ($chunks as $chunk): ?>
        <div class="pnm-container">
            <?php foreach ($chunk as $item): ?>
                <?php
                $url = "index.php?option=com_plugnmeet&view=room&id=" . $item->id . "&Itemid=" . $itemId;
            if ($isActiveSEF) {
                $url = JRoute::_("index.php?option=com_plugnmeet&view=room&id=" . $item->alias . "&Itemid=" . $itemId);
            }
                ?>
            <div class="column column-half">
                <h1 class="headline"><?php echo $item->room_title ?></h1>
                <div class="br">
                    <div class="br-inner"></div>
                </div>
                <div class="description">
                    <?php echo $item->description ?>
                </div>
                <a href="<?php echo $url ?>" class="detail-btn">Details</a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?><!--[/JCBGUI$$$$]-->


<?php if (isset($this->items) && isset($this->pagination) && isset($this->pagination->pagesTotal) && $this->pagination->pagesTotal > 1): ?>
	<div class="pagination">
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> <?php echo $this->pagination->getLimitBox(); ?></p>
		<?php endif; ?>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
