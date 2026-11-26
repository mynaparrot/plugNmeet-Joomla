<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\Helper;
// No direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AiSummaryHelper
{
	private $artifactId;

	private $rawData = array();

	private $timezone;

	public function __construct(string $artifactId)
	{
		$this->artifactId = $artifactId;
		$this->timezone   = new \DateTimeZone('UTC');

		try
		{
			$this->timezone = Factory::getApplication()->getIdentity()->getTimezone();
		}
		catch (Exception $e)
		{
			// Keep the UTC fallback.
		}

		$connect = new plugNmeetConnect();
		$res     = $connect->getArtifactDownloadToken($this->artifactId);

		if (!$res->getStatus())
		{
			throw new Exception($res->getMsg());
		}

		$params    = ComponentHelper::getParams('com_plugnmeet');
		$serverUrl = rtrim(trim((string) $params->get('plugnmeet_server_url', '')), '/');
		$url       = $serverUrl . '/download/artifact/' . $res->getToken();

		$response = HttpFactory::getHttp()->get($url, array(), 60);

		if ($response->code !== 200)
		{
			throw new Exception(sprintf('HTTP Error: %d', $response->code));
		}

		$data = json_decode($response->body, true);

		$this->rawData = is_array($data) ? $data : array();
	}

	public function isValid(): bool
	{
		return !empty($this->rawData);
	}

	public function getSummaryData(): ?array
	{
		if (!$this->isValid())
		{
			return null;
		}

		$summary      = $this->rawData;
		$userInfo     = (array) ($summary['user_info'] ?? array());
		$session      = (array) ($summary['session'] ?? array());
		$evaluation   = (array) ($summary['evaluation'] ?? array());
		$quiz         = (array) ($summary['quiz_evaluation'] ?? array());
		$workspace    = (array) ($summary['workspace_activity'] ?? array());
		$continuation = (array) ($summary['continuation'] ?? array());
		$avatarUsage  = (array) ($summary['avatar_usage'] ?? array());

		$exUserId       = (string) ($userInfo['ex_user_id'] ?? '');
		$internalUserId = (string) ($userInfo['user_id'] ?? '');
		$masteryLevel   = (string) ($evaluation['mastery_level'] ?? '');
		$durationSec    = (int) ($session['duration_seconds'] ?? 0);

		$studentName = $this->resolveStudentName((string) ($userInfo['name'] ?? ''), $exUserId);

		if ('' === $studentName)
		{
			$studentName = '' !== $exUserId ? $exUserId : $internalUserId;
		}

		$quizQuestions = array();

		foreach ((array) ($quiz['questions'] ?? array()) as $question)
		{
			$quizQuestions[] = array(
				'question'       => (string) ($question['question'] ?? ''),
				'correct_option' => (string) ($question['correct_option'] ?? ''),
				'total_votes'    => (int) ($question['total_votes'] ?? 0),
				'correct_votes'  => (int) ($question['correct_votes'] ?? 0),
				'passed'         => !empty($question['passed']),
			);
		}

		$progressLog = array();

		foreach ((array) ($summary['progress_log'] ?? array()) as $entry)
		{
			$status = (string) ($entry['status'] ?? '');

			$progressLog[] = array(
				'concept'            => (string) ($entry['concept'] ?? ''),
				'status'             => $status,
				'status_badge_class' => $this->statusBadgeClass($status),
				'notes'              => (string) ($entry['notes'] ?? ''),
			);
		}

		$toolsUsed = array();

		foreach ((array) ($workspace['tools_used'] ?? array()) as $tool => $count)
		{
			$toolsUsed[] = array(
				'name'  => (string) $tool,
				'count' => (int) $count,
			);
		}

		$usageRows        = array();
		$totalInputTokens = 0;
		$totalOutputTokens = 0;
		$totalTokens      = 0;
		$estimatedCost    = 0.0;

		foreach ((array) ($summary['usage'] ?? array()) as $role => $usage)
		{
			$row = array(
				'role'              => ucfirst((string) $role),
				'prompt_tokens'     => (int) ($usage['prompt_tokens'] ?? 0),
				'completion_tokens' => (int) ($usage['completion_tokens'] ?? 0),
				'total_tokens'      => (int) ($usage['total_tokens'] ?? 0),
				'cost'              => (float) ($usage['total_tokens_estimated_cost'] ?? 0),
			);

			if ($row['total_tokens'] < 1 && $row['cost'] <= 0)
			{
				continue;
			}

			$usageRows[]       = $row;
			$totalInputTokens  += $row['prompt_tokens'];
			$totalOutputTokens += $row['completion_tokens'];
			$totalTokens       += $row['total_tokens'];
			$estimatedCost     += $row['cost'];
		}

		$avatarDurationSec = (int) ($avatarUsage['duration_sec'] ?? 0);
		$avatarCost        = isset($avatarUsage['duration_sec_estimated_cost'])
			? (float) $avatarUsage['duration_sec_estimated_cost'] : null;

		return array(
			'student_name'              => $studentName,
			'ex_user_id'                => '' !== $exUserId ? $exUserId : $internalUserId,
			'agent_name'                => (string) ($session['agent_display_name'] ?? ''),
			'topic'                     => (string) ($session['topic'] ?? ''),
			'started_at'                => $this->formatTime($session['started_at'] ?? 0),
			'ended_at'                  => $this->formatTime($session['ended_at'] ?? 0),
			'duration_minutes'          => (int) round($durationSec / 60),
			'turn_count'                => (int) ($session['turns_count'] ?? 0),
			'end_reason'                => (string) ($session['end_reason'] ?? ''),
			'off_topic_redirects'       => (int) ($session['off_topic_redirects_count'] ?? 0),

			'overall_score'             => max(0, min(100, (int) ($evaluation['overall_score'] ?? 0))),
			'mastery_level'             => ucfirst(trim($masteryLevel)),
			'mastery_badge_class'       => $this->masteryBadgeClass($masteryLevel),
			'participation_level'       => ucfirst(trim((string) ($evaluation['participation_level'] ?? ''))),
			'narrative_summary'         => (string) ($summary['narrative_summary'] ?? ''),
			'rubric_notes'              => (string) ($evaluation['rubric_notes'] ?? ''),
			'next_session_focus'        => (string) ($continuation['next_session_focus'] ?? ''),
			'recommended_warm_up'       => (string) ($continuation['recommended_warm_up'] ?? ''),
			'concepts_mastered'         => array_values((array) ($evaluation['concepts_mastered'] ?? array())),
			'concepts_practiced'        => array_values((array) ($evaluation['concepts_practiced'] ?? array())),
			'struggled_with'            => array_values((array) ($evaluation['struggled_with'] ?? array())),
			'vocabulary'                => array_values((array) ($evaluation['vocabulary_or_key_terms'] ?? array())),
			'unfinished_subtopics'      => array_values((array) ($continuation['unfinished_subtopics'] ?? array())),

			'has_quiz'                  => (int) ($quiz['total_quizzes'] ?? 0) > 0,
			'quiz_total'                => (int) ($quiz['total_quizzes'] ?? 0),
			'quiz_correct'              => (int) ($quiz['correct_responses'] ?? 0),
			'quiz_accuracy'             => (int) ($quiz['accuracy_percent'] ?? 0),
			'quiz_questions'            => $quizQuestions,

			'has_progress_log'          => !empty($progressLog),
			'progress_log'              => $progressLog,
			'has_workspace'             => !empty($toolsUsed)
				|| !empty($workspace['whiteboard_used'])
				|| !empty($workspace['notepad_used']),
			'whiteboard_used'           => !empty($workspace['whiteboard_used']),
			'notepad_used'              => !empty($workspace['notepad_used']),
			'tools_used'                => $toolsUsed,

			'has_continuation'          => '' !== (string) ($continuation['next_session_focus'] ?? '')
				|| '' !== (string) ($continuation['recommended_warm_up'] ?? '')
				|| !empty($continuation['unfinished_subtopics']),
			'previous_session_context_prompt' => (string) ($continuation['previous_session_context_prompt'] ?? ''),

			'has_usage'                 => $totalTokens > 0,
			'usage_rows'                => $usageRows,
			'total_input_tokens'        => $totalInputTokens,
			'total_output_tokens'       => $totalOutputTokens,
			'total_prompt_tokens'       => $totalInputTokens,
			'total_completion_tokens'   => $totalOutputTokens,
			'total_tokens'              => $totalTokens,
			'total_estimated_cost'      => number_format($estimatedCost, 4),
			'estimated_cost'            => number_format($estimatedCost, 4),

			'has_avatar_usage'          => $avatarDurationSec > 0,
			'has_avatar_cost'           => null !== $avatarCost,
			'avatar_provider'           => (string) ($avatarUsage['provider'] ?? ''),
			'avatar_id'                 => (string) ($avatarUsage['avatar_id'] ?? ''),
			'avatar_duration_sec'       => $avatarDurationSec,
			'avatar_duration_seconds'   => $avatarDurationSec,
			'avatar_duration_formatted' => $this->formatDuration($avatarDurationSec),
			'avatar_started_at'         => $this->formatTime($avatarUsage['started_at'] ?? 0),
			'avatar_ended_at'           => $this->formatTime($avatarUsage['ended_at'] ?? 0),
			'avatar_estimated_cost'     => null === $avatarCost ? '' : number_format($avatarCost, 4),
		);
	}

	public function generateXlsxFile(): string
	{
		$data        = $this->getSummaryData() ?? array();
		$spreadsheet = new Spreadsheet();
		$headerStyle = array(
			'font' => array(
				'bold'  => true,
				'color' => array('rgb' => '1171A3'),
				'size'  => 12,
			),
		);

		$this->formatOverviewXlsx($spreadsheet->getActiveSheet(), $headerStyle, $data);
		$this->formatQuizXlsx($spreadsheet->createSheet(), $headerStyle, $data);
		$this->formatProgressLogXlsx($spreadsheet->createSheet(), $headerStyle, $data);
		$this->formatUsageXlsx($spreadsheet->createSheet(), $headerStyle, $data);

		$writer  = new Xlsx($spreadsheet);
		$tmpPath = Factory::getApplication()->getConfig()->get('tmp_path', sys_get_temp_dir());
		$path    = rtrim($tmpPath, '/') . '/' . $this->artifactId . '_ai_report.xlsx';

		$writer->save($path);

		return $path;
	}

	private function formatOverviewXlsx($sheet, $headerStyle, array $data)
	{
		$this->setSheetTitle($sheet, Text::_('COM_PLUGNMEET_AI_SHEET_OVERVIEW'));
		$sheet->getColumnDimension('A')->setWidth(32);
		$sheet->getColumnDimension('B')->setWidth(70);

		$duration = (int) ($data['duration_minutes'] ?? 0) > 0
			? Text::sprintf('COM_PLUGNMEET_AI_SUMMARY_MINUTES', (int) $data['duration_minutes']) : '';

		$rows = array(
			Text::_('COM_PLUGNMEET_AI_SUMMARY_STUDENT')           => $data['student_name'] ?? '',
			Text::_('COM_PLUGNMEET_ANALYTICS_USER_ID')            => $data['ex_user_id'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_AGENT')             => $data['agent_name'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_TOPIC')             => $data['topic'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_STARTED_AT')        => $data['started_at'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_ENDED_AT')          => $data['ended_at'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_DURATION')          => $duration,
			Text::_('COM_PLUGNMEET_AI_SUMMARY_TURNS')             => $data['turn_count'] ?? 0,
			Text::_('COM_PLUGNMEET_AI_SUMMARY_END_REASON')        => $data['end_reason'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_OFF_TOPIC_REDIRECTS') => $data['off_topic_redirects'] ?? 0,
			Text::_('COM_PLUGNMEET_AI_SUMMARY_OVERALL_SCORE')     => ((int) ($data['overall_score'] ?? 0)) . '%',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_MASTERY_LEVEL')     => $data['mastery_level'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_PARTICIPATION')     => $data['participation_level'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_CONCEPTS_MASTERED') => implode(', ', (array) ($data['concepts_mastered'] ?? array())),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_CONCEPTS_PRACTICED') => implode(', ', (array) ($data['concepts_practiced'] ?? array())),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_STRUGGLED_WITH')    => implode(', ', (array) ($data['struggled_with'] ?? array())),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_VOCABULARY')        => implode(', ', (array) ($data['vocabulary'] ?? array())),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_NARRATIVE')         => $data['narrative_summary'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_RUBRIC_NOTES')      => $data['rubric_notes'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_NEXT_FOCUS')        => $data['next_session_focus'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_WARM_UP')           => $data['recommended_warm_up'] ?? '',
			Text::_('COM_PLUGNMEET_AI_SUMMARY_UNFINISHED_SUBTOPICS') => implode(', ', (array) ($data['unfinished_subtopics'] ?? array())),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_TOOLS_USED')        => $this->formatToolsUsed((array) ($data['tools_used'] ?? array())),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_WHITEBOARD')        => !empty($data['whiteboard_used']) ? Text::_('COM_PLUGNMEET_AI_SUMMARY_USED') : Text::_('COM_PLUGNMEET_AI_SUMMARY_NOT_USED'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_NOTEPAD')           => !empty($data['notepad_used']) ? Text::_('COM_PLUGNMEET_AI_SUMMARY_USED') : Text::_('COM_PLUGNMEET_AI_SUMMARY_NOT_USED'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_TOTAL_TOKENS')      => $data['total_tokens'] ?? 0,
			Text::_('COM_PLUGNMEET_AI_SUMMARY_ESTIMATED_COST')    => $data['total_estimated_cost'] ?? '',
		);

		if (!empty($data['has_quiz']))
		{
			$rows[Text::_('COM_PLUGNMEET_AI_SUMMARY_QUIZZES')]         = $data['quiz_total'] ?? 0;
			$rows[Text::_('COM_PLUGNMEET_AI_SUMMARY_CORRECT_RESPONSES')] = $data['quiz_correct'] ?? 0;
			$rows[Text::_('COM_PLUGNMEET_AI_SUMMARY_ACCURACY')]        = ((int) ($data['quiz_accuracy'] ?? 0)) . '%';
		}

		if (!empty($data['has_avatar_usage']))
		{
			$rows[Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_DURATION')] = $data['avatar_duration_formatted'] ?? '';

			if ('' !== (string) ($data['avatar_estimated_cost'] ?? ''))
			{
				$rows[Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_ESTIMATED_COST')] = $data['avatar_estimated_cost'];
			}
		}

		$rowIndex = 1;

		foreach ($rows as $label => $value)
		{
			$sheet->getCell('A' . $rowIndex)->setValue((string) $label);
			$sheet->getStyle('A' . $rowIndex)->applyFromArray($headerStyle);
			$sheet->getCell('B' . $rowIndex)->setValue((string) $value);
			$rowIndex++;
		}
	}

	private function formatQuizXlsx($sheet, $headerStyle, array $data)
	{
		$this->setSheetTitle($sheet, Text::_('COM_PLUGNMEET_AI_SHEET_QUIZ'));

		$sheet->getColumnDimension('A')->setWidth(60);

		foreach (array('B', 'C', 'D', 'E') as $column)
		{
			$sheet->getColumnDimension($column)->setWidth(18);
		}

		$headers = array(
			Text::_('COM_PLUGNMEET_AI_SUMMARY_QUESTION'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_CORRECT_OPTION'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_VOTES'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_CORRECT_VOTES'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_PASSED'),
		);

		foreach ($headers as $index => $title)
		{
			$sheet->getCell(chr(65 + $index) . '1')->setValue($title);
		}

		$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

		$rowIndex = 2;

		foreach ((array) ($data['quiz_questions'] ?? array()) as $question)
		{
			$sheet->getCell('A' . $rowIndex)->setValue($question['question']);
			$sheet->getCell('B' . $rowIndex)->setValue($question['correct_option']);
			$sheet->getCell('C' . $rowIndex)->setValue($question['total_votes']);
			$sheet->getCell('D' . $rowIndex)->setValue($question['correct_votes']);
			$sheet->getCell('E' . $rowIndex)->setValue(
				$question['passed'] ? Text::_('COM_PLUGNMEET_YES') : Text::_('COM_PLUGNMEET_NO')
			);
			$rowIndex++;
		}
	}

	private function formatProgressLogXlsx($sheet, $headerStyle, array $data)
	{
		$this->setSheetTitle($sheet, Text::_('COM_PLUGNMEET_AI_SHEET_PROGRESS_LOG'));

		$sheet->getColumnDimension('A')->setWidth(40);
		$sheet->getColumnDimension('B')->setWidth(20);
		$sheet->getColumnDimension('C')->setWidth(70);

		$headers = array(
			Text::_('COM_PLUGNMEET_AI_SUMMARY_CONCEPT'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_STATUS'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_NOTES'),
		);

		foreach ($headers as $index => $title)
		{
			$sheet->getCell(chr(65 + $index) . '1')->setValue($title);
		}

		$sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

		$rowIndex = 2;

		foreach ((array) ($data['progress_log'] ?? array()) as $entry)
		{
			$sheet->getCell('A' . $rowIndex)->setValue($entry['concept']);
			$sheet->getCell('B' . $rowIndex)->setValue($entry['status']);
			$sheet->getCell('C' . $rowIndex)->setValue($entry['notes']);
			$rowIndex++;
		}
	}

	private function formatUsageXlsx($sheet, $headerStyle, array $data)
	{
		$this->setSheetTitle($sheet, Text::_('COM_PLUGNMEET_AI_SHEET_USAGE'));

		$sheet->getColumnDimension('A')->setWidth(24);

		foreach (array('B', 'C', 'D', 'E') as $column)
		{
			$sheet->getColumnDimension($column)->setWidth(18);
		}

		$headers = array(
			Text::_('COM_PLUGNMEET_AI_SUMMARY_ROLE'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_PROMPT_TOKENS'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_COMPLETION_TOKENS'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_TOTAL_TOKENS'),
			Text::_('COM_PLUGNMEET_AI_SUMMARY_ESTIMATED_COST'),
		);

		foreach ($headers as $index => $title)
		{
			$sheet->getCell(chr(65 + $index) . '1')->setValue($title);
		}

		$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

		$rowIndex = 2;

		foreach ((array) ($data['usage_rows'] ?? array()) as $row)
		{
			$sheet->getCell('A' . $rowIndex)->setValue($row['role']);
			$sheet->getCell('B' . $rowIndex)->setValue($row['prompt_tokens']);
			$sheet->getCell('C' . $rowIndex)->setValue($row['completion_tokens']);
			$sheet->getCell('D' . $rowIndex)->setValue($row['total_tokens']);
			$sheet->getCell('E' . $rowIndex)->setValue($row['cost']);
			$rowIndex++;
		}

		$sheet->getCell('A' . $rowIndex)->setValue(Text::_('COM_PLUGNMEET_AI_SUMMARY_TOTAL'));
		$sheet->getCell('B' . $rowIndex)->setValue((int) ($data['total_input_tokens'] ?? 0));
		$sheet->getCell('C' . $rowIndex)->setValue((int) ($data['total_output_tokens'] ?? 0));
		$sheet->getCell('D' . $rowIndex)->setValue((int) ($data['total_tokens'] ?? 0));
		$sheet->getCell('E' . $rowIndex)->setValue((float) ($data['total_estimated_cost'] ?? 0));
		$sheet->getStyle('A' . $rowIndex . ':E' . $rowIndex)->getFont()->setBold(true);

		if (empty($data['has_avatar_usage']))
		{
			return;
		}

		$rowIndex += 2;
		$sheet->getCell('A' . $rowIndex)->setValue(Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_USAGE'));
		$sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getFont()->setBold(true);
		$rowIndex++;

		$avatarRows = array(
			Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_DURATION') => (string) ($data['avatar_duration_formatted'] ?? ''),
		);

		if ('' !== (string) ($data['avatar_provider'] ?? ''))
		{
			$avatarRows = array(Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_PROVIDER') => (string) $data['avatar_provider']) + $avatarRows;
		}

		if ('' !== (string) ($data['avatar_id'] ?? ''))
		{
			$avatarRows[Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_ID')] = (string) $data['avatar_id'];
		}

		if ('' !== (string) ($data['avatar_estimated_cost'] ?? ''))
		{
			$avatarRows[Text::_('COM_PLUGNMEET_AI_SUMMARY_AVATAR_ESTIMATED_COST')] = (float) $data['avatar_estimated_cost'];
		}

		foreach ($avatarRows as $label => $value)
		{
			$sheet->getCell('A' . $rowIndex)->setValue((string) $label);
			$sheet->getCell('B' . $rowIndex)->setValue($value);
			$rowIndex++;
		}
	}

	private function setSheetTitle($sheet, string $title)
	{
		$title = trim($title);

		if ('' === $title)
		{
			$title = 'Sheet';
		}

		$sheet->setTitle(mb_substr($title, 0, 31));
	}

	private function formatToolsUsed(array $toolsUsed)
	{
		$items = array();

		foreach ($toolsUsed as $tool)
		{
			$items[] = $tool['name'] . ' (' . $tool['count'] . ')';
		}

		return implode(', ', $items);
	}

	private function resolveStudentName(string $studentName, string $exUserId)
	{
		if ('' === trim($studentName) && ctype_digit($exUserId) && (int) $exUserId > 0)
		{
			try
			{
				$users = PlugnmeetHelper::getUsersByIds(array((int) $exUserId));

				if (!empty($users) && isset($users[0]->name))
				{
					$studentName = (string) $users[0]->name;
				}
			}
			catch (Exception $e)
			{
				// Keep the value reported by the artifact.
			}
		}

		return trim($studentName);
	}

	private function formatTime($milliseconds)
	{
		$seconds = (int) floor(((int) $milliseconds) / 1000);

		if ($seconds <= 0)
		{
			return '';
		}

		$format = Text::_('DATE_FORMAT_LC6');

		if ('DATE_FORMAT_LC6' === $format)
		{
			$format = 'Y-m-d H:i:s';
		}

		try
		{
			$date = new \DateTimeImmutable('@' . $seconds);

			return $date->setTimezone($this->timezone)->format($format);
		}
		catch (Exception $e)
		{
			return gmdate('Y-m-d H:i:s', $seconds);
		}
	}

	private function formatDuration(int $seconds)
	{
		$seconds = max(0, $seconds);
		$hours   = (int) floor($seconds / 3600);
		$minutes = (int) floor(($seconds % 3600) / 60);
		$rest    = $seconds % 60;

		if ($hours > 0)
		{
			return sprintf('%dh %dm %ds', $hours, $minutes, $rest);
		}

		if ($minutes > 0)
		{
			return sprintf('%dm %ds', $minutes, $rest);
		}

		return sprintf('%ds', $rest);
	}

	private function masteryBadgeClass($level)
	{
		switch (strtolower(trim((string) $level)))
		{
			case 'advanced':
			case 'mastered':
			case 'proficient':
				return 'bg-success';

			case 'intermediate':
			case 'progressing':
				return 'bg-info text-dark';

			case 'beginner':
			case 'developing':
				return 'bg-warning text-dark';
		}

		return 'bg-secondary';
	}

	private function statusBadgeClass($status)
	{
		switch (strtolower(trim((string) $status)))
		{
			case 'mastered':
			case 'completed':
			case 'passed':
				return 'bg-success';

			case 'practicing':
			case 'practised':
			case 'in progress':
			case 'introduced':
				return 'bg-info text-dark';

			case 'struggling':
			case 'needs work':
			case 'not started':
				return 'bg-warning text-dark';
		}

		return 'bg-secondary';
	}
}
