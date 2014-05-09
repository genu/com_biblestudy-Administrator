<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;
/**
 * Update for 7.0.1 class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JBSM701Update
{

	/**
	 * Upgrade for 7.0.1
	 *
	 * @return boolean
	 */
	public function do701update()
	{

		$db = JFactory::getDBO();

		// Modify table topics
		$tables      = $db->getTableColumns('#__bsms_topics');
		$languagetag = false;
		$paramstag   = false;
		$dbhelper    = new JBSMDbHelper;

		if (is_array($tables))
		{
			if (isset($tables['languages']))
			{
				$languagetag = true;
				$changefield = array(array('table' => '#__bsms_topics', 'field' => 'languages', 'type' => 'CHANGE', 'command' => '`params` varchar(511) NULL'));

				if (!$dbhelper->alterDB($changefield, "Build 701: "))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

					return false;
				}
			}
			elseif (isset($tables['params']))
			{
				$paramstag = true;
			}
			if (!$languagetag && !$paramstag)
			{

				$addfield = array(array(
					'table'   => '#__bsms_topics', 'field' => 'params', 'type' => 'ADD',
					'command' => 'varchar(511) NULL'
				));

				if (!$dbhelper->alterDB($addfield, "Build 701: "))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

					return false;
				}

			}

		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

			return false;
		}
		$fixtopics = $this->updatetopics();

		if (!$fixtopics)
		{
			return false;
		}

		return true;
	}

	/**
	 * Update the Topics
	 *
	 * @return boolean
	 */
	public function updatetopics()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->insert('#__bsms_studytopics (study_id, topic_id)')
			->select('#__bsms_studies.id, #__bsms_studies.topics_id ')
			->from('#__bsms_studies')
			->where('#__bsms_studies.topics_id > 0');
		$db->setQuery($query);

		if (!$db->execute())
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

			return false;
		}

		return true;
	}

}
