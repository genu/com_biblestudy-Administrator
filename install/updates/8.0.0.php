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

/**
 * Update for 8.0.0 class
 *
 * @package  BibleStudy.Admin
 * @since    8.0.0
 */
class JBSM800Update
{

	/**
	 * Method to Update to 8.0.0
	 *
	 * @return boolean
	 */
	public function update800()
	{
		self::migrate_topics();
		self::fix_mediafile_params();

		return true;
	}

	/**
	 * Migrate Topics
	 *
	 * @return bool
	 */
	private function migrate_topics()
	{
		$db       = JFactory::getDBO();
		$registry = new JRegistry;
		$query    = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_topics');
		$db->setQuery($query);
		$topics = $db->loadObjectList();

		foreach ($topics as $topic)
		{
			// Case: Params is null
			if (is_null($topic->params))
			{
				/**
				 * Leave record alone, since it would load language from ini file
				 *
				 * @deprecated 8.2.0
				 **/
				// Case: Params is not null
			}
			else
			{
				$registry->loadString($topic->params);
				$params = $registry->toArray();

				// Loop through every param language and create a new rocord
				foreach ($params as $key => $value)
				{
					// Load Topic table
					JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_biblestudy/tables/');
					$table = JTable::getInstance('Topic', "Table");

					$new_topic           = clone $topic;
					$new_topic->id       = null;
					$new_topic->params   = null;
					$new_topic->language = $key;

					if ($key == 'en-GB')
					{
						$new_topic->language = '*';
					}
					$new_topic->topic_text = $value;
					$table->save($new_topic);
					$this->update_studies($table, $topic->id);
				}

				// Delete old topic
				$query = $db->getQuery(true);
				$query->delete('#__bsms_topics')
					->where('id = ' . $topic->id);
				$db->setQuery($query);

				if (!$db->execute())
				{

					JFactory::getApplication()->enqueueMessage('Filed to delete old topic id: ' . $topic->id, 'warning');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Update studies to reference newly created topics
	 *
	 * @param   Object $topic_table   Object containing the saved topic record
	 * @param   String $old_topic_id  Reference to the old topic id
	 *
	 * @return void
	 */
	private function update_studies($topic_table, $old_topic_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('studytopics.id, studytopics.topic_id, studytopics.study_id, study.language as study_language')
			->from('#__bsms_studytopics AS studytopics')
			->join('LEFT', '#__bsms_topics as topic ON topic.id = studytopics.topic_id')
			->join('LEFT', '#__bsms_studies as study on study.id = studytopics.study_id')
			->where('studytopics.topic_id = ' . $old_topic_id);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			if ($topic_table->language == $result->study_language)
			{
				// Change study topic reference
				$query = $db->getQuery(true);
				$query->update('#__bsms_studytopics as studytopics')
					->set('topic_id = ' . $topic_table->id)
					->where('studytopics.id = ' . $result->id);
				$db->setQuery($query);

				if (!$db->execute())
				{
					JFactory::getApplication()->enqueueMessage('Update of Studies topics Filed' . $result->id, 'warning');

					return;
				}
			}
		}
	}

	/**
	 * Fix Media File Player settings
	 *
	 * @return mixed
	 */
	public function fix_mediafile_params()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles')
			->where('`params` LIKE ' . $db->q('%internal_popup%'));
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			$registry = new JRegistry;
			$registry->loadString($result->params);
			$old_params = $registry->toObject();

			$new_params = new stdClass;

			if (isset($old_params->playerwidth))
			{
				$new_params->playerwidth = $old_params->playerwidth;
			}
			else
			{
				$new_params->playerwidth = '';

			}
			if (isset($new_params->playerheight))
			{
				$new_params->playerheight = $old_params->playerheight;
			}
			else
			{
				$new_params->playerheight = '';

			}
			if (isset($new_params->itempopuptitle))
			{
				$new_params->itempopuptitle = $old_params->itempopuptitle;
			}
			else
			{
				$new_params->itempopuptitle = '';

			}
			if (isset($new_params->itempopupfooter))
			{
				$new_params->itempopupfooter = $old_params->itempopupfooter;
			}
			else
			{
				$new_params->itempopupfooter = '';

			}
			if (isset($new_params->popupmargin))
			{
				$new_params->popupmargin = $old_params->popupmargin;
			}
			else
			{
				$new_params->popupmargin = 50;

			}
			if (isset($new_params->autostart))
			{
				$new_params->autostart = $old_params->autostart;
			}
			else
			{
				$new_params->autostart = 0;

			}

			// Pars thought the records and correct the params for the player upgrade errors.
			// Store the combined new and existing values back as a JSON string
			$paramsString = json_encode($new_params);
			$query        = $db->getQuery(true);
			$query->update('#__bsms_mediafiles')
				->set('params = ' . $db->q($paramsString))
				->where('id = ' . $db->q($result->id));
			$db->setQuery($query);

			if (!$db->execute())
			{
				JFactory::getApplication()->enqueueMessage('Update of mediafile params Filed' . $result->id, 'warning');

				return false;
			}
		}

		return true;
	}
}
