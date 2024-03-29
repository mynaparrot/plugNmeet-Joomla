<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');


?>
<form action="<?php echo JRoute::_('index.php?option=com_plugnmeet'); ?>" method="post" name="adminForm" id="adminForm">
<?php echo $this->toolbar->render(); ?>
<!--[JCBGUI.site_view.default.1.$$$$]-->
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
            $params = json_decode($item->params);

            $url = "index.php?option=com_plugnmeet&view=category&id=" . $item->id . "&Itemid=" . $itemId;
            if ($isActiveSEF) {
                $url = JRoute::_("index.php?option=com_plugnmeet&view=category&id=" . $item->alias . "&Itemid=" . $itemId);
            }

            ?>
            <div class="column column-half">
                <h1 class="headline"><?php echo $item->title ?></h1>
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
