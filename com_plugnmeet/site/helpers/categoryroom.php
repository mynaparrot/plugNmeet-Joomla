<?php


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Plugnmeet Room Component Category Tree
 */
class PlugnmeetRoomCategories extends JCategories
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 */
	public function __construct($options = array())
	{
		$options['table'] = '#__plugnmeet_room';
		$options['extension'] = 'com_plugnmeet.room';

		parent::__construct($options);
	}
}
