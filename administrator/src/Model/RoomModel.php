<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\PlugnmeetHelper;


/**
 * Room model.
 *
 * @since  1.0.0
 */
class RoomModel extends AdminModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_PLUGNMEET';

	/**
	 * @var    string  Alias to manage history control
	 *
	 * @since  1.0.0
	 */
	public $typeAlias = 'com_plugnmeet.room';

	/**
	 * @var    null  Item data
	 *
	 * @since  1.0.0
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 *
	 * @since   1.0.0
	 */
	public function getTable($type = 'Room', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_plugnmeet.room',
			'room',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}


	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		if (!$this->checkRequiredFields($data))
		{
			return false;
		}

		$app = Factory::getApplication();
		if ($data['alias'] == null)
		{
			if ($app->get('unicodeslugs') == 1)
			{
				$data['alias'] = OutputFilter::stringUrlUnicodeSlug($data['title']);
			}
			else
			{
				$data['alias'] = OutputFilter::stringURLSafe($data['title']);
			}
		}
		else
		{
			if ($app->get('unicodeslugs') == 1)
			{
				$data['alias'] = OutputFilter::stringUrlUnicodeSlug($data['alias']);
			}
			else
			{
				$data['alias'] = OutputFilter::stringURLSafe($data['alias']);
			}
		}

		$room_metadata = [];
		foreach (PlugnmeetHelper::$roomMetadataItems as $metadataItem)
		{
			if (isset($data[$metadataItem]))
			{
				$room_metadata[$metadataItem] = $data[$metadataItem];
				unset($data[$metadataItem]);
			}
		}
		$data["room_metadata"] = $room_metadata;

		return parent::save($data);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_plugnmeet.edit.room.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->room_metadata))
			{
				$room_metadata = json_decode($item->room_metadata, true);
				foreach (PlugnmeetHelper::$roomMetadataItems as $metadataItem)
				{
					if (isset($room_metadata[$metadataItem]))
					{
						$item->$metadataItem = $room_metadata[$metadataItem];
					}
				}
			}

			if (!empty($item->design_customisation))
			{
				$item->design_customisation = json_decode($item->design_customisation);
			}
		}

		return $item;
	}

	/**
	 * Method to duplicate an Room
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  \Exception
	 */
	public function duplicate(&$pks)
	{
		throw new \Exception("not allow");
	}

	private function checkRequiredFields(array $data)
	{
		$requiredFields = array(
			"room_title"     => "COM_PLUGNMEET_ROOM_TITLE",
			"moderator_pass" => "COM_PLUGNMEET_ROOM_MODERATOR_PASS",
			"attendee_pass"  => "COM_PLUGNMEET_ROOM_ATTENDEE_PASS"
		);
		$app            = Factory::getApplication();
		foreach ($requiredFields as $key => $label)
		{
			if (empty($data[$key]))
			{
				$app->enqueueMessage(Text::sprintf("COM_PLUGNMEET_ERROR_MESSAGE_REQUIRED_FIELD_MISSING", Text::_($label)), 'error');

				return false;
			}
		}

		if ($data["moderator_pass"] == $data["attendee_pass"])
		{
			$app->enqueueMessage(Text::_("COM_PLUGNMEET_ERROR_MESSAGE_SAME_PASS"), 'error');

			return false;
		}

		return true;
	}
}
