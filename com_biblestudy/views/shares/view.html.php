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
 * View class for a list of Shares
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewShares extends JViewLegacy
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
	 * @var object
	 */
	protected $state;

	/**
	 * Can Do
	 *
	 * @var object
	 */
	protected $canDo;

	/** @var  array Filter Levels */
	protected $f_levels;

	/** @var  object Side Bar */
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
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'share');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

			return false;
		}

		// Preprocess the list of items to find ordering divisions.
		// TODO: Complete the ordering stuff with nested sets
		foreach ($this->items as &$item)
		{
			$item->order_up = true;
			$item->order_dn = true;
		}

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
	 * Add the page title and toolbar
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();
		JToolBarHelper::title(JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'), 'social.png');

		if ($this->canDo->get('core.create'))
		{
			JToolBarHelper::addNew('share.add');
		}
		if ($this->canDo->get('core.edit'))
		{
			JToolBarHelper::editList('share.edit');
		}
		if ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publishList('shares.publish');
			JToolBarHelper::unpublishList('shares.unpublish');
			JToolBarHelper::archiveList('shares.archive', 'JTOOLBAR_ARCHIVE');
		}
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
        {
            JToolBarHelper::deleteList('', 'shares.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
		elseif ($this->canDo->get('core.delete'))
		{
			JToolBarHelper::trash('shares.trash');
		}
		if (BIBLESTUDY_CHECKREL)
		{
			JHtmlSidebar::setAction('index.php?option=com_biblestudy&view=shares');

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
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
		$document->setTitle(JText::_('JBS_TITLE_SOCIAL_NETWORKING_LINKS'));
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
			'podcast.title'  => JText::_('JBS_CMN_PODCAST'),
			'share.publish'  => JText::_('JSTATUS'),
			'share.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'share.name'     => JText::_('JBS_SHR_SOCIAL_NETWORK'),
			'share.id'       => JText::_('JGRID_HEADING_ID')
		);
	}
}
