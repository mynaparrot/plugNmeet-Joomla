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

use Joomla\CMS\Language\Text;

$ai = $this->artifact->aiSummary ?? null;
$e  = function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

if (empty($ai)) :
	?>
	<div class="p-4 gy-5">
		<div class="alert alert-warning">
			<?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_UNAVAILABLE'); ?>
		</div>
	</div>
	<?php
	return;
endif;

$stats = array(
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_DURATION'), 'value' => (int) $ai['duration_minutes'] > 0 ? Text::sprintf('COM_PLUGNMEET_AI_SUMMARY_MINUTES', (int) $ai['duration_minutes']) : '—'),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_TURNS'), 'value' => (int) $ai['turn_count']),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_STARTED_AT'), 'value' => '' !== $ai['started_at'] ? $ai['started_at'] : '—'),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_ENDED_AT'), 'value' => '' !== $ai['ended_at'] ? $ai['ended_at'] : '—'),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_END_REASON'), 'value' => '' !== $ai['end_reason'] ? $ai['end_reason'] : '—'),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_OFF_TOPIC_REDIRECTS'), 'value' => (int) $ai['off_topic_redirects']),
);

$chipGroups = array(
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_CONCEPTS_MASTERED'), 'items' => $ai['concepts_mastered'], 'class' => 'bg-success'),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_CONCEPTS_PRACTICED'), 'items' => $ai['concepts_practiced'], 'class' => 'bg-info text-dark'),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_STRUGGLED_WITH'), 'items' => $ai['struggled_with'], 'class' => 'bg-warning text-dark'),
	array('label' => Text::_('COM_PLUGNMEET_AI_SUMMARY_VOCABULARY'), 'items' => $ai['vocabulary'], 'class' => 'bg-secondary'),
);
?>
<div class="p-4 gy-5 plugnmeet-ai-report">
	<h2 class="plugnmeet-ai-report-title"><?php echo Text::_('COM_PLUGNMEET_AI_REPORT'); ?></h2>

	<div class="card plugnmeet-ai-card plugnmeet-ai-header-card">
		<div class="card-body">
			<div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
				<div class="plugnmeet-ai-identity">
					<h3 class="plugnmeet-ai-student mb-1"><?php echo $e($ai['student_name']); ?></h3>
					<?php if ('' !== $ai['ex_user_id']) : ?>
						<div class="plugnmeet-ai-meta">
							<?php echo $e(Text::sprintf('COM_PLUGNMEET_AI_SUMMARY_USER_ID_VALUE', $ai['ex_user_id'])); ?>
						</div>
					<?php endif; ?>
					<div class="plugnmeet-ai-meta">
						<?php echo $e($ai['agent_name']); ?>
						<?php if ('' !== $ai['topic']) : ?>
							<span class="plugnmeet-ai-dot" aria-hidden="true">&middot;</span>
							<?php echo $e($ai['topic']); ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="d-flex flex-column align-items-end gap-2">
					<div class="plugnmeet-ai-score">
						<span class="plugnmeet-ai-score-value"><?php echo (int) $ai['overall_score']; ?>%</span>
						<span class="plugnmeet-ai-score-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_OVERALL_SCORE'); ?></span>
					</div>
					<?php if ('' !== $ai['mastery_level'] || '' !== $ai['participation_level']) : ?>
						<div class="plugnmeet-ai-badges justify-content-end">
							<?php if ('' !== $ai['mastery_level']) : ?>
								<span class="badge <?php echo $e($ai['mastery_badge_class']); ?>">
									<?php echo $e($ai['mastery_level']); ?>
								</span>
							<?php endif; ?>
							<?php if ('' !== $ai['participation_level']) : ?>
								<span class="badge bg-secondary">
									<?php echo $e(Text::sprintf('COM_PLUGNMEET_AI_SUMMARY_PARTICIPATION_VALUE', $ai['participation_level'])); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="row row-cols-2 row-cols-md-3 g-2 plugnmeet-ai-stats mt-3">
				<?php foreach ($stats as $stat) : ?>
					<div class="col">
						<div class="plugnmeet-ai-stat">
							<div class="plugnmeet-ai-stat-value"><?php echo $e($stat['value']); ?></div>
							<div class="plugnmeet-ai-stat-label"><?php echo $e($stat['label']); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php if ('' !== $ai['narrative_summary']) : ?>
		<div class="card plugnmeet-ai-card">
			<div class="card-body">
				<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NARRATIVE'); ?></h3>
				<p class="plugnmeet-ai-text"><?php echo nl2br($e($ai['narrative_summary'])); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<?php if ('' !== $ai['rubric_notes']) : ?>
		<div class="card plugnmeet-ai-card">
			<div class="card-body">
				<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_RUBRIC_NOTES'); ?></h3>
				<p class="plugnmeet-ai-text"><?php echo nl2br($e($ai['rubric_notes'])); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<?php if ('' !== $ai['next_session_focus'] || '' !== $ai['recommended_warm_up'] || !empty($ai['unfinished_subtopics'])) : ?>
		<div class="card plugnmeet-ai-card">
			<div class="card-body">
				<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NEXT_SESSION_PLAN'); ?></h3>

				<?php if ('' !== $ai['next_session_focus']) : ?>
					<p class="plugnmeet-ai-text mb-2">
						<strong><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NEXT_FOCUS'); ?>:</strong>
						<?php echo $e($ai['next_session_focus']); ?>
					</p>
				<?php endif; ?>

				<?php if ('' !== $ai['recommended_warm_up']) : ?>
					<p class="plugnmeet-ai-text mb-2">
						<strong><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_WARM_UP'); ?>:</strong>
						<?php echo $e($ai['recommended_warm_up']); ?>
					</p>
				<?php endif; ?>

				<?php if (!empty($ai['unfinished_subtopics'])) : ?>
					<div class="plugnmeet-ai-chip-group">
						<span class="plugnmeet-ai-chip-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_UNFINISHED_SUBTOPICS'); ?></span>
						<?php foreach ($ai['unfinished_subtopics'] as $subtopic) : ?>
							<span class="badge bg-warning text-dark"><?php echo $e($subtopic); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	$hasChips = false;

	foreach ($chipGroups as $group)
	{
		if (!empty($group['items']))
		{
			$hasChips = true;
			break;
		}
	}
	?>

	<?php if ($hasChips) : ?>
		<div class="card plugnmeet-ai-card">
			<div class="card-body">
				<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_CONCEPTS'); ?></h3>

				<?php foreach ($chipGroups as $group) : ?>
					<?php if (empty($group['items'])) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<div class="plugnmeet-ai-chip-group">
						<span class="plugnmeet-ai-chip-label"><?php echo $e($group['label']); ?></span>
						<?php foreach ($group['items'] as $item) : ?>
							<span class="badge <?php echo $e($group['class']); ?>"><?php echo $e($item); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="card plugnmeet-ai-card">
		<div class="card-body">
			<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_QUIZ_EVALUATION'); ?></h3>

			<?php if (!empty($ai['quiz_questions'])) : ?>
				<div class="table-responsive">
					<table class="table table-striped align-middle plugnmeet-ai-table">
						<thead>
						<tr>
							<th scope="col"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_QUESTION'); ?></th>
							<th scope="col"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_CORRECT_OPTION'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_VOTES'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_CORRECT_VOTES'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_PASSED'); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($ai['quiz_questions'] as $question) : ?>
							<tr>
								<td><?php echo $e($question['question']); ?></td>
								<td><?php echo $e($question['correct_option']); ?></td>
								<td class="text-center"><?php echo (int) $question['total_votes']; ?></td>
								<td class="text-center"><?php echo (int) $question['correct_votes']; ?></td>
								<td class="text-center">
									<?php if ($question['passed']) : ?>
										<span class="badge bg-success"><?php echo Text::_('COM_PLUGNMEET_YES'); ?></span>
									<?php else : ?>
										<span class="badge bg-danger"><?php echo Text::_('COM_PLUGNMEET_NO'); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p class="plugnmeet-ai-empty"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NO_DATA'); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="card plugnmeet-ai-card">
		<div class="card-body">
			<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_PROGRESS_LOG'); ?></h3>

			<?php if (!empty($ai['progress_log'])) : ?>
				<div class="table-responsive">
					<table class="table table-striped align-middle plugnmeet-ai-table">
						<thead>
						<tr>
							<th scope="col"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_CONCEPT'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_STATUS'); ?></th>
							<th scope="col"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NOTES'); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($ai['progress_log'] as $entry) : ?>
							<tr>
								<td><?php echo $e($entry['concept']); ?></td>
								<td class="text-center">
									<?php if ('' !== $entry['status']) : ?>
										<span class="badge <?php echo $e($entry['status_badge_class']); ?>"><?php echo $e($entry['status']); ?></span>
									<?php else : ?>
										—
									<?php endif; ?>
								</td>
								<td><?php echo $e($entry['notes']); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p class="plugnmeet-ai-empty"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NO_DATA'); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="card plugnmeet-ai-card">
		<div class="card-body">
			<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_WORKSPACE_ACTIVITY'); ?></h3>

			<div class="plugnmeet-ai-chip-group">
				<span class="plugnmeet-ai-chip-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_WHITEBOARD'); ?></span>
				<span class="badge <?php echo $ai['whiteboard_used'] ? 'bg-success' : 'bg-secondary'; ?>">
					<?php echo $ai['whiteboard_used'] ? Text::_('COM_PLUGNMEET_AI_SUMMARY_USED') : Text::_('COM_PLUGNMEET_AI_SUMMARY_NOT_USED'); ?>
				</span>
				<span class="plugnmeet-ai-chip-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NOTEPAD'); ?></span>
				<span class="badge <?php echo $ai['notepad_used'] ? 'bg-success' : 'bg-secondary'; ?>">
					<?php echo $ai['notepad_used'] ? Text::_('COM_PLUGNMEET_AI_SUMMARY_USED') : Text::_('COM_PLUGNMEET_AI_SUMMARY_NOT_USED'); ?>
				</span>
			</div>

			<?php if (!empty($ai['tools_used'])) : ?>
				<div class="plugnmeet-ai-chip-group">
					<span class="plugnmeet-ai-chip-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_TOOLS_USED'); ?></span>
					<?php foreach ($ai['tools_used'] as $tool) : ?>
						<span class="badge bg-info text-dark"><?php echo $e($tool['name'] . ' (' . (int) $tool['count'] . ')'); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="card plugnmeet-ai-card">
		<div class="card-body">
			<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_TOKEN_USAGE'); ?></h3>

			<?php if (!empty($ai['usage_rows'])) : ?>
				<div class="table-responsive">
					<table class="table table-striped align-middle plugnmeet-ai-table">
						<thead>
						<tr>
							<th scope="col"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_ROLE'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_PROMPT_TOKENS'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_COMPLETION_TOKENS'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_TOTAL_TOKENS'); ?></th>
							<th scope="col" class="text-center"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_ESTIMATED_COST'); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($ai['usage_rows'] as $row) : ?>
							<tr>
								<td><?php echo $e($row['role']); ?></td>
								<td class="text-center"><?php echo number_format((int) $row['prompt_tokens']); ?></td>
								<td class="text-center"><?php echo number_format((int) $row['completion_tokens']); ?></td>
								<td class="text-center"><?php echo number_format((int) $row['total_tokens']); ?></td>
								<td class="text-center"><?php echo number_format((float) $row['cost'], 4); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<tfoot>
						<tr class="plugnmeet-ai-total-row">
							<th scope="row"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_TOTAL'); ?></th>
							<td class="text-center"><?php echo number_format((int) $ai['total_input_tokens']); ?></td>
							<td class="text-center"><?php echo number_format((int) $ai['total_output_tokens']); ?></td>
							<td class="text-center"><?php echo number_format((int) $ai['total_tokens']); ?></td>
							<td class="text-center"><?php echo $e($ai['total_estimated_cost']); ?></td>
						</tr>
						</tfoot>
					</table>
				</div>
			<?php else : ?>
				<p class="plugnmeet-ai-empty"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_NO_DATA'); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="card plugnmeet-ai-card">
		<div class="card-body">
			<h3 class="plugnmeet-ai-heading"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_USAGE'); ?></h3>

			<?php if (!empty($ai['has_avatar_usage'])) : ?>
				<div class="row row-cols-2 row-cols-md-3 g-2 plugnmeet-ai-stats">
					<div class="col">
						<div class="plugnmeet-ai-stat">
							<div class="plugnmeet-ai-stat-value"><?php echo $e($ai['avatar_duration_formatted']); ?></div>
							<div class="plugnmeet-ai-stat-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_DURATION'); ?></div>
						</div>
					</div>
					<?php if ('' !== $ai['avatar_provider']) : ?>
						<div class="col">
							<div class="plugnmeet-ai-stat">
								<div class="plugnmeet-ai-stat-value"><?php echo $e($ai['avatar_provider']); ?></div>
								<div class="plugnmeet-ai-stat-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_PROVIDER'); ?></div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ('' !== $ai['avatar_id']) : ?>
						<div class="col">
							<div class="plugnmeet-ai-stat">
								<div class="plugnmeet-ai-stat-value"><?php echo $e($ai['avatar_id']); ?></div>
								<div class="plugnmeet-ai-stat-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_ID'); ?></div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ('' !== $ai['avatar_started_at']) : ?>
						<div class="col">
							<div class="plugnmeet-ai-stat">
								<div class="plugnmeet-ai-stat-value"><?php echo $e($ai['avatar_started_at']); ?></div>
								<div class="plugnmeet-ai-stat-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_STARTED_AT'); ?></div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ('' !== $ai['avatar_ended_at']) : ?>
						<div class="col">
							<div class="plugnmeet-ai-stat">
								<div class="plugnmeet-ai-stat-value"><?php echo $e($ai['avatar_ended_at']); ?></div>
								<div class="plugnmeet-ai-stat-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_ENDED_AT'); ?></div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ('' !== $ai['avatar_estimated_cost']) : ?>
						<div class="col">
							<div class="plugnmeet-ai-stat">
								<div class="plugnmeet-ai-stat-value"><?php echo $e($ai['avatar_estimated_cost']); ?></div>
								<div class="plugnmeet-ai-stat-label"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_ESTIMATED_COST'); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<p class="plugnmeet-ai-empty"><?php echo Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_NOT_USED'); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
