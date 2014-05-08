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

// Base this model on the backend version.
JLoader::register('BiblestudyModelMessages', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/messages.php');

/**
 * Model class for MessageList
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 */
class BiblestudyModelMessagelist extends BiblestudyModelMessages
{

}
