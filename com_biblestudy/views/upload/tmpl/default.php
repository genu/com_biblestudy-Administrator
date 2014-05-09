<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die();

?>
<div id="mediamu_wrapper">
	<div id="uploader_content">
		<?php echo $this->loadTemplate('uploader'); ?>
	</div>

	<div id="filebroswer_content">
		<?php echo $this->loadTemplate('navigator'); ?>
	</div>
</div>

