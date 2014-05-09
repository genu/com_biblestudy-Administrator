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
 * Class for updating the alias in certain tables
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class JBSMAlias
{
	/**
	 * Extension Name
	 *
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Update Alias
	 *
	 * @since 7.1.0
	 * @return string
	 */
	public static function updateAlias()
	{
		$done    = 0;
		$db      = JFactory::getDBO();
		$objects = self::getObjects();
		$results = array();

		foreach ($objects as $object)
		{
			$results[] = self::getTableQuery($table = $object['name'], $title = $object['titlefield']);
		}

		foreach ($results as $result)
		{
			foreach ($result as $r)
			{
				if (!$r['title'])
				{
					// Do nothing
				}
				else
				{
					$alias = JFilterOutput::stringURLSafe($r['title']);
					$query = $db->getQuery(true);
					$query->update($db->qn($r['table']))
						->set('alias=' . $db->q($alias))
						->where('id=' . $db->q($r['id']));
					$db->setQuery($query);
					$db->query();
					$done++;
				}
			}
		}

		return $done;
	}

	/**
	 * Get Table fields for updating.
	 *
	 * @param   string $table  Table
	 * @param   string $title  Title
	 *
	 * @return boolean|array
	 */
	private static function getTableQuery($table, $title)
	{
		$data = array();

		if (!$table)
		{
			return false;
		}
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id, alias, ' . $title)
			->from($table);
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			if (!$result->alias)
			{
				$temp   = array(
					'id'    => $result->id,
					'title' => $result->$title,
					'alias' => $result->alias,
					'table' => $table
				);
				$data[] = $temp;
			}
		}

		return $data;
	}

	/**
	 * Get Object's for tables
	 *
	 * @return array
	 */
	private static function getObjects()
	{
		$objects = array(
			array('name' => '#__bsms_series', 'titlefield' => 'series_text'),
			array('name' => '#__bsms_studies', 'titlefield' => 'studytitle'),
			array('name' => '#__bsms_message_type', 'titlefield' => 'message_type'),
			array('name' => '#__bsms_teachers', 'titlefield' => 'teachername'),
		);

		return $objects;
	}

}
