<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (!$this->artifact->status) :
    ?>
    <div class="alert alert-danger">
        <?php echo $this->escape($this->artifact->msg); ?>
    </div>
    <?php
    return;
endif;
?>

<div class="plugnmeet-details-header">
    <h1><?php echo Text::_('COM_PLUGNMEET_ARTIFACT_DETAILS'); ?></h1>
    <div class="plugnmeet-header-actions">
        <a href="<?php echo $this->backUrl; ?>" class="btn btn-secondary">
            <?php echo Text::_('COM_PLUGNMEET_ARTIFACT_BACK'); ?>
        </a>
        <?php if ($this->artifact->isFileBased) : ?>
            <button type="button" class="btn btn-primary plugnmeet-download-artifact"
                    data-artifact-id="<?php echo $this->escape($this->artifact->artifactId); ?>">
                <?php echo Text::_('COM_PLUGNMEET_ARTIFACT_DOWNLOAD'); ?>
            </button>
        <?php endif; ?>
        <?php if ($this->artifact->isAnalytics) : ?>
            <form action="<?php echo Route::_('index.php?option=com_plugnmeet&view=artifacts'); ?>" method="post"
                  class="d-inline">
                <input type="hidden" name="task" value="artifacts.downloadAnalytics"/>
                <input type="hidden" name="artifact_id"
                       value="<?php echo $this->escape($this->artifact->artifactId); ?>"/>
                <?php echo HTMLHelper::_('form.token'); ?>
                <button type="submit" class="btn btn-success">
                    <?php echo Text::_('COM_PLUGNMEET_ARTIFACT_DOWNLOAD_EXCEL'); ?>
                </button>
            </form>
        <?php endif; ?>
        <?php if ($this->artifact->isFileBased) : ?>
            <button type="button" class="btn btn-danger plugnmeet-delete-artifact"
                    data-artifact-id="<?php echo $this->escape($this->artifact->artifactId); ?>">
                <?php echo Text::_('COM_PLUGNMEET_ARTIFACT_DELETE'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="p-4 gy-5">
    <table class="table table-bordered details">
        <tbody>
        <?php foreach ($this->artifact->details as $detail) : ?>
            <tr>
                <th scope="row" class="w-25"><?php echo $this->escape($detail['label']); ?></th>
                <td><?php echo $this->escape($detail['value']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($this->artifact->isAnalytics && $this->artifact->analytics) : ?>
    <div class="p-4 gy-5">
        <h2><?php echo Text::_('COM_PLUGNMEET_ARTIFACT_ROOM_ANALYTICS'); ?></h2>
        <table class="table table-bordered">
            <tbody>
            <?php foreach ($this->artifact->analytics['roomDetails'] as $detail) : ?>
                <tr>
                    <th scope="row" class="w-25"><?php echo $this->escape($detail['label']); ?></th>
                    <td><?php echo $this->escape($detail['value']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($this->artifact->analytics['hasUsers']) : ?>
        <div class="p-4 gy-5">

            <h2><?php echo Text::_('COM_PLUGNMEET_ARTIFACT_USER_ANALYTICS'); ?></h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <?php foreach ($this->artifact->analytics['userHeaders'] as $header) : ?>
                            <th><?php echo $this->escape($header); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->artifact->analytics['userRows'] as $row) : ?>
                        <tr>
                            <?php foreach ($row['data'] as $cell) : ?>
                                <td><?php echo $cell['value']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($this->artifact->isMeetingSummary) : ?>
    <div class="p-4 gy-5">
        <h2><?php echo Text::_('COM_PLUGNMEET_ARTIFACT_MEETING_SUMMARY'); ?></h2>
        <div class="plugnmeet-meeting-summary">
            <?php echo $this->artifact->meetingSummaryContent; ?>
        </div>
    </div>
<?php endif; ?>
