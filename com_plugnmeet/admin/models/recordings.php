<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;

/**
 * Plugnmeet List Model for Recordings
 */
class PlugnmeetModelRecordings extends ListModel
{
	/**
	 * Model user data.
	 *
	 * @var  strings
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
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$this->user = JFactory::getUser();
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups	= $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->initSet = true; 
		// Make sure all records load, since no pagination allowed.
		$this->setState('list.limit', 0);
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__plugnmeet_room as a
		$query->select('a.*');
		$query->from($db->quoteName('#__plugnmeet_room', 'a'));
		// Get where a.published is 1
		$query->where('a.published = 1');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$user = JFactory::getUser();
		// check if this user has permission to access items
		if (!$user->authorise('recordings.access', 'com_plugnmeet'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('Not authorised!'), 'error');
			// redirect away if not a correct (TODO for now we go to default view)
			$app->redirect('index.php?option=com_plugnmeet');
			return false;
		}
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = JComponentHelper::getParams('com_plugnmeet', true);

		// Insure all item fields are adapted where needed.
		if (PlugnmeetHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = (isset($item->alias) && isset($item->id)) ? $item->id.':'.$item->alias : $item->id;
			}
		}

		// return items
		return $items;
	}


/***[JCBGUI.custom_admin_view.php_model.1.$$$$]***/
    public function doGetRecordings($roomId, $from, $limit, $orderBy)
    {
        $output = new stdClass();
        $output->status = false;
        $output->msg = "error";
        $output->result = [];

        if (!class_exists("plugNmeetConnect")) {
            require JPATH_ROOT . "/administrator/components/com_plugnmeet/helpers/plugNmeetConnect.php";
        }

        $connect = new plugNmeetConnect();
        $roomIds = array($roomId);
        $res = $connect->getRecordings($roomIds, $from, $limit, $orderBy);

        $output->status = $res->getStatus();
        $output->msg = $res->getResponseMsg();
		if(isset($res->getRawResponse()->result)){
			$output->result = $res->getRawResponse()->result;
		}

        return $output;
    }

    public function doDownloadRecording($recordingId)
    {
        $output = new stdClass();
        $output->status = false;
        $output->msg = "error";

        if (!class_exists("plugNmeetConnect")) {
            require JPATH_ROOT . "/administrator/components/com_plugnmeet/helpers/plugNmeetConnect.php";
        }

        $connect = new plugNmeetConnect();
        $res = $connect->getRecordingDownloadLink($recordingId);
        $output->status = $res->getStatus();
        $output->msg = $res->getResponseMsg();

        if ($res->getStatus() && $res->getToken()) {
            $params = JComponentHelper::getParams("com_plugnmeet");
            $output->url = $params->get("plugnmeet_server_url") . "/download/recording/" . $res->getToken();
        }
        return $output;
    }

    public function doDeleteRecording($recordingId)
    {
        $output = new stdClass();
        $output->status = false;
        $output->msg = "error";

        if (!class_exists("plugNmeetConnect")) {
            require JPATH_ROOT . "/administrator/components/com_plugnmeet/helpers/plugNmeetConnect.php";
        }

        $connect = new plugNmeetConnect();
        $res = $connect->deleteRecording($recordingId);
        $output->status = $res->getStatus();
        $output->msg = $res->getResponseMsg();
        
        return $output;
    }/***[/JCBGUI$$$$]***/

}
