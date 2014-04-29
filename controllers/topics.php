<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Teachers list controller class.
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerTopics extends JControllerAdmin
{

	/**
	 * Proxy for getModel
	 *
	 * @param   string $name    The model name. Optional.
	 * @param   string $prefix  The class prefix. Optional.
	 * @param   array  $config  Configuration array for model. Optional.
	 *
	 * @return JModel
	 *
	 * @since 7.0.0
	 */
	public function getModel($name = 'Topic', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

}
