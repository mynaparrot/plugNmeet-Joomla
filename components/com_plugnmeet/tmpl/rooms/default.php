<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Router\Route;

// No direct access
defined('_JEXEC') or die;

$rows = array_chunk($this->items, 3);
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
<?php endif; ?>
<div class="container">
	<?php foreach ($rows as $row) : ?>
        <div class="row">
			<?php foreach ($row as $i => $item) : ?>
                <div class="col mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <a href="<?php echo Route::_('index.php?option=com_plugnmeet&view=room&id=' . (int) $item->id . '&catid=' . (int) $item->cat); ?>"><?php echo $item->room_title; ?></a>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="card-text">
								<?php echo $item->description; ?>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
	<?php endforeach; ?>
	<?php if ($this->items) : ?>
        <div class="pagination justify-content-end">
			<?php echo $this->pagination->getListFooter(); ?>
        </div>
	<?php endif; ?>
</div>
