<?php

/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mynaparrot\Component\Plugnmeet\Site\Service;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Content Component Category Tree
 *
 * @since  1.0.0
 */
class Category extends Categories
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		$options['table']     = '#__plugnmeet_rooms';
		$options['extension'] = 'com_plugnmeet.rooms';
		parent::__construct($options);
	}
}
