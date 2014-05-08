<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if (BIBLESTUDY_CHECKREL)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}
else
{
	JHtml::_('behavior.tooltip');
}
JHtml::_('behavior.multiselect');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;

$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=mimetypes'); ?>" method="post" name="adminForm"
      id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label for="filter_search"
					       class="element-invisible"><?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?></label>
					<input type="text" name="filter_search"
					       placeholder="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"
					       id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					       title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
				</div>
				<div class="btn-group pull-left hidden-phone">
					<button class="btn tip hasTooltip" type="submit"
					        title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
							class="icon-search"></i></button>
					<button class="btn tip hasTooltip" type="button"
					        onclick="document.id('filter_search').value='';this.form.submit();"
					        title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit"
					       class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable"
					       class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
					<select name="directionTable" id="directionTable" class="input-medium"
					        onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option
							value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JBS_CMN_ASCENDING'); ?></option>
						<option
							value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JBS_CMN_DESCENDING'); ?></option>
					</select>
				</div>
				<div class="btn-group pull-right">
					<label for="sortTable"
					       class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
					</select>
				</div>
				<?php if (!BIBLESTUDY_CHECKREL): ?>
					<div class="clearfix"></div>
					<div class="btn-group pull-right">
						<label for="filter_published" id="filter_published"
						       class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
						<select name="filter_published" class="input-medium" onchange="this.form.submit()">
							<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
							<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
						</select>
					</div>
					<div class="btn-group pull-right">
						<label for="filter_language" id="filter_language"
						       class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
						<select name="filter_language" class="input-medium" onchange="this.form.submit()">
							<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
							<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
						</select>
					</div>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>

			<table class="table table-striped adminlist" id="mimetypes">
				<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value=""
						       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'mimetype.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JBS_MMT_MIME_TEXT', 'mimetype.mimetext', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JBS_CMN_MIME_TYPE', 'mimetype.mimetype', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'mimetype.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($this->items as $i => $item) :
					$link       = JRoute::_('index.php?option=com_biblestudy&task=mimetype.edit&id=' . (int) $item->id);
					$canCreate  = $user->authorise('core.create');
					$canEdit    = $user->authorise('core.edit', 'com_biblestudy.mimetype.' . $item->id);
					$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.mimetype.' . $item->id);
					$canChange  = $user->authorise('core.edit.state', 'com_biblestudy.mimetype.' . $item->id);
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">

						<td class="center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->published, $i, 'folders.', $canChange, 'cb', '', ''); ?>
							</div>
						</td>
						<td class="nowrap has-context">
							<div class="pull-left">

								<?php if ($canEdit || $canEditOwn) : ?>
									<a href="<?php echo $link; ?>"><?php echo $item->mimetext; ?></a>
								<?php else : ?>
									<span title="<?php echo $item->mimetext; ?>"><?php echo $item->mimetext; ?></span>
								<?php endif; ?>
							</div>
							<div class="pull-left">
								<?php
								if (BIBLESTUDY_CHECKREL)
								{
									// Create dropdown items
									JHtml::_('dropdown.edit', $item->id, 'folder.');
									JHtml::_('dropdown.divider');
									if ($item->published) :
										JHtml::_('dropdown.unpublish', 'cb' . $i, 'folders.');
									else :
										JHtml::_('dropdown.publish', 'cb' . $i, 'folders.');
									endif;

									JHtml::_('dropdown.divider');

									if ($archived) :
										JHtml::_('dropdown.unarchive', 'cb' . $i, 'folders.');
									else :
										JHtml::_('dropdown.archive', 'cb' . $i, 'folders.');
									endif;

									if ($trashed) :
										JHtml::_('dropdown.untrash', 'cb' . $i, 'folders.');
									else :
										JHtml::_('dropdown.trash', 'cb' . $i, 'folders.');
									endif;

									// Render dropdown list
									echo JHtml::_('dropdown.render');
								}
								?>
							</div>
						</td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->mimetype); ?>
						</td>
						<td class="center hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php echo $this->pagination->getListFooter(); ?>
			<?php // Load the batch processing form. ?>
			<?php // Echo $this->loadTemplate('batch'); ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
