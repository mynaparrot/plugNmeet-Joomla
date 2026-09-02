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

/**
 * Helper to parse analytics artifacts and generate Excel reports.
 *
 * @since  1.0.0
 */
class AnalyticsHelper
{
	/**
	 * @var \Mynaparrot\Plugnmeet\AnalyticsFormatter
	 */
	private $formatter;

	/**
	 * @var array
	 */
	private $roomData = array();

	/**
	 * @var array
	 */
	private $usersData = array();

	/**
	 * @var string
	 */
	private $artifactId;

	/**
	 * @param   string  $artifactId  The artifact id.
	 *
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function __construct(string $artifactId)
	{
		$this->artifactId = $artifactId;

		$connect = new plugNmeetConnect();
		$res     = $connect->getArtifactDownloadToken($this->artifactId);

		if (!$res->getStatus())
		{
			throw new Exception($res->getMsg());
		}

		$params = ComponentHelper::getParams('com_plugnmeet');
		$url    = rtrim($params->get('plugnmeet_server_url'), '/') . '/download/artifact/' . $res->getToken();

		$response = HttpFactory::getHttp()->get($url, array(), 60);

		if ($response->code !== 200)
		{
			throw new Exception(sprintf('HTTP Error: %d', $response->code));
		}

		$data = json_decode($response->body, true);
		if (empty($data))
		{
			throw new Exception(Text::_('COM_PLUGNMEET_ARTIFACT_INVALID_DATA'));
		}

		$timezone = 'UTC';
		try
		{
			$timezone = Factory::getApplication()->getIdentity()->getTimezone()->getName();
		}
		catch (Exception $e)
		{
			// Keep the UTC fallback.
		}

		$this->formatter = plugNmeetConnect::getAnalyticsFormatter($data, $timezone);

		$formattedData   = $this->formatter->getFormattedEventData();
		$this->roomData  = $formattedData['room'];
		$this->usersData = $formattedData['users'];
	}

	/**
	 * Get the context data for the artifact details page.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getContextData()
	{
		$context = array(
			'roomDetails' => array(),
			'hasUsers'    => false,
			'userHeaders' => array(),
			'userRows'    => array(),
		);

		$roomFields = $this->getRoomFields();
		$userFields = $this->getUserFields();
		$roomLabels = $this->getRoomAnalyticsLabels();
		$userLabels = $this->getUserAnalyticsLabels();

		foreach ($roomFields as $field)
		{
			$value = $this->roomData[$field] ?? 0;
			if (is_array($value))
			{
				continue;
			}

			if ($field === 'room_duration' || $field === 'speech_service_total_usage')
			{
				$value = $this->formatSecondsToTime($value);
			}
			else if ($field === 'enabled_e2ee')
			{
				$value = $value ? Text::_('COM_PLUGNMEET_YES') : Text::_('COM_PLUGNMEET_NO');
			}

			$context['roomDetails'][] = array('label' => $roomLabels[$field] ?? $field, 'value' => $value);
		}

		if (!empty($this->usersData))
		{
			$context['hasUsers']    = true;
			$context['userHeaders'] = array_map(function ($field) use ($userLabels) {
				return $userLabels[$field] ?? $field;
			}, $userFields);

			foreach ($this->usersData as $userRow)
			{
				$rowData = array();

				foreach ($userFields as $field)
				{
					$value = $userRow[$field] ?? 0;

					if (is_bool($value))
					{
						$value = $value ? Text::_('COM_PLUGNMEET_YES') : Text::_('COM_PLUGNMEET_NO');
						$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
					}
					else if (is_array($value))
					{
						if ($field === 'joined' || $field === 'left')
						{
							$arr   = array_map(function ($d) {
								return $this->formatTimestamp($d);
							}, empty($value) ? array() : $value);
							$value = implode('<br><br>', $arr);
						}
						else if ($field === 'connection_quality')
						{
							$connectionLabels = $this->getConnectionQualityLabels();
							$arr              = array_map(function ($k, $v) use ($connectionLabels) {
								return ($connectionLabels[$k] ?? $k) . ': ' . $v;
							}, array_keys(empty($value) ? array() : $value), array_values(empty($value) ? array() : $value));
							$value            = implode('<br>', $arr);
						}
						else
						{
							$value = Text::_('COM_PLUGNMEET_ARTIFACT_SEE_EXCEL_REPORT');
						}
					}
					else if ($field === 'duration' || $field === 'talked_duration' || $field === 'speech_service_total_usage')
					{
						$value = $this->formatSecondsToTime($value);
						$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
					}
					else
					{
						$value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
					}

					$rowData[] = array('value' => $value);
				}

				$context['userRows'][] = array('data' => $rowData);
			}
		}

		return $context;
	}

	/**
	 * Generate an Excel file for the analytics artifact.
	 *
	 * @return  array  An array with the generated file path and name.
	 *
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function generateXlsxFile()
	{
		$spreadsheet = new Spreadsheet();
		$headerStyle = array(
			'font' => array(
				'bold'  => true,
				'color' => array('rgb' => '1171A3'),
				'size'  => 12,
			),
		);

		$this->formatRoomXlsx($spreadsheet, $headerStyle);
		$this->formatUsersXlsx($spreadsheet, $headerStyle);
		$this->formatPollsXlsx($spreadsheet, $headerStyle);
		$this->formatWhiteboardFilesXlsx($spreadsheet, $headerStyle);

		$writer   = new Xlsx($spreadsheet);
		$tmpPath  = Factory::getApplication()->getConfig()->get('tmp_path', sys_get_temp_dir());
		$filename = 'plugnmeet_analytics_' . $this->artifactId . '.xlsx';
		$path     = rtrim($tmpPath, '/') . '/' . $filename;

		$writer->save($path);

		return array('path' => $path, 'filename' => $filename);
	}

	/**
	 * Get the formatted room fields.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	private function getRoomFields()
	{
		return $this->formatter->getRoomFields();
	}

	/**
	 * Get the formatted user fields.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	private function getUserFields()
	{
		return $this->formatter->getUserFields();
	}

	/**
	 * Format seconds to a H:i:s string.
	 *
	 * @param   mixed  $seconds  The number of seconds.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function formatSecondsToTime($seconds)
	{
		return $this->formatter->formatSecondsToTime($seconds);
	}

	/**
	 * Format a timestamp to a readable date.
	 *
	 * @param   mixed  $timestamp  The timestamp value.
	 * @param   bool   $ms         Whether the timestamp is in milliseconds.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function formatTimestamp($timestamp, $ms = true)
	{
		return $this->formatter->formatTimestamp($timestamp, $ms);
	}

	/**
	 * Format the room analytics sheet.
	 *
	 * @param   Spreadsheet  $spreadsheet  The spreadsheet object.
	 * @param   array        $headerStyle  The header style array.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function formatRoomXlsx($spreadsheet, $headerStyle)
	{
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle(Text::_('COM_PLUGNMEET_ANALYTICS_SHEET_ROOM_INFO'));

		$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->getColumnDimension('B')->setWidth(50);

		$rowIndex   = 1;
		$roomLabels = $this->getRoomAnalyticsLabels();

		foreach ($this->formatter->getRoomFields() as $field)
		{
			$data  = $this->roomData[$field] ?? 0;
			$title = $roomLabels[$field] ?? $field;
			$sheet->getCell('A' . $rowIndex)->setValue($title);
			$sheet->getStyle('A' . $rowIndex)->applyFromArray($headerStyle);

			$formattedData = $this->formatRoomDataForXlsx($data, $field);
			$sheet->getCell('B' . $rowIndex)->setValue((string) $formattedData);

			$rowIndex++;
		}
	}

	/**
	 * Format the users analytics sheet.
	 *
	 * @param   Spreadsheet  $spreadsheet  The spreadsheet object.
	 * @param   array        $headerStyle  The header style array.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function formatUsersXlsx($spreadsheet, $headerStyle)
	{
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle(Text::_('COM_PLUGNMEET_ANALYTICS_SHEET_USERS_INFO'));

		$columnMap   = array();
		$columnIndex = 'A';
		$userLabels  = $this->getUserAnalyticsLabels();

		foreach ($this->formatter->getUserFields() as $field)
		{
			$columnMap[$field] = $columnIndex++;
		}

		foreach ($columnMap as $field => $colIndex)
		{
			$sheet->getColumnDimension($colIndex)->setWidth(25);
			$title = $userLabels[$field] ?? $field;
			$sheet->getCell($colIndex . '1')->setValue($title);
			$sheet->getStyle($colIndex . '1')->applyFromArray($headerStyle);
		}

		$rowIndex = 2;
		foreach ($this->usersData as $user)
		{
			foreach ($columnMap as $field => $colIndex)
			{
				$data          = $user[$field] ?? 0;
				$formattedData = $this->formatUserDataForXlsx($data, $field);
				$sheet->getCell($colIndex . $rowIndex)->setValue((string) $formattedData);
			}

			$rowIndex++;
		}
	}

	/**
	 * Format the polls sheet.
	 *
	 * @param   Spreadsheet  $spreadsheet  The spreadsheet object.
	 * @param   array        $headerStyle  The header style array.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function formatPollsXlsx($spreadsheet, $headerStyle)
	{
		if (empty($this->roomData['polls']))
		{
			return;
		}

		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle(Text::_('COM_PLUGNMEET_ANALYTICS_SHEET_POLLS'));

		$sheet->getColumnDimension('A')->setWidth(50);
		$sheet->getColumnDimension('B')->setWidth(50);
		$sheet->getColumnDimension('C')->setWidth(30);

		$sheet->getCell('A1')->setValue(Text::_('COM_PLUGNMEET_ANALYTICS_QUESTION'));
		$sheet->getCell('B1')->setValue(Text::_('COM_PLUGNMEET_ANALYTICS_OPTIONS'));
		$sheet->getCell('C1')->setValue(Text::_('COM_PLUGNMEET_ANALYTICS_CREATED_AT'));
		$sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

		$i = 2;
		foreach ($this->roomData['polls'] as $poll)
		{
			$sheet->getCell('A' . $i)->setValue($poll['question']);
			$sheet->getCell('C' . $i)->setValue($poll['created']);

			$arr  = array_map(function ($v) {
				return $v['text'] . ': ' . $v['responses'];
			}, $poll['options']);
			$data = implode("\n", $arr);
			$sheet->getCell('B' . $i)->setValue($data);
			$sheet->getStyle('B' . $i)->getAlignment()->setWrapText(true);

			$i++;
		}
	}

	/**
	 * Format the whiteboard files sheet.
	 *
	 * @param   Spreadsheet  $spreadsheet  The spreadsheet object.
	 * @param   array        $headerStyle  The header style array.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function formatWhiteboardFilesXlsx($spreadsheet, $headerStyle)
	{
		if (empty($this->roomData['whiteboard_files']))
		{
			return;
		}

		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle(Text::_('COM_PLUGNMEET_ANALYTICS_SHEET_WHITEBOARD_FILES'));

		$sheet->getColumnDimension('A')->setWidth(50);
		$sheet->getColumnDimension('B')->setWidth(30);

		$sheet->getCell('A1')->setValue(Text::_('COM_PLUGNMEET_ANALYTICS_FILE_NAME'));
		$sheet->getCell('B1')->setValue(Text::_('COM_PLUGNMEET_ANALYTICS_CREATED_AT'));
		$sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

		$i = 2;
		foreach ($this->roomData['whiteboard_files'] as $file)
		{
			$created = $this->formatter->formatTimestamp($file['time']);

			$sheet->getCell('A' . $i)->setValue($file['value']);
			$sheet->getCell('B' . $i)->setValue($created);

			$i++;
		}
	}

	/**
	 * Format a room data value for the Excel sheet.
	 *
	 * @param   mixed   $data   The data value.
	 * @param   string  $field  The field name.
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	private function formatRoomDataForXlsx($data, $field)
	{
		if ($field === 'room_duration' || $field === 'speech_service_total_usage')
		{
			return $this->formatter->formatSecondsToTime($data);
		}

		if (is_bool($data) || $field === 'enabled_e2ee')
		{
			return $data ? Text::_('COM_PLUGNMEET_YES') : Text::_('COM_PLUGNMEET_NO');
		}

		return $data;
	}

	/**
	 * Format a user data value for the Excel sheet.
	 *
	 * @param   mixed   $data   The data value.
	 * @param   string  $field  The field name.
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	private function formatUserDataForXlsx($data, $field)
	{
		if ($field === 'joined' || $field === 'left')
		{
			if (empty($data))
			{
				return 0;
			}

			$arr = array_map(function ($d) {
				return $this->formatter->formatTimestamp($d);
			}, (array) $data);

			return implode("\n", $arr);
		}

		if ($field === 'connection_quality')
		{
			if (empty($data))
			{
				return 0;
			}

			$connectionLabels = $this->getConnectionQualityLabels();
			$arr              = array_map(function ($k, $v) use ($connectionLabels) {
				return ($connectionLabels[$k] ?? $k) . ': ' . $v;
			}, array_keys((array) $data), array_values((array) $data));

			return implode("\n", $arr);
		}

		if (
			$field === 'duration' || $field === 'talked_duration' || $field === 'speech_service_total_usage'
			|| $field === 'webcam_duration' || $field === 'mic_duration'
		)
		{
			return $this->formatter->formatSecondsToTime($data);
		}

		if (is_bool($data))
		{
			return $data ? Text::_('COM_PLUGNMEET_YES') : Text::_('COM_PLUGNMEET_NO');
		}

		return $data;
	}

	/**
	 * Get the room analytics labels.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	private function getRoomAnalyticsLabels()
	{
		return array(
			'room_id'                      => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_ID'),
			'room_title'                   => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_TITLE'),
			'room_creation'                => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_CREATION'),
			'room_ended'                   => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_ENDED'),
			'room_duration'                => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_DURATION'),
			'room_total_users'             => Text::_('COM_PLUGNMEET_ANALYTICS_ROOM_TOTAL_USERS'),
			'enabled_e2ee'                 => Text::_('COM_PLUGNMEET_ANALYTICS_ENABLED_E2EE'),
			'recording_status'             => Text::_('COM_PLUGNMEET_ANALYTICS_RECORDING_STATUS'),
			'rtmp_status'                  => Text::_('COM_PLUGNMEET_ANALYTICS_RTMP_STATUS'),
			'speech_service_total_usage'   => Text::_('COM_PLUGNMEET_ANALYTICS_SPEECH_SERVICE_TOTAL_USAGE'),
			'external_media_player_status' => Text::_('COM_PLUGNMEET_ANALYTICS_EXTERNAL_MEDIA_PLAYER_STATUS'),
			'shared_notepad_status'        => Text::_('COM_PLUGNMEET_ANALYTICS_SHARED_NOTEPAD_STATUS'),
			'external_display_link_status' => Text::_('COM_PLUGNMEET_ANALYTICS_EXTERNAL_DISPLAY_LINK_STATUS'),
			'ingress_created'              => Text::_('COM_PLUGNMEET_ANALYTICS_INGRESS_CREATED'),
			'breakout_room'                => Text::_('COM_PLUGNMEET_ANALYTICS_BREAKOUT_ROOM'),
		);
	}

	/**
	 * Get the user analytics labels.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	private function getUserAnalyticsLabels()
	{
		return array(
			'name'                  => Text::_('COM_PLUGNMEET_ANALYTICS_NAME'),
			'id'                    => Text::_('COM_PLUGNMEET_ANALYTICS_USER_ID'),
			'ex_user_id'            => Text::_('COM_PLUGNMEET_ANALYTICS_USER_ID'),
			'user_id'               => Text::_('COM_PLUGNMEET_ANALYTICS_USER_ID'),
			'is_admin'              => Text::_('COM_PLUGNMEET_ANALYTICS_IS_ADMIN'),
			'duration'              => Text::_('COM_PLUGNMEET_ANALYTICS_DURATION'),
			'joined'                => Text::_('COM_PLUGNMEET_ANALYTICS_JOINED'),
			'left'                  => Text::_('COM_PLUGNMEET_ANALYTICS_LEFT'),
			'mic_status'            => Text::_('COM_PLUGNMEET_ANALYTICS_MIC_STATUS'),
			'mic_muted'             => Text::_('COM_PLUGNMEET_ANALYTICS_MIC_MUTED'),
			'mic_duration'          => Text::_('COM_PLUGNMEET_ANALYTICS_MIC_DURATION'),
			'talked'                => Text::_('COM_PLUGNMEET_ANALYTICS_TALKED'),
			'talked_duration'       => Text::_('COM_PLUGNMEET_ANALYTICS_TALKED_DURATION'),
			'webcam_status'         => Text::_('COM_PLUGNMEET_ANALYTICS_WEBCAM_STATUS'),
			'webcam_duration'       => Text::_('COM_PLUGNMEET_ANALYTICS_WEBCAM_DURATION'),
			'raise_hand'            => Text::_('COM_PLUGNMEET_ANALYTICS_RAISE_HAND'),
			'voted_poll'            => Text::_('COM_PLUGNMEET_ANALYTICS_VOTED_POLL'),
			'whiteboard_annotated'  => Text::_('COM_PLUGNMEET_ANALYTICS_WHITEBOARD_ANNOTATED'),
			'whiteboard_files'      => Text::_('COM_PLUGNMEET_ANALYTICS_WHITEBOARD_FILES'),
			'screen_share_status'   => Text::_('COM_PLUGNMEET_ANALYTICS_SCREEN_SHARE_STATUS'),
			'speech_services_usage' => Text::_('COM_PLUGNMEET_ANALYTICS_SPEECH_SERVICES_USAGE'),
			'public_chat'           => Text::_('COM_PLUGNMEET_ANALYTICS_PUBLIC_CHAT'),
			'private_chat'          => Text::_('COM_PLUGNMEET_ANALYTICS_PRIVATE_CHAT'),
			'chat_files'            => Text::_('COM_PLUGNMEET_ANALYTICS_CHAT_FILES'),
			'interface_invisible'   => Text::_('COM_PLUGNMEET_ANALYTICS_INTERFACE_INVISIBLE'),
			'connection_quality'    => Text::_('COM_PLUGNMEET_ANALYTICS_CONNECTION_QUALITY'),
		);
	}

	/**
	 * Get the connection quality labels.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	private function getConnectionQualityLabels()
	{
		return array(
			'excellent' => Text::_('COM_PLUGNMEET_ANALYTICS_EXCELLENT'),
			'good'      => Text::_('COM_PLUGNMEET_ANALYTICS_GOOD'),
			'poor'      => Text::_('COM_PLUGNMEET_ANALYTICS_POOR'),
		);
	}
}
