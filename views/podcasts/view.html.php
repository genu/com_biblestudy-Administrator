<?php

/**
 * JView html
 *
 * @package    BibleStudy
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Podcasts
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewPodcasts extends JViewLegacy
{

	/**
	 * Items
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var array
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var object
	 */
	protected $state;

	/**
	 * Can Do
	 *
	 * @var object
	 */
	public $canDo;

	/** @var  array Filter Levels */
	public $f_levels;

	/** @var  array Side Bar */
	public $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'podcast');
		$this->addToolbar();

		// Levels filter.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));

		$this->f_levels = $options;

		if (BIBLESTUDY_CHECKREL)
		{
			$this->sidebar = JHtmlSidebar::render();
		}
		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('JBS_CMN_PODCASTS'), 'podcast.png');

		if ($this->canDo->get('core.create'))
		{
			JToolBarHelper::addNew('podcast.add');
		}
		if ($this->canDo->get('core.edit'))
		{
			JToolBarHelper::editList('podcast.edit');
		}
		if ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publishList('podcasts.publish');
			JToolBarHelper::unpublishList('podcasts.unpublish');
			JToolBarHelper::archiveList('podcasts.archive', 'JTOOLBAR_ARCHIVE');
		}
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
        {
            JToolBarHelper::deleteList('', 'podcasts.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
		elseif ($this->canDo->get('core.delete'))
		{
			JToolBarHelper::trash('podcasts.trash');
		}
		if ($this->canDo->get('core.create'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('writeXMLFile', 'xml.png', 'JBS_PDC_WRITE_XML_FILES', 'JBS_PDC_WRITE_XML_FILES', false, false);
		}
		if (BIBLESTUDY_CHECKREL)
		{
			JHtmlSidebar::setAction('index.php?option=com_biblestudy&view=series');

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
			);

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_ACCESS'), 'filter_access',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
			);

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_LANGUAGE'), 'filter_language',
				JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
			);
		}
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_PODCASTS'));
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'podcast.title'     => JText::_('JBS_CMN_PODCAST'),
			'podcast.published' => JText::_('JSTATUS'),
			'podcast.language'  => JText::_('JGRID_HEADING_LANGUAGE'),
			'podcast.id'        => JText::_('JGRID_HEADING_ID')
		);
	}
}
