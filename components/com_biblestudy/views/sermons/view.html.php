<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package        BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View for Sermons class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewSermons extends JViewLegacy
{

	/**
	 * Items
	 *
	 * @var object
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var object
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var JRegistry
	 */
	protected $state;

	/**
	 * Page Links
	 *
	 * @var string
	 */
	protected $pagelinks;

	/**
	 * Limit Box
	 *
	 * @var string
	 */
	protected $limitbox;

	/**
	 * Admin Info
	 *
	 * @var JObject
	 */
	protected $admin;

	/**
	 * Admin Params
	 *
	 * @var JRegistry
	 */
	protected $admin_params;

	/**
	 * Params
	 *
	 * @var JRegistry
	 */
	protected $params;

	/**
	 * Study
	 *
	 * @var object
	 */
	protected $study;

	/**
	 * Subscribe
	 *
	 * @var string
	 */
	protected $subscribe;

	/**
	 * Series
	 *
	 * @var string
	 */
	protected $series;

	/**
	 * Teachers
	 *
	 * @var string
	 */
	protected $teachers;

	/**
	 * Message Types
	 *
	 * @var string
	 */
	protected $messageTypes;

	/**
	 * Years
	 *
	 * @var string
	 */
	protected $years;

	/**
	 * Locations
	 *
	 * @var string
	 */
	protected $locations;

	/**
	 * Topics
	 *
	 * @var string
	 */
	protected $topics;

	/**
	 * Orders
	 *
	 * @var string
	 */
	protected $orders;

	/**
	 * Books
	 *
	 * @var string
	 */
	protected $books;

	/**
	 * Templates
	 *
	 * @var object
	 */
	protected $template;

	/**
	 * Order
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Topic
	 *
	 * @var array
	 */
	protected $topic;

	/**
	 * Main
	 *
	 * @var object
	 */
	protected $main;

	/**
	 * Page
	 *
	 * @var object
	 */
	protected $page;

	/**
	 * Request Url
	 *
	 * @var string
	 */
	protected $request_url;

	/**
	 * Document
	 *
	 * @var object
	 */
	public $document;

	/**
	 * Limit Start
	 *
	 * @var int
	 */
	protected $limitstart;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$input      = new JInput;
		$limitstart = $input->get('limitstart', '', 'int');
		$input->set('start', $limitstart);
		$this->state = $this->get('State');

		$items = $this->get('Items');

		$this->limitstart = $input->get('start', '', 'int');
		$pagination       = $this->get('Pagination');
		$pagelinks        = $pagination->getPagesLinks();

		if ($pagelinks !== '')
		{
			$this->pagelinks = $pagelinks;
		}

		$this->limitbox   = '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $pagination->getLimitBox() . '</span>';
		$this->pagination = $pagination;
		$this->admin      = JBSMParams::getAdmin();

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$params = $this->state->params;

		$this->admin_params = $this->admin->params;
		$images             = new JBSMImages;
		$this->main         = $images->mainStudyImage();

		// Only load pagebuilder if the default template is NOT being used
		if ($params->get('useexpert_list') > 0 && !$params->get('sermonstemplate'))
		{
			$page_builder = new JBSMPagebuilder;

			for ($i = 0, $n = count($items); $i < $n; $i++)
			{

				$item = & $items[$i];

				if ($item->access > 1 && !in_array($item->access, $groups))
				{
					unset($item);
				}
				else
				{
					$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

					$pelements        = $page_builder->buildPage($item, $params, $this->admin_params);
					$item->scripture1 = $pelements->scripture1;
					$item->scripture2 = $pelements->scripture2;
					$item->media      = $pelements->media;
					$item->duration   = $pelements->duration;
					$item->studydate  = $pelements->studydate;
					$item->topics     = $pelements->topics;

					if (isset($pelements->study_thumbnail))
					{
						$item->study_thumbnail = $pelements->study_thumbnail;
					}
					else
					{
						$item->study_thumbnail = null;
					}

					if (isset($pelements->series_thumbnail))
					{

						$item->series_thumbnail = $pelements->series_thumbnail;
					}
					else
					{
						$item->series_thumbnail = null;
					}
					$item->detailslink = $pelements->detailslink;

					if (!isset($item->studyintro))
					{
						$item->studyintro = '';
					}

					if (isset($pelements->secondary_reference))
					{
						$item->secondary_reference = $pelements->secondary_reference;
					}
					else
					{
						$item->secondary_reference = '';
					}
					if (isset($pelements->sdescription))
					{
						$item->sdescription = $pelements->sdescription;
					}
					else
					{
						$item->sdescription = '';
					}
				}

			}
		}
		// Get the podcast subscription
		$podcast         = new JBSMPodcastSubscribe;
		$this->subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

		JViewLegacy::loadHelper('image');

		$uri = new JUri;

		$filter_topic       = $this->state->get('filter.topic');
		$filter_book        = $this->state->get('filter.book');
		$filter_teacher     = $this->state->get('filter.teacher');
		$filter_series      = $this->state->get('filter.series');
		$filter_messagetype = $this->state->get('filter.messageType');
		$filter_year        = $this->state->get('filter.year');
		$filter_location    = $this->state->get('filter.location');
		$filter_orders      = $this->state->get('filter.orders');
		$filter_languages   = $this->state->get('filter.languages');

		$this->teachers     = $this->get('Teachers');
		$this->series       = $this->get('Series');
		$this->messageTypes = $this->get('MessageTypes');
		$this->years        = $this->get('Years');
		$this->locations    = $this->get('Locations');
		$this->topics       = $this->get('Topics');
		$this->orders       = $this->get('Orders');
		$this->books        = $this->get('Books');

		// This is the helper for scripture formatting
		JViewLegacy::loadHelper('scripture');

		// End scripture helper
		// Get the data for the drop down boxes
		$this->template   = $this->state->get('template');
		$this->pagination = $pagination;
		$this->order      = $this->orders;
		$this->topic      = $this->topics;

		// Get the template options for showing the dropdowns
		$teacher_menu1     = $params->get('teacher_id');
		$teacher_menu      = $teacher_menu1[0];
		$topic_menu1       = $params->get('topic_id');
		$topic_menu        = $topic_menu1[0];
		$book_menu1        = $params->get('booknumber');
		$book_menu         = $book_menu1[0];
		$location_menu1    = $params->get('locations');
		$location_menu     = $location_menu1[0];
		$series_menu1      = $params->get('series_id');
		$series_menu       = $series_menu1[0];
		$messagetype_menu1 = $params->get('messagetype');
		$messagetype_menu  = $messagetype_menu1[0];

		// Initialize the page
		$this->page = new stdClass;

		// Build drop down menus for search filters
		// Get the Popular stats
		$stats               = new JBSMStats;
		$this->page->popular = $stats->top_score_site();
		if ($params->get('show_popular') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddpopular'), 'item' => $this->page->popular);
		}

		// Get whether "Go" Button is used then turn off onchange if it is
		if ($params->get('use_go_button', 0) == 0)
		{
			$go = 'onchange="this.form.submit()"';
		}
		else
		{
			$go = null;
		}
		// Build go button
		$this->page->gobutton = '<input class="btn btn-primary" type="submit" value="' . JText::_('JBS_STY_GO_BUTTON') . '">';

		if ($params->get('use_go_button') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddgobutton'), 'item' => $this->page->gobutton);
		}

		// Build language drop down
		$used = JLanguageHelper::getLanguages();
		$lang = array();

		foreach ($used as $use)
		{
			$langtemp = array(
				'text'  => $use->title_native,
				'value' => $use->lang_code
			);
			$lang[]   = $langtemp;
		}
		$langdropdown[]        = JHTML::_('select.option', '0', JTEXT::_('JBS_SELECT_LANGUAGE'));
		$langdropdown          = array_merge($langdropdown, $lang);
		$this->page->languages = JHTML::_('select.genericlist', $langdropdown, 'filter_languages', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_languages"
		);
		if ($params->get('listlanguage') == 1)
		{
			$dropdowns[] = array('order' => $params->get('ddlanguage'), 'item' => $this->page->languages);
		}

		// Build the teacher dropdown
		$types[]              = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_TEACHER'));
		$types                = array_merge($types, $this->teachers);
		$this->page->teachers = JHTML::_('select.genericlist', $types, 'filter_teacher', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_teacher"
		);
		if (($params->get('show_teacher_search') > 0 && ($teacher_menu == -1)) || $params->get('show_teacher_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddteachers'), 'item' => $this->page->teachers);
		}

		// Build Series List for drop down menu
		$types3[]           = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_SERIES'));
		$types3             = array_merge($types3, $this->series);
		$this->page->series = JHTML::_('select.genericlist', $types3, 'filter_series', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_series"
		);
		if (($params->get('show_series_search') > 0 && ($series_menu == -1)) || $params->get('show_series_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddseries'), 'item' => $this->page->series);
		}

		// Build message types
		$types4[]                 = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_MESSAGE_TYPE'));
		$types4                   = array_merge($types4, $this->messageTypes);
		$this->page->messagetypes = JHTML::_('select.genericlist', $types4, 'filter_messagetype', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_messagetype"
		);
		if (($params->get('show_type_search') > 0 && ($messagetype_menu == -1)) || $params->get('show_type_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddmessagetype'), 'item' => $this->page->messagetypes);
		}
		// Build study years
		$years[]           = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_YEAR'));
		$years             = array_merge($years, $this->years);
		$this->page->years = JHTML::_('select.genericlist', $years, 'filter_year', 'class="inputbox"  ' . $go, 'value', 'text', "$filter_year");
		if ($params->get('show_year_search') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddyears'), 'item' => $this->page->years);
		}
		// Build locations
		$loc[]                 = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_LOCATION'));
		$loc                   = array_merge($loc, $this->locations);
		$this->page->locations = JHTML::_(
			'select.genericlist', $loc, 'filter_location', 'class="inputbox" size="1" '
			. $go, 'value', 'text', "$filter_location"
		);
		if (($params->get('show_locations_search') > 0 && ($location_menu == -1)) || $params->get('show_locations_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddlocations'), 'item' => $this->page->locations);
		}
		// Build Topics
		$top[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_TOPIC'));
		if ($top && $this->topics)
		{
			$top = array_merge($top, $this->topics);
		}
		$this->page->topics = JHTML::_('select.genericlist', $top, 'filter_topic', 'class="inputbox" ' . $go, 'value', 'text', "$filter_topic");
		if (($params->get('show_topic_search') > 0 && ($topic_menu == -1)) || $params->get('show_topic_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddtopics'), 'item' => $this->page->topics);
		}
		// Build Books
		$boo[]             = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_BOOK'));
		$boo               = array_merge($boo, $this->books);
		$this->page->books = JHTML::_('select.genericlist', $boo, 'filter_book', 'class="inputbox"  ' . $go, 'value', 'text', "$filter_book");
		if (($params->get('show_book_search') > 0 && $book_menu == -1) || $params->get('show_book_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddbooks'), 'item' => $this->page->books);
		}

		// Build order
		$ordervalues       = array(
			array(
				'value' => "DESC",
				'text'  => JText::_("JBS_CMN_DESCENDING")
			),
			array(
				'value' => "ASC",
				'text'  => JText::_("JBS_CMN_ASCENDING")
			)
		);
		$ord[]             = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_ORDER'));
		$ord               = array_merge($ord, $ordervalues);
		$this->page->order = JHTML::_('select.genericlist', $ord, 'filter_orders', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_orders");
		if ($params->get('show_order_search') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddorder'), 'item' => $this->page->order);
		}
		if ($params->get('show_pagination') == 1)
		{
			$this->page->limits = '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
			$dropdowns[]        = array('order' => '0', 'item' => $this->page->limits);
		}
		foreach ($dropdowns as $key => $value)
		{
			$dropdownmenus[] = $value;
		}
		asort($dropdownmenus);
		foreach ($dropdownmenus as $dmenus)
		{
			$this->page->dropdowns .= $dmenus['item'];
		}
		$this->items       = $items;
		$stringuri         = $uri->toString();
		$this->request_url = $stringuri;
		$this->params      = $params;

		$this->_prepareDocument();

		// Get the drop down menus

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		$app     = JFactory::getApplication();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JBS_CMN_MESSAGES_LIST'));
		}

		$title = $this->params->def('page_title', '');
		$title .= ' : ' . JText::_('JBS_CMN_MESSAGES_LIST');

		if ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

		// Prepare meta information (under development)
		if ($this->params->get('metakey'))
		{
			$this->document->setMetadata('keywords', $this->params->get('metakey'));
		}
		elseif (!$this->params->get('metakey') && $this->admin_params->get('metakey'))
		{
			$this->document->setMetadata('keywords', $this->admin_params->get('metakey'));
		}

		if ($this->params->get('metadesc'))
		{
			$this->document->setDescription($this->params->get('metadesc'));
		}
		elseif (!$this->params->get('metadesc') && $this->admin_params->get('metadesc'))
		{
			$this->document->setDescription($this->admin_params->get('metadesc'));
		}

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		if (BIBLESTUDY_CHECKREL)
		{
			JHtml::_('behavior.framework');
		}
		else
		{
			JHTML::_('behavior.mootools');
		}
		$css = $this->params->get('css');

		if ($css <= "-1")
		{
			$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
		}
		else
		{
			$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		}
		$this->document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');

		// Errors when using local swfobject.js file.  IE 6 doesn't work
		// Import Scripts
		$this->document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
		$this->document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery-noconflict.js');
		$this->document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
		$this->document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$this->document->addScript(JURI::base() . 'media/com_biblestudy/js/views/studieslist.js');
		$this->document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
		$this->document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-responsive.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-extended.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-responsive-min.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-min.css');

		// Styles from tooltip.css moved to css/biblestudy.css
		// Import Stylesheets
		$this->document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');
	}

}
