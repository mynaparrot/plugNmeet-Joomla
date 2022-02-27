<?php
/**
 * @package 	plugNmeet
 * @subpackage	room.php
 * @version		1.0.0
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Plugnmeet Room Model
 */
class PlugnmeetModelRoom extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_plugnmeet.room';

	/**
	 * Model user data.
	 *
	 * @var        strings
	 */
	protected $user;
	protected $userId;
	protected $guest;
	protected $groups;
	protected $levels;
	protected $app;
	protected $input;
	protected $uikitComp;

	/**
	 * @var object item
	 */
	protected $item;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		// Get the itme main id
		$id = $this->input->getInt('id', null);
		$this->setState('room.id', $id);

		// Load the parameters.
		$params = $this->app->getParams();
		$this->setState('params', $params);
		parent::populateState();
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$this->user = JFactory::getUser();
		// check if this user has permission to access item
		if (!$this->user->authorise('site.room.access', 'com_plugnmeet'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_PLUGNMEET_NOT_AUTHORISED_TO_VIEW_ROOM'), 'error');
			// redirect away to the default view if no access allowed.
			$app->redirect(JRoute::_('index.php?option=com_plugnmeet&view=categories'));
			return false;
		}
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();
		$this->initSet = true;

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('room.id');
		
		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				// Get a db connection.
				$db = JFactory::getDbo();

				// Create a new query object.
				$query = $db->getQuery(true);

				// Get from #__plugnmeet_room as a
				$query->select('a.*');
				$query->from($db->quoteName('#__plugnmeet_room', 'a'));
				// Check if $this->input->getInt("id", 0) is a string or numeric value.
				$checkValue = $this->input->getInt("id", 0);
				if (isset($checkValue) && PlugnmeetHelper::checkString($checkValue))
				{
					$query->where('a.id = ' . $db->quote($checkValue));
				}
				elseif (is_numeric($checkValue))
				{
					$query->where('a.id = ' . $checkValue);
				}
				else
				{
					return false;
				}

				// Reset the query using our newly populated query object.
				$db->setQuery($query);
				// Load the results as a stdClass object.
				$data = $db->loadObject();

				if (empty($data))
				{
					$app = JFactory::getApplication();
					// If no data is found redirect to default page and show warning.
					$app->enqueueMessage(JText::_('COM_PLUGNMEET_NOT_FOUND_OR_ACCESS_DENIED'), 'warning');
					$app->redirect(JRoute::_('index.php?option=com_plugnmeet&view=categories'));
					return false;
				}

				// set data object to item.
				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseWarning(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}


/***[JCBGUI.site_view.php_model.28.$$$$]***/
    public function doLoginToPNM($id, $name, $password)
    {
        $output = new stdClass();
        $output->status = false;
        $output->msg = "error";
        $output->token = "";

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array("room_id", "room_title", "moderator_pass", "attendee_pass", "welcome_message", "max_participants", "room_metadata"))
            ->from("#__plugnmeet_room")
            ->where("id = " . $db->q($id));
        $db->setQuery($query);

        $roomInfo = $db->loadObject();

        if ($password === $roomInfo->moderator_pass) {
            $isAdmin = true;
        } elseif ($password === $roomInfo->attendee_pass) {
            $isAdmin = false;
        } else {
            $output->msg = JText::_("COM_PLUGNMEET_PASSWORD_DIDNT_MATCH");
            return $output;
        }

        if (!class_exists("plugNmeetConnect")) {
            include JPATH_ROOT . "/administrator/components/com_plugnmeet/helpers/plugNmeetConnect.php";
        }
        $connect = new plugNmeetConnect();
        $isRoomActive = false;
        $room_metadata = json_decode($roomInfo->room_metadata, true);

        try {
            $res = $connect->isRoomActive($roomInfo->room_id);
            $isRoomActive = $res->status;
            $output->msg = $res->msg;
        } catch (Exception $e) {
            $output->msg = $e->getMessage();
            return $output;
        }

        if (!$isRoomActive) {
            try {
                $create = $connect->createRoom($roomInfo->room_id, $roomInfo->room_title, $roomInfo->welcome_message, $roomInfo->max_participants, "", $room_metadata);

                $isRoomActive = $create->status;
                $output->msg = $create->msg;
            } catch (Exception $e) {
                $output->msg = $e->getMessage();
                return $output;
            }
        }

        if ($isRoomActive) {
            try {
                $useId = JFactory::getUser()->id;
                if (!$useId) {
                    $session = JFactory::getSession();
                    $useId = $session->getId();
                }
                $join = $connect->getJoinToken($roomInfo->room_id, $name, $useId, $isAdmin);

                $output->token = $join->token;
                $output->status = $join->status;
                $output->msg = $join->msg;
            } catch (Exception $e) {
                $output->msg = $e->getMessage();
                return $output;
            }
        }

        return $output;
    }/***[/JCBGUI$$$$]***/

}
