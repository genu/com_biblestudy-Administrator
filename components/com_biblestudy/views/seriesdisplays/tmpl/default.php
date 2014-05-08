<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_serieslist') > 0)
{
	echo $this->loadTemplate('custom');
}
elseif ($this->params->get('seriesdisplaystemplate'))
{
	echo $this->loadTemplate($this->params->get('seriesdisplaystemplate'));
}
else
{
	echo $this->loadTemplate('main');
}
