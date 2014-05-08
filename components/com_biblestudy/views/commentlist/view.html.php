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
 * View class for CommentList extends Comments
 *
 * @property mixed canDo
 * @property array f_levels
 * @property mixed sidebar
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewCommentlist extends JViewLegacy
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
	 * @var array
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app              = JFactory::getApplication();
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');

			return;
		}
		$this->canDo = JBSMBibleStudyHelper::getActions('', 'comments');

		// Check permissions to enter studies
		if (!$this->canDo->get('core.edit'))
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
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
			if (BIBLESTUDY_CHECKREL)
			{
				$this->sidebar = JHtmlSidebar::render();
			}
		}

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return  void
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_COMMENTS'));
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
			'comment.full_name' => JText::_('JBS_CMT_FULL_NAME'),
			'comment.published' => JText::_('JSTATUS'),
			'study.studytitle'  => JText::_('JBS_CMN_TITLE'),
			'comment.language'  => JText::_('JGRID_HEADING_LANGUAGE'),
			'access_level'      => JText::_('JGRID_HEADING_ACCESS'),
			'comment.id'        => JText::_('JGRID_HEADING_ID')
		);
	}

}
