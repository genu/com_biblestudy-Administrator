<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * BibleStudy Install Script
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class Com_BiblestudyInstallerScript
{

	/**
	 * The release value to be displayed and check against throughout this file.
	 *
	 * @var string
	 */
	private $_release = '8.1.0';

	/**
	 * Find minimum required joomla version for this extension.
	 * It will be read from the version attribute (install tag) in the manifest file
	 *
	 * @var string
	 */
	private $_minimum_joomla_release = '2.5.6';

	/**
	 * Find minimum required PHP version for this extension.
	 * It will be read from the version attribute (install tag) in the manifest file
	 *
	 * @var string
	 */
	private $_minimum_php = '5.3.1';

	/**
	 * The component's name
	 *
	 * @var string
	 * */
	protected $biblestudy_extension = 'com_biblestudy';

	/**
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 *
	 * @param   string         $type   Type of install
	 * @param   JInstallerFile $parent Where it is coming from
	 *
	 * @return boolean
	 */
	public function preflight($type, $parent)
	{
		// This component does not work with Joomla releases prior to 2.5
		// abort if the current Joomla release is older

		// Extract the version number from the manifest. This will overwrite the 1.0 value set above
		/** @noinspection PhpUndefinedMethodInspection */
		$this->_release = $parent->get("manifest")->version;

		// Start DB factory
		$db = JFactory::getDBO();

		// Set the #__schemas version_id to the correct number so the update will occur if out of sequence.
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where('name LIKE ' . $db->q('%com_biblestudy%'));
		$db->setQuery($query);
		$extensionid = $db->loadResult();

		if ($extensionid)
		{
			$query = $db->getQuery(true);
			$query->select('version_id')
				->from('#__schemas')
				->where('extension_id = ' . $db->quote($extensionid));
			$db->setQuery($query);
			$jbsversion = $db->loadResult();

			if ($jbsversion == '20100101')
			{
				$query = $db->getQuery(true);
				$query->update('#__schemas')
					->set('version_id = ' . $db->q('7.0.0'))
					->where('extension_id = ' . $db->quote($extensionid));
				$db->setQuery($query);
				$db->execute();
			}
		}

		if ($type == 'install')
		{
			// Copy the css file over to another location
			$src = JPATH_SITE . '/components/com_biblestudy/assets/css/biblestudy.css';

			if (JFile::exists($src))
			{
				JFile::copy($src, JPATH_SITE . '/tmp/biblestudy.css');
			}
		}
		$install_good = version_compare(PHP_VERSION, $this->_minimum_php, '<');

		if (!$install_good)
		{
			$install_good = version_compare(JVERSION, $this->_minimum_joomla_release, 'ge');
		}
		else
		{
			JFactory::$application->enqueueMessage('Your host needs to use PHP ' . $this->_minimum_php . ' or higher to run Joomla Bible Study');
			$install_good = false;
		}

		// Only allow to install on minimum Joomla! version
		return $install_good;
	}

	/**
	 * Install
	 *
	 * @param   JInstallerFile $parent Where call is coming from
	 *
	 * @return  void
	 */
	public function install($parent)
	{
		$db     = JFactory::getDBO();
		$buffer = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/sql/install-defaults.sql');

		if ($buffer === false)
		{
			die('No install-defaults.sql file');
		}

		// Create an array of queries from the sql file
		$queries = $db->splitSql($buffer);

		foreach ($queries as $querie)
		{
			$querie = trim($querie);

			if ($querie != '' && $querie{0} != '#')
			{
				$db->setQuery($querie);
				$db->execute();
			}
		}
		require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/biblestudy.install.special.php';
		$fresh = new JBSMFreshInstall;

		if (!$fresh->installCSS())
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JBS_INS_FAILURE'), 'error');
		}

	}

	/**
	 * Uninstall
	 *
	 * @param   JInstallerFile $parent Where call is coming from
	 *
	 * @return   void
	 */
	public function uninstall($parent)
	{
		// Need to load JBSMDbHelper for script
		JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');
		$dbhelper    = new JBSMDbHelper;
		$drop_result = '';

		if ($dbhelper->checkIfTable('#__bsms_admin'))
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__bsms_admin')
				->where('id = 1');
			$db->setQuery($query);
			$adminsettings = $db->loadObject();

			$drop_tables = $adminsettings->drop_tables;

			if ($drop_tables > 0)
			{
				// We must remove the assets manually each time
				$db    = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select('id')
					->from('#__assets')
					->where('name = ' . $db->q($this->biblestudy_extension));
				$db->setQuery($query);
				$parent_id = $db->loadResult();
				$query     = $db->getQuery(true);

				if ($parent_id != '0')
				{
					$query->delete()
						->from('#__assets')
						->where('parent_id = ' . $db->q($parent_id))
						->where('name != ' . $db->q('root.1'));
					$db->setQuery($query);
					$db->execute();
				}

				$query = $db->getQuery(true);
				$query->delete()
					->from('#__assets')
					->where('name LIKE ' . $db->q($this->biblestudy_extension))
					->where('name != ' . $db->q('root.1'));
				$db->setQuery($query);
				$db->execute();
				$buffer = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/sql/uninstall-dbtables.sql');

				// Graceful exit and rollback if read not successful
				if ($buffer === false)
				{
					die('no uninstall-dbtables.sql');
				}
				$queries = $db->splitSql($buffer);

				foreach ($queries as $querie)
				{
					$querie = trim($querie);

					if ($querie != '' && $querie{0} != '#' && $querie != '`')
					{
						$db->setQuery($querie);
						$db->execute();
					}
				}
			}
		}
		else
		{
			$drop_result = '<h3>' . JText::_('JBS_INS_NO_DATABASE_REMOVED') . '</h3>';
		}
		echo '<h2>' . JText::_('JBS_INS_UNINSTALLED') . ' ' . $this->_release . '</h2> <div>' . $drop_result . '</div>';
	}

	/**
	 * Update
	 *
	 * @param   JInstallerFile $parent Where call is coming from
	 *
	 * @return   void
	 */
	public function update($parent)
	{
		$this->fixMenus();
		$this->fixImagePaths();
		$this->fixemptyaccess();
		$this->fixemptylanguage();
		JLoader::register('JBSM800Update', JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/updates/8.0.0.php');
		$update800 = new JBSM800Update;
		$update800->update800();
	}

	/**
	 * Post Flight
	 *
	 * @param   string         $type   Type of install
	 * @param   JInstallerFile $parent Where it is coming from
	 *
	 * @return   void
	 */
	public function postflight($type, $parent)
	{
		// Set the #__schemas version_id to the correct number for error from 7.0.0
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where('name LIKE' . $db->q('%com_biblestudy%'));
		$db->setQuery($query);
		$extensionid = $db->loadResult();

		if ($extensionid)
		{
			$query = $db->getQuery(true);
			$query->select('version_id')
				->from('#__schemas')
				->where('extension_id = ' . $db->q($extensionid));
			$db->setQuery($query);
			$jbsversion = $db->loadResult();

			if ($jbsversion == '20100101')
			{
				$query = $db->getQuery(true);
				$query->update('#__schemas')
					->set('version_id = ' . $db->q($this->_release))
					->where('extension_id = ' . $db->q($extensionid));
				$db->setQuery($query);
				$db->execute();
			}
		}

		// Set initial values for component parameters
		$params['my_param0'] = 'Component version ' . $this->_release;
		$params['my_param1'] = 'Start';
		$params['my_param2'] = '1';
		$this->setParams($params);

		// Set install state
		$subquery = '{"release":"' . $this->_release . '","jbsparent":"' .
			$parent . '","jbstype":"' . $type . '","jbsname":"com_biblestudy"}';
		$query1   = $db->getQuery(true);
		$query1->update('#__bsms_admin')
			->set('installstate = ' . $db->q($subquery))
			->where('id = 1');
		$db->setQuery($query1);
		$db->execute();

		// An redirect to a new location after the install is completed.
		$parent->getParent()->set('redirect_url', JURI::base() . 'index.php?option=com_biblestudy');
	}

	/**
	 * Get a variable from the manifest file (actually, from the manifest cache).
	 *
	 * @param   string $name Name of param
	 *
	 * @return string
	 */
	public function getParam($name)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('manifest_cache')
			->from('#_extensions')
			->where('name = ' . $db->q($this->biblestudy_extension));
		$db->setQuery($query);
		$manifest = json_decode($db->loadResult(), true);

		return $manifest[$name];
	}

	/**
	 * sets parameter values in the component's row of the extension table
	 *
	 * @param   array $param_array Array of params to set.
	 *
	 * @return   void
	 */
	public function setParams($param_array)
	{
		if (count($param_array) > 0)
		{
			// Read the existing component value(s)
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('params')
				->from('#__extensions')
				->where('name = ' . $db->q($this->biblestudy_extension));
			$db->setQuery($query);
			$params = json_decode($db->loadResult(), true);

			// Add the new variable(s) to the existing one(s)
			foreach ($param_array as $name => $value)
			{
				$params[(string) $name] = (string) $value;
			}

			// Store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params);
			$query        = $db->getQuery(true);
			$query->update('#__extensions')
				->set('params = ' . $db->q($paramsString))
				->where('name = ' . $db->q($this->biblestudy_extension));
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Remove Old Files and Folders
	 *
	 * @since 7.1.0
	 *
	 * @return   void
	 */
	public function deleteUnexistingFiles()
	{
		$files = array(
			'/media/com_biblestudy/css/biblestudy.css.dist',
			'/images/textfile24.png',
			'/components/com_biblestudy/biblestudy.css',
			'/components/com_biblestudy/class.biblestudydownload.php',
			'/components/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
			'/administrator/components/com_biblestudy/Snoopy.class.php',
			'/administrator/components/com_biblestudy/admin.biblestudy.php',
			'/components/com_biblestudy/helpers/updatesef.php',
			'/components/com_biblestudy/helpers/image.php',
			'/components/com_biblestudy/helpers/helper.php',
			'/components/com_biblestudy/views/messages/tmpl/modal16.php',
			'/components/com_biblestudy/controllers/teacherlist.php',
			'/components/com_biblestudy/controllers/teacherdisplay.php',
			'/components/com_biblestudy/controllers/studydetails.php',
			'/components/com_biblestudy/controllers/studieslist.php',
			'/components/com_biblestudy/controllers/serieslist.php',
			'/components/com_biblestudy/controllers/seriesdetail.php',
			'/components/com_biblestudy/models/teacherlist.php',
			'/components/com_biblestudy/models/teacherdisplay.php',
			'/components/com_biblestudy/models/studydetails.php',
			'/components/com_biblestudy/models/studieslist.php',
			'/components/com_biblestudy/models/seriesdetail.php',
			'/components/com_biblestudy/models/serieslist.php',
			'/components/com_biblestudy/views/mediafile/tmpl/form.php',
			'/components/com_biblestudy/views/mediafile/tmpl/form.xml',
			'/language/en-GB/en-GB.com_biblestudy.ini',
			'/language/cs-CZ/cs-CZ.com_biblestudy.ini',
			'/language/de-DE/de-DE.com_biblestudy.ini',
			'/language/es-ES/es-ES.com_biblestudy.ini',
			'/language/hu-HU/hu-HU.com_biblestudy.ini',
			'/language/nl-NL/nl-NL.com_biblestudy.ini',
			'/language/no-NO/no-NO.com_biblestudy.ini',
			'/language/en-GB/en-GB.mod_biblestudy.ini',
			'/language/en-GB/en-GB.mod_biblestudy.sys.ini',
			'/administrator/components/com_biblestudy/install/biblestudy.assets.php',
			'/administrator/components/com_biblestudy/install/sql/jbs7.0.0.sql',
			'/administrator/components/com_biblestudy/install/sql/updates/mysql/20100101.sql',
			'/administrator/components/com_biblestudy/lib/biblestudy.podcast.class.php',
			'/administrator/components/com_biblestudy/controllers/commentsedit.php',
			'/administrator/components/com_biblestudy/controllers/commentslist.php',
			'/administrator/components/com_biblestudy/controllers/cssedit.php',
			'/administrator/components/com_biblestudy/controllers/folderslist.php',
			'/administrator/components/com_biblestudy/controllers/foldersedit.php',
			'/administrator/components/com_biblestudy/controllers/locationslist.php',
			'/administrator/components/com_biblestudy/controllers/locationsedit.php',
			'/administrator/components/com_biblestudy/controllers/mediaedit.php',
			'/administrator/components/com_biblestudy/controllers/mediafilesedit.php',
			'/administrator/components/com_biblestudy/controllers/mediafileslist.php',
			'/administrator/components/com_biblestudy/controllers/medialist.php',
			'/administrator/components/com_biblestudy/controllers/messagetypelist.php',
			'/administrator/components/com_biblestudy/controllers/messagetypeedit.php',
			'/administrator/components/com_biblestudy/controllers/mimetypelist.php',
			'/administrator/components/com_biblestudy/controllers/mimetypeedit.php',
			'/administrator/components/com_biblestudy/controllers/podcastlist.php',
			'/administrator/components/com_biblestudy/controllers/podcastedit.php',
			'/administrator/components/com_biblestudy/controllers/serieslist.php',
			'/administrator/components/com_biblestudy/controllers/seriesedit.php',
			'/administrator/components/com_biblestudy/controllers/serverslist.php',
			'/administrator/components/com_biblestudy/controllers/serversedit.php',
			'/administrator/components/com_biblestudy/controllers/sharelist.php',
			'/administrator/components/com_biblestudy/controllers/shareedit.php',
			'/administrator/components/com_biblestudy/controllers/studieslist.php',
			'/administrator/components/com_biblestudy/controllers/studiesedit.php',
			'/administrator/components/com_biblestudy/controllers/teacherlist.php',
			'/administrator/components/com_biblestudy/controllers/teacheredit.php',
			'/administrator/components/com_biblestudy/controllers/templateedit.php',
			'/administrator/components/com_biblestudy/controllers/templateslist.php',
			'/administrator/components/com_biblestudy/controllers/topicslist.php',
			'/administrator/components/com_biblestudy/controllers/topicsedit.php',
			'/administrator/components/com_biblestudy/models/forms/commentsedit.xml',
			'/administrator/components/com_biblestudy/models/forms/foldersedit.xml',
			'/administrator/components/com_biblestudy/models/forms/locationsedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediaedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediafilesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/messagetypeedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mimetypeedit.xml',
			'/administrator/components/com_biblestudy/models/forms/podcastedit.xml',
			'/administrator/components/com_biblestudy/models/forms/seriesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/serversedit.xml',
			'/administrator/components/com_biblestudy/models/forms/shareedit.xml',
			'/administrator/components/com_biblestudy/models/forms/studiesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/teacheredit.xml',
			'/administrator/components/com_biblestudy/models/forms/templateedit.xml',
			'/administrator/components/com_biblestudy/models/forms/topicsedit.xml',
			'/administrator/components/com_biblestudy/models/episodelist.php',
			'/administrator/components/com_biblestudy/models/commentsedit.php',
			'/administrator/components/com_biblestudy/models/commentslist.php',
			'/administrator/components/com_biblestudy/models/cssedit.php',
			'/administrator/components/com_biblestudy/models/folderslist.php',
			'/administrator/components/com_biblestudy/models/foldersedit.php',
			'/administrator/components/com_biblestudy/models/locationslist.php',
			'/administrator/components/com_biblestudy/models/locationsedit.php',
			'/administrator/components/com_biblestudy/models/mediaedit.php',
			'/administrator/components/com_biblestudy/models/mediafilesedit.php',
			'/administrator/components/com_biblestudy/models/mediafileslist.php',
			'/administrator/components/com_biblestudy/models/medialist.php',
			'/administrator/components/com_biblestudy/models/messagetypelist.php',
			'/administrator/components/com_biblestudy/models/messagetypeedit.php',
			'/administrator/components/com_biblestudy/models/mimetypelist.php',
			'/administrator/components/com_biblestudy/models/mimetypeedit.php',
			'/administrator/components/com_biblestudy/models/podcastlist.php',
			'/administrator/components/com_biblestudy/models/podcastedit.php',
			'/administrator/components/com_biblestudy/models/serieslist.php',
			'/administrator/components/com_biblestudy/models/seriesedit.php',
			'/administrator/components/com_biblestudy/models/serverslist.php',
			'/administrator/components/com_biblestudy/models/serversedit.php',
			'/administrator/components/com_biblestudy/models/sharelist.php',
			'/administrator/components/com_biblestudy/models/shareedit.php',
			'/administrator/components/com_biblestudy/models/studieslist.php',
			'/administrator/components/com_biblestudy/models/studiesedit.php',
			'/administrator/components/com_biblestudy/models/teacherlist.php',
			'/administrator/components/com_biblestudy/models/teacheredit.php',
			'/administrator/components/com_biblestudy/models/templateedit.php',
			'/administrator/components/com_biblestudy/models/templateslist.php',
			'/administrator/components/com_biblestudy/models/topicslist.php',
			'/administrator/components/com_biblestudy/models/topicsedit.php',
			'/administrator/components/com_biblestudy/tables/biblestudy.php',
			'/administrator/components/com_biblestudy/tables/booksedit.php',
			'/administrator/components/com_biblestudy/tables/commentsedit.php',
			'/administrator/components/com_biblestudy/tables/foldersedit.php',
			'/administrator/components/com_biblestudy/tables/locationsedit.php',
			'/administrator/components/com_biblestudy/tables/mediaedit.php',
			'/administrator/components/com_biblestudy/tables/mediafilesedit.php',
			'/administrator/components/com_biblestudy/tables/messagetypeedit.php',
			'/administrator/components/com_biblestudy/tables/mimetypeedit.php',
			'/administrator/components/com_biblestudy/tables/podcastedit.php',
			'/administrator/components/com_biblestudy/tables/seriesedit.php',
			'/administrator/components/com_biblestudy/tables/serversedit.php',
			'/administrator/components/com_biblestudy/tables/shareedit.php',
			'/administrator/components/com_biblestudy/tables/studiesedit.php',
			'/administrator/components/com_biblestudy/tables/teacheredit.php',
			'/administrator/components/com_biblestudy/tables/topicsedit.php',
			'/administrator/components/com_biblestudy/tables/templateedit.php',
			'/administrator/components/com_biblestudy/helpers/version.php',
			'/administrator/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
			'/administrator/language/cs-CZ/cs-CZ.com_biblestudy.ini',
			'/administrator/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini',
			'/administrator/language/de-DE/de-DE.com_biblestudy.ini',
			'/administrator/language/de-DE/de-DE.com_biblestudy.sys.ini',
			'/administrator/language/es-ES/es-ES.com_biblestudy.ini',
			'/administrator/language/es-ES/es-ES.com_biblestudy.sys.ini',
			'/administrator/language/hu-HU/hu-HU.com_biblestudy.ini',
			'/administrator/language/hu-HU/hu-HU.com_biblestudy.sys.ini',
			'/administrator/language/nl-NL/nl-NL.com_biblestudy.ini',
			'/administrator/language/nl-NL/no-NO.com_biblestudy.ini',
			'/administrator/language/no-NO/no-NO.com_biblestudy.sys.ini',
			// JBSM 8.0.0
			// Site:
			'/components/com_biblestudy/controllers\commentsedit.php',
			'/components/com_biblestudy/controllers\commentslist.php',
			'/components/com_biblestudy/controllers\mediafile.php',
			'/components/com_biblestudy/controllers\mediafiles.php',
			'/components/com_biblestudy/controllers\message.php',
			'/components/com_biblestudy/controllers\messages.php',
			'/components/com_biblestudy/helpers\book.php',
			'/components/com_biblestudy/helpers\date.php',
			'/components/com_biblestudy/helpers\duration.php',
			'/components/com_biblestudy/helpers\editlink.php',
			'/components/com_biblestudy/helpers\editlisting.php',
			'/components/com_biblestudy/helpers\filepath.php',
			'/components/com_biblestudy/helpers\filesize.php',
			'/components/com_biblestudy/helpers\header.php',
			'/components/com_biblestudy/helpers\listing.php',
			'/components/com_biblestudy/helpers\location.php',
			'/components/com_biblestudy/helpers\mediatable.php',
			'/components/com_biblestudy/helpers\messagetype.php',
			'/components/com_biblestudy/helpers\params.php',
			'/components/com_biblestudy/helpers\passage.php',
			'/components/com_biblestudy/helpers\scripture.php',
			'/components/com_biblestudy/helpers\share.php',
			'/components/com_biblestudy/helpers\store.php',
			'/components/com_biblestudy/helpers\textlink.php',
			'/components/com_biblestudy/helpers\title.php',
			'/components/com_biblestudy/helpers\toolbar.php',
			'/components/com_biblestudy/helpers\topics.php',
			'/components/com_biblestudy/helpers\year.php',
			'/components/com_biblestudy/lib\biblestudy.admin.class.php',
			'/components/com_biblestudy/lib\biblestudy.defines.php',
			'/components/com_biblestudy/lib\biblestudy.stats.class.php',
			'/components/com_biblestudy/models\forms\commentsedit.xml',
			'/components/com_biblestudy/models\commentsedit.php',
			'/components/com_biblestudy/models\commentslist.php',
			'/components/com_biblestudy/models\mediafile.php',
			'/components/com_biblestudy/models\mediafiles.php',
			'/components/com_biblestudy/models\message.php',
			'/components/com_biblestudy/models\messages.php',
			// Admin:
			'/administrator/components/com_biblestudy/controllers\ajax.php',
			'/administrator/components/com_biblestudy/helpers\cleanurl.php',
			'/administrator/components/com_biblestudy/helpers\toolbar.php',
			'/administrator/components/com_biblestudy/lib\biblestudy.admin.class.php',
			'/administrator/components/com_biblestudy/lib\biblestudy.migrate.php',
			'/administrator/components/com_biblestudy/migration\biblestudy.611.upgrade.php',
			'/administrator/components/com_biblestudy/migration\biblestudy.612.upgrade.php',
			'/administrator/components/com_biblestudy/migration\biblestudy.613.upgrade.php',
			'/administrator/components/com_biblestudy/migration\biblestudy.614.upgrade.php',
			'/administrator/components/com_biblestudy/migration\biblestudy.622.upgrade.php',
			'/administrator/components/com_biblestudy/migration\biblestudy.623.upgrade.php',
			'/administrator/components/com_biblestudy/migration\biblestudy.700.upgrade.php',
			'/administrator/components/com_biblestudy/migration\update701.php',
			'/administrator/components/com_biblestudy/models\fields\locationordering.php',
			'/administrator/components/com_biblestudy/models\fields\mediaordering.php',
			'/administrator/components/com_biblestudy/models\fields\messagetypeordering.php',
			'/administrator/components/com_biblestudy/models\fields\shareordering.php',
			'/administrator/components/com_biblestudy/models\fields\teacherordering.php',
			'/administrator/components/com_biblestudy/views\admin\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\admin\tmpl\form_assets.php',
			'/administrator/components/com_biblestudy/views\admin\tmpl\form_backup.php',
			'/administrator/components/com_biblestudy/views\admin\tmpl\form_database.php',
			'/administrator/components/com_biblestudy/views\admin\tmpl\form_migrate.php',
			'/administrator/components/com_biblestudy/views\comment\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\folder\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\folders\tmpl\index.html',
			'/administrator/components/com_biblestudy/views\location\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\mediaimage\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\message\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\messagetype\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\mimetype\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\podcast\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\serie\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\server\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\server\tmpl\index.html',
			'/administrator/components/com_biblestudy/views\share\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\teacher\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\template\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\templatecode\tmpl\form.php',
			'/administrator/components/com_biblestudy/views\topic\tmpl\form.php',
		);

		$folders = array(
			'/components/com_biblestudy/assets',
			'/components/com_biblestudy/images',
			'/components/com_biblestudy/views/teacherlist',
			'/components/com_biblestudy/views/teacherdisplay',
			'/components/com_biblestudy/views/studieslist',
			'/components/com_biblestudy/views/studydetails',
			'/components/com_biblestudy/views/serieslist',
			'/components/com_biblestudy/views/seriesdetail',
			'/administrator/media',
			'/administrator/components/com_biblestudy/assets',
			'/administrator/components/com_biblestudy/images',
			'/administrator/components/com_biblestudy/css',
			'/administrator/components/com_biblestudy/js',
			'/administrator/components/com_biblestudy/views/commentsedit',
			'/administrator/components/com_biblestudy/views/commentslist',
			'/administrator/components/com_biblestudy/views/cssedit',
			'/administrator/components/com_biblestudy/views/folderslist',
			'/administrator/components/com_biblestudy/views/foldersedit',
			'/administrator/components/com_biblestudy/views/locationslist',
			'/administrator/components/com_biblestudy/views/locationsedit',
			'/administrator/components/com_biblestudy/views/mediaedit',
			'/administrator/components/com_biblestudy/views/mediafilesedit',
			'/administrator/components/com_biblestudy/views/mediafileslist',
			'/administrator/components/com_biblestudy/views/medialist',
			'/administrator/components/com_biblestudy/views/messagetypelist',
			'/administrator/components/com_biblestudy/views/messagetypeedit',
			'/administrator/components/com_biblestudy/views/mimetypelist',
			'/administrator/components/com_biblestudy/views/mimetypeedit',
			'/administrator/components/com_biblestudy/views/podcastlist',
			'/administrator/components/com_biblestudy/views/podcastedit',
			'/administrator/components/com_biblestudy/views/serieslist',
			'/administrator/components/com_biblestudy/views/seriesedit',
			'/administrator/components/com_biblestudy/views/serverslist',
			'/administrator/components/com_biblestudy/views/serversedit',
			'/administrator/components/com_biblestudy/views/sharelist',
			'/administrator/components/com_biblestudy/views/shareedit',
			'/administrator/components/com_biblestudy/views/studieslist',
			'/administrator/components/com_biblestudy/views/studiesedit',
			'/administrator/components/com_biblestudy/views/teacherlist',
			'/administrator/components/com_biblestudy/views/teacheredit',
			'/administrator/components/com_biblestudy/views/templateedit',
			'/administrator/components/com_biblestudy/views/templateslist',
			'/administrator/components/com_biblestudy/views/topicslist',
			'/administrator/components/com_biblestudy/views/topicsedit',
			// JBSM 8.0.0
			'/components/com_biblestudy/views/messages',
			'/components/com_biblestudy/views/message',
			'/components/com_biblestudy/views/mediafiles',
			'/components/com_biblestudy/views/mediafile',
			'/components/com_biblestudy/views/commentslist',
			'/components/com_biblestudy/views/commentsedit',
		);

		foreach ($files as $file)
		{
			if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br />';
			}
		}

		foreach ($folders as $folder)
		{
			if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br />';
			}
		}
	}

	/**
	 * Fix Menus
	 *
	 * @since 7.1.0
	 *
	 * @return   void
	 */
	public function fixMenus()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__menu')
			->where($db->qn('menutype') . ' != ' . $db->q('main'))
			->where($db->qn('link') . ' LIKE ' . $db->q('%com_biblestudy%'));
		$db->setQuery($query);
		$menus = $db->loadObjectList();

		foreach ($menus AS $menu)
		{
			$menu->link = str_replace('teacherlist', 'teachers', $menu->link);
			$menu->link = str_replace('teacherdisplay', 'teacher', $menu->link);
			$menu->link = str_replace('studydetails', 'sermon', $menu->link);
			$menu->link = str_replace('serieslist', 'seriesdisplays', $menu->link);
			$menu->link = str_replace('seriesdetail', 'seriesdisplay', $menu->link);
			$menu->link = str_replace('studieslist', 'sermons', $menu->link);
			$query      = $db->getQuery(true);
			$query->update('#__menu')
				->set("link = " . $db->q($menu->link))
				->where('id = ' . $db->q($menu->id));
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Fix Image paths
	 *
	 * @since 7.1.0
	 *
	 * @return   void
	 */
	public function fixImagePaths()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_media');
		$db->setQuery($query);
		$images = $db->loadObjectList();

		foreach ($images as $image)
		{
			if (!empty($image->media_image_path))
			{
				$image->media_image_path = str_replace('components', 'media', $image->media_image_path);
				$query                   = $db->getQuery(true);
				$query->update('#__bsms_media')
					->set("media_image_path = " . $db->q($image->media_image_path))
					->where('id = ' . $db->q($image->id));
				$db->setQuery($query);
				$db->execute();
			}
		}
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_share');
		$db->setQuery($query);
		$datas = $db->loadObjectList();

		foreach ($datas as $data)
		{
			// Need to adjust the params and write back
			$registry = new JRegistry;
			$registry->loadString($data->params);
			$params     = $registry;
			$shareimage = $params->get('shareimage');
			$shareimage = str_replace('components', 'media', $shareimage);
			$params->set('shareimage', $shareimage);

			// Now write the params back into the $table array and store.
			$data->params = (string) $params->toString();
			$qeery        = $db->getQuery(true);
			$qeery->update('#__bsms_share')
				->set('params =' . $db->q($data->params))
				->where('id = ' . $db->q($data->id));
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Function to find empty language field and set them to "*"
	 *
	 * @since 7.1.0
	 *
	 * @return   void
	 */
	public function fixemptylanguage()
	{
		// Tables to fix
		$tables = array(
			array('table' => '#__bsms_comments'),
			array('table' => '#__bsms_mediafiles'),
			array('table' => '#__bsms_series'),
			array('table' => '#__bsms_studies'),
			array('table' => '#__bsms_teachers'),
		);

		// Correct blank records
		foreach ($tables as $table)
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->update($table['table'])
				->set('language = ' . $db->q('*'))
				->where('language = ' . $db->q(''));
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Function to Find empty access in the db and set them to Public
	 *
	 * @since 7.1.0
	 *
	 * @return   void
	 */
	public function fixemptyaccess()
	{
		// Tables to fix
		$tables = array(
			array('table' => '#__bsms_admin'),
			array('table' => '#__bsms_mediafiles'),
			array('table' => '#__bsms_message_type'),
			array('table' => '#__bsms_mimetype'),
			array('table' => '#__bsms_podcast'),
			array('table' => '#__bsms_series'),
			array('table' => '#__bsms_servers'),
			array('table' => '#__bsms_share'),
			array('table' => '#__bsms_studies'),
			array('table' => '#__bsms_studytopics'),
			array('table' => '#__bsms_teachers'),
			array('table' => '#__bsms_templates'),
			array('table' => '#__bsms_topics'),
		);

		// Get Public id
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__viewlevels')
			->where('title = ' . $db->q('Public'));
		$db->setQuery($query);
		$id = $db->loadResult();

		// Correct blank or not set records
		foreach ($tables as $table)
		{
			$query = $db->getQuery(true);
			$query->update($table['table'])
				->set('access = ' . $id)
				->where("(access = " . $db->q('0') . " or access = " . $db->q('') . ")");
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Old Update URL's
	 *
	 * @return array
	 */
	public function rmoldurl()
	{

		$urls = array("http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=3&dummy=extension.xml /extension.xml",
			"http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=14&dummy=extension.xml /extension.xml, ",
			"http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=13&dummy=extension.xml /extension.xml, ",
			"http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=4&dummy=extension.xml /extension.xml, ",
			"http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=8&dummy=extension.xml /extension.xml ",
			"http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=5&dummy=extension.xml /extension.xml");

		return $urls;
	}

}
