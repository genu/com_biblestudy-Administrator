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
class JFormFieldSpanOptions extends JFormFieldList
{

    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Spanoptions';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options[] = JHtml::_('select.option', '1', 1);
        $options[] = JHtml::_('select.option', '2', 2);
        $options[] = JHtml::_('select.option', '3', 3);
        $options[] = JHtml::_('select.option', '4', 4);
        $options[] = JHtml::_('select.option', '5', 5);
        $options[] = JHtml::_('select.option', '6', 6);
        $options[] = JHtml::_('select.option', '7', 7);
        $options[] = JHtml::_('select.option', '8', 8);
        $options[] = JHtml::_('select.option', '9', 9);
        $options[] = JHtml::_('select.option', '10', 10);
        $options[] = JHtml::_('select.option', '11', 11);
        $options[] = JHtml::_('select.option', '12', 12);
        $options   = array_merge(parent::getOptions(), $options);

        return $options;
    }

}