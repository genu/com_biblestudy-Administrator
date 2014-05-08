<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

/**
 * A helper to return buttons for podcast subscriptions
 *
 * @package  BibleStudy.Site
 * @since    7.1.0
 *
 */
class JBSMPodcastSubscribe
{

	/**
	 * Build Subscribe Table
	 *
	 * @param   string $introtext  Intro Text
	 *
	 * @return string
	 */
	public function buildSubscribeTable($introtext = 'Our Podcasts')
	{
		$podcasts = $this->getPodcasts();

		$subscribe = '';

		if ($podcasts)
		{

			$subscribe .= '<div class="podcastheader" ><h3>' . $introtext . '</h3></div>';
			$subscribe .= '<div class="prow">';

			foreach ($podcasts AS $podcast)
			{

				$podcastshow = $podcast->podcast_subscribe_show;

				if (!$podcastshow)
				{
					$podcastshow = 2;
				}

				switch ($podcastshow)
				{
					case 1:
						break;

					case 2:
						$subscribe .= '<div class="pcell"><h3>' . $podcast->title . '</h3><div class="clr padding-bottom-5"><hr /></div>';
						$subscribe .= $this->buildStandardPodcast($podcast);
						$subscribe .= '</div>';
						break;

					case 3:
						$subscribe .= '<div class="pcell"><h3>' . $podcast->title . '</h3><div class="clr padding-bottom-5"><hr /></div>';
						$subscribe .= $this->buildAlternatePodcast($podcast);
						$subscribe .= '</div>';
						break;

					case 4:
						$subscribe .= '<div class="pcell"><h3>' . $podcast->title . '</h3><div class="clr padding-bottom-5"><hr /></div><div class="fltlft">';
						$subscribe .= $this->buildStandardPodcast($podcast);
						$subscribe .= '</div><div class="fltlft">';
						$subscribe .= $this->buildAlternatePodcast($podcast);
						$subscribe .= '</div></div>';
						break;
				}
			}
			// End of row
			$subscribe .= '</div>';

			// Add a div around it all
			$subscribe = '<div class="podcastsubscribe">' . $subscribe . '</div>';
		}

		return $subscribe;
	}

	/**
	 * Build Standard Podcast
	 *
	 * @param   object $podcast  Podcast Info
	 *
	 * @return string
	 */
	public function buildStandardPodcast($podcast)
	{
		$subscribe = '';

		if (!empty($podcast->podcast_image_subscribe))
		{
			$image = $this->buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
			$link  = '<div class="image"><a href="' . JURI::base() . $podcast->filename . '">' . $image . '</a></div><div class="clr"></div>';
			$subscribe .= $link;
		}

		if (empty($podcast->podcast_subscribe_desc))
		{
			$name = $podcast->title;
		}
		else
		{
			$name = $podcast->podcast_subscribe_desc;
		}
		$subscribe .= '<div class="text"><a href="' . JURI::base() . $podcast->filename . '">' . $name . '</a></div>';

		return $subscribe;
	}

	/**
	 * Build Alternate Podcast
	 *
	 * @param   object $podcast  Podcast info
	 *
	 * @return string
	 */
	public function buildAlternatePodcast($podcast)
	{
		$subscribe = '';

		if (!empty($podcast->alternateimage))
		{
			$image = $this->buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
			$link  = '<div class="image"><a href="' . $podcast->alternatelink . '">' . $image . '</a></div><div class="clr"></div>';
			$subscribe .= $link;
		}
		$subscribe .= '<div class="text"><a href="' . $podcast->alternatelink . '">' . $podcast->alternatewords . '</a></div>';

		return $subscribe;
	}

	/**
	 * Get Podcasts
	 *
	 * @return object
	 */
	public function getPodcasts()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery('true');
		$query->select('*');
		$query->from('#__bsms_podcast as p');
		$query->where('p.published = 1');
		$db->setQuery($query);
		$podcasts = $db->loadObjectList();

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$count  = count($podcasts);

		for ($i = 0; $i < $count; $i++)
		{

			if ($podcasts[$i]->access > 1)
			{
				if (!in_array($podcasts[$i]->access, $groups))
				{
					unset($podcasts[$i]);
				}
			}
		}

		return $podcasts;
	}

	/**
	 * Build Podcast Image
	 *
	 * @param   array $podcastimagefromdb  Podcast image
	 * @param   array $words               Alt podcast image text
	 *
	 * @return string
	 */
	public function buildPodcastImage($podcastimagefromdb = null, $words = null)
	{
		$images       = new JBSMImages;
		$image        = $images->getMediaImage($podcastimagefromdb);
		$podcastimage = null;

		if ($image->path)
		{
			$podcastimage = '<img class="image" src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="'
				. $image->height . '" alt="' . $words . '" title="' . $words . '" />';
		}

		return $podcastimage;
	}

}
