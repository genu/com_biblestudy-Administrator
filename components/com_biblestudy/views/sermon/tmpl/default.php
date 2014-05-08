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
?>
<div class="container-fluid"> <!-- This div is the container for the whole page --><?php

	if ($this->item->params->get('useexpert_details') > 0)
	{
		echo $this->loadTemplate('custom');
	}
	elseif ($this->params->get('sermontemplate'))
	{
		echo $this->loadTemplate($this->params->get('sermontemplate'));
	}
	else
	{
		echo $this->loadTemplate('main');
	}
	$show_comments = $this->item->params->get('show_comments');

	if ($show_comments >= 1)
	{
		$user           = JFactory::getUser();
		$groups         = $user->getAuthorisedViewLevels();
		$comment_access = $this->item->params->get('comment_access');

		if (in_array($show_comments, $groups))
		{
			//Determine what kind of comments component to use
			switch ($this->item->params->get('comments_type', 0))
			{
				case 0:
					//this should be using JBS comments only
					echo $this->loadTemplate('commentsform');
					break;

				case 1:
					//This is a just JComments
					$comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
					if (file_exists($comments))
					{
						require_once($comments);
						echo JComments::showComments($this->item->id, 'com_biblestudy', $this->item->studytitle);
					}
					break;

				case 2:
					//this is a combination of JBS and JComments
					$comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
					if (file_exists($comments))
					{
						require_once($comments);
						echo JComments::show($this->item->id, 'com_biblestudy', $this->item->studytitle);
					}
					echo $this->loadTemplate('commentsform');
					break;
			}

		}
	}
	echo $this->loadTemplate('footerlink');

	?>
</div><!--end of container fluid-->