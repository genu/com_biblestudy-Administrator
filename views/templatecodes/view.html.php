<?php

/**
 * JView html
 *
 * @package    BibleStudy
 * @copyright  (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * @since      7.1.0
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for TemplateCodes
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BiblestudyViewTemplatecodes extends JViewLegacy
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
	 * @var object
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
	protected $canDo;

	/**
	 * Filter Levels
	 *
	 * @var array
	 */
	public $f_levels;

	/**
	 * Side Bar
	 *
	 * @var string
	 */
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
		$this->canDo = JBSMBibleStudyHelper::getActions('', 'templatecode');
		$this->state = $this->get('State');
		$this->items = $this->get('Items');

		foreach ($this->items as $item)
		{
			switch ($item->type)
			{
				case 1:
					$item->typetext = JText::_('JBS_TPLCODE_SERMONLIST');
					break;
				case 2:
					$item->typetext = JText::_('JBS_TPLCODE_SERMON');
					break;
				case 3:
					$item->typetext = JText::_('JBS_TPLCODE_TEACHERS');
					break;
				case 4:
					$item->typetext = JText::_('JBS_TPLCODE_TEACHER');
					break;
				case 5:
					$item->typetext = JText::_('JBS_TPLCODE_SERIESDISPLAYS');
					break;
				case 6:
					$item->typetext = JText::_('JBS_TPLCODE_SERIESDISPLAY');
					break;
				case 7:
					$item->typetext = JText::_('JBS_TPLCODE_MODULE');
					break;
			}
		}
		$modelView        = $this->getModel();
		$this->pagination = $this->get('Pagination');

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

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();

			if (BIBLESTUDY_CHECKREL)
			{
				$this->sidebar = JHtmlSidebar::render();
			}
		}

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	protected function addToolbar()
	{

		JToolBarHelper::title(JText::_('JBS_TPLCODE_TPLCODES'), 'templates.png');

		if ($this->canDo->get('core.create'))
		{
			JToolBarHelper::addNew('templatecode.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolBarHelper::editList('templatecode.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publishList('templatecodes.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublishList('templatecodes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('templatecodes.archive', 'JTOOLBAR_ARCHIVE');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'templatecodes.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('templatecodes.trash');
			JToolBarHelper::divider();
		}
		if (BIBLESTUDY_CHECKREL)
		{
			JHtmlSidebar::setAction('index.php?option=com_biblestudy&view=templatecodes');

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
		$document->setTitle(JText::_('JBS_TITLE_TEMPLATECODES'));
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
			'study.studytitle'     => JText::_('JBS_CMN_STUDY_TITLE'),
			'mediatype.media_text' => JText::_('JBS_MED_MEDIA_TYPE'),
			'mediafile.filename'   => JText::_('JBS_MED_FILENAME'),
			'mediafile.ordering'   => JText::_('JGRID_HEADING_ORDERING'),
			'mediafile.published'  => JText::_('JSTATUS'),
			'mediafile.id'         => JText::_('JGRID_HEADING_ID')
		);
	}
}
