<?php

/**
 * Controller for Comments
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('BiblestudyControllerComments', JPATH_ADMINISTRATOR . '/components/com_biblestudy/controllers/comments.php');

/**
 * Controller for Comments
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerCommentlist extends BiblestudyControllerComments
{

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 * @param   array   $config  Set ignore request
	 *
	 * @return JModel
	 *
	 * @since 7.0
	 */
	public function &getModel(
		$name = 'Commentform',
		$prefix = 'BiblestudyModel',
		$config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

}
