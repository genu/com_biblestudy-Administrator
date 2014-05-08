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
class JFormFieldElementOptions extends JFormFieldList
{

    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Elementoptions';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options[] = JHtml::_('select.option', '0', JText::_('JBS_CMN_NONE'));
        $options[] = JHtml::_('select.option', '1', JText::_('JBS_TPL_PARAGRAPH'));
        $options[] = JHtml::_('select.option', '2', JText::_('JBS_TPL_HEADER1'));
        $options[] = JHtml::_('select.option', '3', JText::_('JBS_TPL_HEADER2'));
        $options[] = JHtml::_('select.option', '4', JText::_('JBS_TPL_HEADER3'));
        $options[] = JHtml::_('select.option', '5', JText::_('JBS_TPL_HEADER4'));
        $options[] = JHtml::_('select.option', '6', JText::_('JBS_TPL_HEADER5'));
        $options[] = JHtml::_('select.option', '7', JText::_('JBS_TPL_BLOCKQUOTE'));
        $options   = array_merge(parent::getOptions(), $options);

        return $options;
    }

}