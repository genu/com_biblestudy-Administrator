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
 * Bible Study stats support class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JBSMStats
{

	/**
	 * Total plays of media files per study
	 *
	 * @param   int $id  Id number of study
	 *
	 * @return int Total plays form the media
	 */
	public static function total_plays($id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('sum(m.plays), m.study_id, m.published, s.id FROM #__bsms_mediafiles AS m')
			->leftJoin('#__bsms_studies AS s ON (m.study_id = s.id)')
			->where('m.study_id = ' . $db->q($id));
		$db->setQuery($query);
		$plays = $db->loadResult();

		return (int) $plays;
	}

	/**
	 * Total messages in Bible Study
	 *
	 * @param   string $start  ?
	 * @param   string $end    ?
	 *
	 * @return int Total Messages
	 */
	public static function get_total_messages($start = '', $end = '')
	{
		$db    = JFactory::getDBO();
		$where = array();

		if (!empty($start))
		{
			$where[] = 'time > UNIX_TIMESTAMP(\'' . $start . '\')';
		}
		if (!empty($end))
		{
			$where[] = 'time < UNIX_TIMESTAMP(\'' . $end . '\')';
		}
		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from('#__bsms_studies')
			->where('published =' . $db->q('1'));

		if (count($where) > 0)
		{
			$query->where(implode(' AND ', $where));
		}
		$db->setQuery($query);
		$results = $db->loadResult();

		return intval($results);
	}

	/**
	 * Total topics in Bible Study
	 *
	 * @param   string $start  ?
	 * @param   string $end    ?
	 *
	 * @return int  Total Topics
	 */
	public static function get_total_topics($start = '', $end = '')
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from('#__bsms_studies')
			->leftJoin('#__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)')
			->leftJoin('#__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)')
			->where('#__bsms_topics.published = ' . $db->q('1'));

		if (!empty($start))
		{
			$query->where('time > UNIX_TIMESTAMP(\'' . $start . '\')');
		}
		if (!empty($end))
		{
			$query->where('time < UNIX_TIMESTAMP(\'' . $end . '\')');
		}
		$db->setQuery($query);

		return intval($db->loadResult());
	}

	/**
	 * Get top studies
	 *
	 * @return array
	 */
	public static function get_top_studies()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__bsms_studies')
			->where('published = ' . $db->q('1'))
			->where('hits > ' . $db->q('0'))
			->order('hits desc');
		$db->setQuery($query, 0, 1);
		$results     = $db->loadObjectList();
		$top_studies = null;

		foreach ($results as $result)
		{
			$top_studies .= $result->hits . ' ' . JText::_('JBS_CMN_HITS') .
				' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $result->id . '">' .
				$result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
		}

		return $top_studies;
	}

	/**
	 * Total media files in Bible Study
	 *
	 * @return int
	 */
	public static function get_total_categories()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__bsms_mediafiles')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);

		return intval($db->loadResult());
	}

	/**
	 * Get top books
	 *
	 * @return object
	 *
	 * @deprecated Not used as of 8.0.0
	 */
	public static function get_top_books()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('booknumber, COUNT( hits ) AS totalmsg')
			->from('#__bsms_studies')
			->group('booknumber')
			->order('totalmsg DESC');
		$db->setQuery($query, 0, 5);
		$results = $db->loadObjectList();

		if (count($results) > 0)
		{
			$ids   = implode(',', $results);
			$query = $db->getQuery(true);
			$query
				->select('bookname')
				->from('#__bsms_books')
				->where('booknumber IN (' . $ids . ')')
				->order('booknumber');
			$db->setQuery($query);
			$names = $db->loadResult();
			$i     = 0;

			foreach ($results as $result)
			{
				$result->bookname = $names[$i++];
			}
		}
		else
		{
			$results = new stdClass;
		}

		return $results;
	}

	/**
	 * Total comments
	 *
	 * @return int
	 */
	public static function get_total_comments()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from('#__bsms_comments')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);

		return intval($db->loadResult());
	}

	/**
	 * Get top thirty days
	 *
	 * @return string
	 */
	public static function get_top_thirty_days()
	{
		$month      = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
		$last_month = date("Y-m-d 00:00:01", $month);
		$db         = JFactory::getDBO();
		$query      = $db->getQuery(true);
		$query
			->select('*')
			->from('#__bsms_studies')
			->where('published = ' . $db->q('1'))
			->where('hits > ' . $db->q('0'))
			->where('UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( ' . $db->q($last_month) . ' )')
			->order('hits desc');
		$db->setQuery($query, 0, 5);
		$results     = $db->loadObjectList();
		$top_studies = null;

		if (!$results)
		{
			$top_studies = JText::_('JBS_CPL_NO_INFORMATION');
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->hits . ' ' . JText::_('JBS_CMN_HITS') .
					' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $result->id . '">' .
					$result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
			}
		}

		return $top_studies;
	}

	/**
	 * Get Total Meida Files
	 *
	 * @return array Don't know
	 */
	public static function total_media_files()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from('#__bsms_mediafiles')
			->where('published = ' . 1);
		$db->setQuery($query);

		return intval($db->loadResult());
	}

	/**
	 * Get Top Downloads
	 *
	 * @return string List of links to the downloads
	 */
	public static function get_top_downloads()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select(
				'#__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished,' .
				'#__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate ')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id)')
			->where('#__bsms_mediafiles.published = 1 ')
			->where('downloads > 0')
			->order('downloads desc');

		$db->setQuery($query, 0, 5);
		$results     = $db->loadObjectList();
		$top_studies = null;

		foreach ($results as $result)
		{
			$top_studies .=
				$result->downloads . ' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;d=' .
				$result->sid . '">' . $result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) .
				'<br>';
		}

		return $top_studies;
	}

	/**
	 * Get Downloads ninety
	 *
	 * @return array list of download links
	 */
	public static function get_downloads_ninety()
	{
		$month     = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
		$lastmonth = date("Y-m-d 00:00:01", $month);
		$db        = JFactory::getDBO();
		$query     = $db->getQuery(true);
		$query
			->select(
				'#__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished,' .
				' #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate ')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id)')
			->where('#__bsms_mediafiles.published = ' . $db->q('1'))
			->where('downloads > ' . (int) $db->q('0'))
			->where('UNIX_TIMESTAMP(createdate) > UNIX_TIMESTAMP( ' . $db->q($lastmonth) . ' )')
			->order('downloads DESC');
		$db->setQuery($query, 0, 5);
		$results     = $db->loadObjectList();
		$top_studies = null;

		if (!$results)
		{
			$top_studies = JText::_('JBS_CPL_NO_INFORMATION');
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->downloads . ' ' . JText::_('JBS_CMN_HITS') .
					' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $result->sid . '">' .
					$result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) . '<br>';
			}
		}

		return $top_studies;
	}

	/**
	 * Total Downloads
	 *
	 * @return array
	 */
	public static function total_downloads()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('SUM(downloads)')
			->from('#__bsms_mediafiles')
			->where('published = ' . 1)
			->where('downloads > ' . 0);
		$db->setQuery($query);

		return intval($db->loadResult());
	}

	/**
	 * Top Score ???
	 *
	 * @return int number of scors
	 */
	public static function top_score()
	{
		$final        = array();
		$admin_params = JBSMParams::getAdmin();
		$format       = $admin_params->params->get('format_popular', '0');
		$db           = JFactory::getDBO();
		$query        = $db->getQuery(true);
		$query
			->select('study_id, sum(downloads + plays) as added ')
			->from('#__bsms_mediafiles')
			->where('published = ' . 1)
			->group('study_id')
            ->order('added DESC');
		$db->setQuery($query);
		$results = $db->loadAssocList();
        array_splice($results, 5);
		foreach ($results as $key=>$result)
		{
			$query = $db->getQuery(true);
			$query
				->select('#__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits,' .
				'#__bsms_studies.id, #__bsms_mediafiles.study_id from #__bsms_studies')
				->leftJoin('#__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)')
				->where('#__bsms_mediafiles.study_id = ' . (int) $result['study_id']);
			$db->setQuery($query);
			$hits = $db->loadObject();

			if ($format < 1)
			{
				$total = $result['added'] + $hits->hits;
			}
			else
			{
				$total = $result->added;
			}
			$link    = ' <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $hits->id . '">' .
				$hits->studytitle . '</a> ' . date('Y-m-d', strtotime($hits->studydate)) . '<br>';
			$final2  = array('total' => $total, 'link' => $link);
			$final[] = $final2;
		}
		rsort($final);
		array_splice($final, 5);
		$top_score_table = '';

		foreach ($final as $value)
		{
			foreach ($value as $scores)
			{
				$top_score_table .= $scores;
			}
		}

		return $top_score_table;
	}

	/**
	 * Returns a System of Player
	 *
	 * @return string
	 */
	public static function players()
	{
		$count_no_player       = 0;
		$count_global_player   = 0;
		$count_internal_player = 0;
		$count_av_player       = 0;
		$count_legacy_player   = 0;
		$count_embed_code      = 0;
		$db                    = JFactory::getDBO();
		$query                 = $db->getQuery(true);
		$query
			->select('player')
			->from('#__bsms_mediafiles')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);
		$plays         = $db->loadObjectList();
		$total_players = count($plays);

		foreach ($plays as $player)
		{
			switch ($player->player)
			{
				case 0:
					$count_no_player++;
					break;
				case '100':
					$count_global_player++;
					break;
				case '1':
					$count_internal_player++;
					break;
				case '3':
					$count_av_player++;
					break;
				case '7':
					$count_legacy_player++;
					break;
				case '8':
					$count_embed_code++;
					break;
			}
		}

		$media_players = '<br /><strong>' . JText::_('JBS_CMN_TOTAL_PLAYERS') . ': ' . $total_players . '</strong>' .
			'<br /><strong>' . JText::_('JBS_CMN_INTERNAL_PLAYER') . ': </strong>' . $count_internal_player .
			'<br /><strong><a href="http://extensions.joomla.org/extensions/multimedia/multimedia-players/video-players-a-gallery/11572" target="blank">' .
			JText::_('JBS_CMN_AVPLUGIN') . '</a>: </strong>' . $count_av_player . '<br /><strong>' .
			JText::_('JBS_CMN_LEGACY_PLAYER') . ': </strong>' . $count_legacy_player . '<br /><strong>' .
			JText::_('JBS_CMN_NO_PLAYER_TREATED_DIRECT') . ': </strong>' . $count_no_player . '<br /><strong>' .
			JText::_('JBS_CMN_GLOBAL_SETTINGS') . ': </strong>' . $count_global_player . '<br /><strong>' .
			JText::_('JBS_CMN_EMBED_CODE') . ': </strong>' . $count_embed_code;

		return $media_players;
	}

	/**
	 * Popups for media files
	 *
	 * @return string
	 */
	public static function popups()
	{
		$no_player    = 0;
		$pop_count    = 0;
		$inline_count = 0;
		$global_count = 0;
		$db           = JFactory::getDBO();
		$query        = $db->getQuery(true);
		$query
			->select('popup')
			->from('#__bsms_mediafiles')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);
		$popups            = $db->loadObjectList();
		$total_media_files = count($popups);

		foreach ($popups as $popup)
		{
			switch ($popup->popup)
			{
				case 0:
					$no_player++;
					break;
				case 1:
					$pop_count++;
					break;
				case 2:
					$inline_count++;
					break;
				case 3:
					$global_count++;
					break;
			}
		}

		$popups = '<br /><strong>' . JText::_('JBS_CMN_TOTAL_MEDIAFILES') . ': ' . $total_media_files . '</strong>' .
			'<br /><strong>' . JText::_('JBS_CMN_INLINE') . ': </strong>' . $inline_count . '<br /><strong>' .
			JText::_('JBS_CMN_POPUP') . ': </strong>' . $pop_count . '<br /><strong>' .
			JText::_('JBS_CMN_GLOBAL_SETTINGS') . ': </strong>' . $global_count . '<br /><strong>' .
			JText::_('JBS_CMN_NO_OPTION_TREATED_GLOBAL') . ': </strong>' . $no_player;

		return $popups;
	}

	/**
	 * Top Score Site
	 *
	 * @return string
	 */
	public function top_score_site()
	{
		$input = new JInput;
		$t     = $input->get('t', 1, 'int');

		$admin        = JBSMParams::getAdmin();
		$admin_params = $admin->params;
		$limit        = $admin_params->get('popular_limit', '25');
		$top          = '<select onchange="goTo()" id="urlList"><option value="">' .
			JText::_('JBS_CMN_SELECT_POPULAR_STUDY') . '</option>';
		$final        = array();

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('m.study_id, s.access, s.published AS spub, sum(m.downloads + m.plays) as added')
			->from('#__bsms_mediafiles AS m')
			->leftJoin('#__bsms_studies AS s ON (m.study_id = s.id)')
			->where('m.published = 1 GROUP BY m.study_id');
		$db->setQuery($query);
		$format = $admin_params->get('format_popular', '0');

		$items = $db->loadObjectList();

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$count  = count($items);

		for ($i = 0; $i < $count; $i++)
		{
			if ($items[$i]->access > 1)
			{
				if (!in_array($items[$i]->access, $groups))
				{
					unset($items[$i]);
				}
			}
		}

		foreach ($items as $result)
		{
			$query = $db->getQuery(true);
			$query->select('#__bsms_studies.studydate, #__bsms_studies.studytitle,
							#__bsms_studies.hits, #__bsms_studies.id, #__bsms_mediafiles.study_id')
				->from('#__bsms_studies')
				->leftJoin('#__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)')
				->where('#__bsms_mediafiles.study_id = ' . (int) $result->study_id);
			$db->setQuery($query);
			$hits = $db->loadObject();
            if (!$hits){return false;}
			if (!$hits->studytitle)
			{
				$name = $hits->id;
			}
			else
			{
				$name = $hits->studytitle;
			}
			if ($format < 1)
			{
				$total = $result->added + $hits->hits;
			}
			else
			{
				$total = $result->added;
			}
			$selectvalue   = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $hits->id . '&t=' . $t);
			$selectdisplay = $name . ' - ' . JText::_('JBS_CMN_SCORE') . ': ' . $total;
			$final2        = array(
				'score'   => $total,
				'select'  => $selectvalue,
				'display' => $selectdisplay
			);
			$final[]       = $final2;
		}
		rsort($final);
		array_splice($final, $limit);

		foreach ($final as $topscore)
		{

			$top .= '<option value="' . $topscore['select'] . '">' . $topscore['display'] . '</option>';
		}
		$top .= '</select>';

		return $top;
	}

}
