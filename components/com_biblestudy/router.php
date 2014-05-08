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
 * BibleStudy Build Route
 *
 * @param   array &$query  Info to Query
 *
 * @return string
 */
function biblestudyBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['view']))
	{
		if ($query['view'] == 'mediafile')
		{
			return $segments;
		}
		if ($query['view'] == 'message')
		{
			return $segments;
		}
		if ($query['view'] == 'comment')
		{
			return $segments;
		}
		if ($query['view'] == 'comments')
		{
			return $segments;
		}
		$segments[] = $query['view'];
		unset($query['view']);
	}

	if (isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	}

	if (isset($query['t']))
	{
		$segments[] = $query['t'];
		unset($query['t']);
	}

	return $segments;
}

/**
 * BibleStudy Parse Route
 *
 * @param   array $segments  Parse Route Info
 *
 * @return object
 */
function biblestudyParseRoute($segments)
{
	$vars = array();

	// Count route segments
	$count = count($segments);


	if ($count == 3)
	{
		$vars['view'] = $segments[0];
		$vars['id']   = (int) $segments[$count - 2];
		$vars['t']    = $segments[$count - 1];

		return $vars;
	}
	elseif ($count == 2)
	{
		$vars['view'] = $segments[0];
		$vars['t']    = $segments[$count - 1];

		return $vars;
	}
	else
	{
		$vars['view'] = $segments[0];

		return $vars;
	}
}
