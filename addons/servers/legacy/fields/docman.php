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
 * Virtuemart Category List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.4
 */
class JFormFieldDocman extends JFormFieldList
{

	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'Docman';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 */
	protected function getOptions()
	{
		/*

		*/
		// Check to see if Docman is installed
		jimport('joomla.filesystem.folder');

		if (!JFolder::exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_docman'))
		{
			return JText::_('JBS_CMN_DOCMAN_NOT_INSTALLED');
		}

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('dm.slug, dm.docman_document_id, dm.title');
		$query->from('#__docman_documents AS dm');
		$query->order('dm.docman_document_id DESC');
		$db->setQuery((string) $query);
		$docs    = $db->loadObjectList();
		$options = array();

		if ($docs)
		{
			$options[] = JHtml::_('select.option', '-1', JTEXT::_('JBS_MED_SELECT_DOCMAN'));

			foreach ($docs as $doc)
			{
				$options[] = JHtml::_('select.option', $doc->slug, $doc->title);
			}
		}
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
