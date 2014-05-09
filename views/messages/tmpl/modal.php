<?php
/**
 * Modal
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if (JFactory::getApplication()->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.tooltip');
$input = new JInput;
$function = $input->get('function', 'jSelectStudy', 'cmd');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=messages&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
	method="post" name="adminForm" id="adminForm">
	<fieldset id="filter clearfix">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search"
			       class="element-invisible"><?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?></label>
			<input type="text" name="filter_search" placeholder="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"
			       id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			       title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
		</div>
		<div class="btn-group pull-left hidden-phone">
			<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
					class="icon-search"></i></button>
			<button class="btn tip hasTooltip" type="button"
			        onclick="document.id('filter_search').value='';this.form.submit();"
			        title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
		</div>
		<div class="right">
			<select name="filter_book" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JBS_CMN_SELECT_BOOK'); ?></option>
				<?php echo JHtml::_('select.options', $this->books, 'value', 'text', $this->state->get('filter.book')); ?>
			</select>
			<select name="filter_teacher" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JBS_CMN_SELECT_TEACHER'); ?></option>
				<?php echo JHtml::_('select.options', $this->teachers, 'value', 'text', $this->state->get('filter.teacher')); ?>
			</select>
			<select name="filter_series" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JBS_CMN_SELECT_SERIES'); ?></option>
				<?php echo JHtml::_('select.options', $this->series, 'value', 'text', $this->state->get('filter.series')); ?>
			</select>
			<select name="filter_message_type" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JBS_CMN_SELECT_MESSAGE_TYPE'); ?></option>
				<?php echo JHtml::_('select.options', $this->messageTypes, 'value', 'text', $this->state->get('filter.messageType')); ?>
			</select>
			<select name="filter_year" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JBS_CMN_SELECT_YEAR'); ?></option>
				<?php echo JHtml::_('select.options', $this->years, 'value', 'text', $this->state->get('filter.year')); ?>
			</select>
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter._published'), true); ?>
			</select>
			<select name="filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
			</select>
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
		<tr>
			<th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_STUDY_DATE', 'study.studydate', $listDirn, $listOrder); ?>
			</th>
			<th width="8%">
				<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'study.published', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_SCRIPTURE', 'book.bookname', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_MESSAGE_TYPE', 'messageType.message_type', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
			</th>
			<th width="5%">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="12">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<?php
		foreach ($this->items as $i => $item) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<a class="pointer"
					   onclick="if(window.parent) window.parent.<?php echo $function; ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->studytitle)); ?>');">
						<?php echo $this->escape($item->studytitle); ?>
					</a>
				</td>
				<td class="center">
					<?php echo JHtml::_('date', $item->studydate, JText::_('DATE_FORMAT_LC4')); ?>
				</td>

				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'messages.', true, 'cb', '', ''); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->bookname) . ' ' . $this->escape($item->chapter_begin) . ':' . $this->escape($item->verse_begin); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->teachername); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->messageType); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->series_text); ?>
				</td>
				<td class="center">
					<?php if ($item->language == '*'): ?>
						<?php echo JText::alt('JALL', 'language'); ?>
					<?php else: ?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
