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
include_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.backup.php';
jimport('joomla.application.component.controllerform');

/**
 * Template controller class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerTemplate extends JControllerForm
{

	/**
	 * Class constructor.
	 *
	 * @param   array $config  A named array of configuration variables.
	 *
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Copy Template
	 *
	 * @return void
	 */
	public function copy()
	{
		$input = new JInput;
		$cid   = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		$model = & $this->getModel('template');

		if ($model->copy($cid))
		{
			$msg = JText::_('JBS_TPL_TEMPLATE_COPIED');
		}
		else
		{
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=templates', $msg);
	}

	/**
	 * Make Template Default
	 *
	 * @return void
	 */
	public function makeDefault()
	{
		$app   = JFactory::getApplication();
		$input = new JInput;
		$cid   = $input->get('cid', array(0), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			$app->enqueueMessage(JText::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'), 'error');
		}

		$model = $this->getModel('template');

		if (!$model->makeDefault($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=templates');
	}

	/**
	 * Get Template Settings
	 *
	 * @param   string $template  ?
	 *
	 * @return boolean|string
	 *
	 * @deprecated 8.0.0 Not used in scope bcc
	 */
	public function getTemplate($template)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('tc.id, tc.templatecode,tc.type,tc.filename');
		$query->from('#__bsms_templatecode as tc');
		$query->where('tc.filename ="' . $template . '"');
		$db->setQuery($query);

		if (!$object = $db->loadObject())
		{
			return false;
		}
		$templatereturn = '
                        INSERT INTO #__bsms_templatecode SET `type` = "' . $db->q($object->type) . '",
                        `templatecode` = "' . $db->q($object->templatecode) . '",
                        `filename`="' . $db->q($template) . '",
                        `published` = "1";
                        ';

		return $templatereturn;
	}

}
