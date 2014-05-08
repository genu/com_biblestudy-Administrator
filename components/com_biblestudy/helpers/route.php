<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * Biblestudy Component Route Helper
 *
 * @static
 * @package  BibleStudy.Site
 * @since    7.2
 */
abstract class JBSMRoute
{

	/**
	 * Lookup
	 *
	 * @var string
	 */
	protected static $lookup;

	/**
	 * Get Article Rout
	 *
	 * @param   int $id        The route of the study item
	 * @param   int $language  The state of language
	 *
	 * @return string
	 */
	public static function getArticleRoute($id, $language = 0)
	{
		$needles = array(
			'article' => array((int) $id)
		);

		// Create the link
		$link = 'index.php?option=com_biblestudy&view=sermon&id=' . $id;

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('a.sef AS sef');
			$query->select('a.lang_code AS lang_code');
			$query->from('#__languages AS a');

			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang=' . $lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		elseif ($item = self::_findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Get Teacher Route
	 *
	 * @param   int $id  The route of the teacher item
	 *
	 * @return string
	 */
	public static function getTeacherRoute($id)
	{
		// Create the link
		$link = 'index.php?option=com_biblestudy&view=teacher&id=' . $id;

		return $link;
	}

	/**
	 * Get Series Route
	 *
	 * @param   int $id  ID
	 *
	 * @return string
	 */
	public static function getSeriesRoute($id)
	{
		// Create the link
		$link = 'index.php?option=com_biblestudy&view=seriesdisplay&id=' . $id;

		return $link;
	}

	/**
	 * Find Item
	 *
	 * @param   string $needles  ?
	 *
	 * @return mixed
	 */
	protected static function _findItem($needles = null)
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component = JComponentHelper::getComponent('com_content');
			$items     = $menus->getItems('component_id', $component->id);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$view]))
					{
						self::$lookup[$view] = array();
					}
					if (isset($item->query['id']))
					{
						self::$lookup[$view][$item->query['id']] = $item->id;
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$view][(int) $id]))
						{
							return self::$lookup[$view][(int) $id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();

			if ($active && $active->component == 'com_content')
			{
				return $active->id;
			}
		}

		return false;
	}

	/**
	 * @param    string $url     URL of website
	 * @param    string $scheme  Scheme that need to lead with.
	 *
	 * @return string  The fixed URL
	 */
	public static function addScheme($url, $scheme = 'http://')
	{
		if (parse_url($url, PHP_URL_SCHEME) === null)
		{
			return $scheme . $url;
		}

		return $url;
	}

}
