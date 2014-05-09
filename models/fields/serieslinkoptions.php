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

// Import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Books List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.4
 */
class JFormFieldSeriesLinkoptions extends JFormFieldList
{

    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'SeriesLinkoptions';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options[] = JHtml::_('select.option', '0', JText::_('JBS_TPL_NO_LINK'));
        $options[] = JHtml::_('select.option', '1', JText::_('JBS_TPL_LINK_TO_DETAILS'));
        $options   = array_merge(parent::getOptions(), $options);

        return $options;
    }

}
