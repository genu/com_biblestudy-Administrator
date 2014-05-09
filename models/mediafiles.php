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

jimport('joomla.application.component.modellist');

/**
 * MediaFiles model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelMediafiles extends JModelList
{
	/**
	 * Number of Deletions
	 *
	 * @var int
	 */
	private $_deletes;

	/**
	 * Constructor.
	 *
	 * @param   array $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'mediafile.id',
				'published', 'mediafile.published',
				'ordering', 'mediafile.ordering',
				'studytitle', 'study.studytitle',
				'createdate', 'mediafile.createdate',
				'language', 'mediafile.language'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get Deletes
	 *
	 * @return object
	 */
	public function getDeletes()
	{
		if (empty($this->_deletes))
		{
			$query          = 'SELECT allow_deletes'
				. ' FROM #__bsms_admin'
				. ' WHERE id = 1';
			$this->_deletes = $this->_getList($query);
		}

		return $this->_deletes;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering   An optional ordering field.
	 * @param   string $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   7.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{

		// Initialise variables.
		$app     = JFactory::getApplication();
		$session = JFactory::getSession();

		// Adjust the context to support modal layouts.
		$input  = new JInput;
		$layout = $input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$filename = $this->getUserStateFromRequest($this->context . '.filter.filename', 'filter_filename');
		$this->setState('filter.filename', $filename);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$study = $this->getUserStateFromRequest($this->context . '.filter.study_id', 'filter_study_id');
		$this->setState('filter.study_id', $study);

		$mediaType = $this->getUserStateFromRequest($this->context . '.filter.mediaType', 'filter_mediaType');
		$this->setState('filter.mediaType', $mediaType);

		$mediaYears = $this->getUserStateFromRequest($this->context . '.filter.mediaYears', 'filter_mediaYears');
		$this->setState('filter.mediaYears', $mediaYears);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$download = $this->getUserStateFromRequest($this->context . '.filter.download', 'filter_download', '');
		$this->setState('filter.download', $download);

		$player = $this->getUserStateFromRequest($this->context . '.filter.player', 'filter_player', '');
		$this->setState('filter.player', $player);

		$popup = $this->getUserStateFromRequest($this->context . '.filter.popup', 'filter_popup', '');
		$this->setState('filter.popup', $popup);

		parent::populateState('mediafile.createdate', 'DESC');
	}

	/**
	 * Builds a list of mediatypes (Used for the filter combo box)
	 *
	 * @return array Array of Objects
	 *
	 * @since 7.0
	 */
	public function getMediatypes()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('media.id AS value, media.media_text AS text');
		$query->from('#__bsms_media AS media');
		$query->join('INNER', '#__bsms_mediafiles AS mediafile ON mediafile.media_image = media.id');
		$query->group('media.id');
		$query->order('media.media_text');

		$db->setQuery($query->__toString());

		//return $db->loadObjectList();
	}

	/**
	 * Get Stored ID
	 *
	 * @param   string $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since 7.0
	 */
	protected function getStoreId($id = '')
	{

		// Compile the store id.
		$id .= ':' . $this->getState('filter.filename');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.study_id');
		$id .= ':' . $this->getState('filter.mediaType');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select', 'mediafile.id, mediafile.published, mediafile.ordering,
                        mediafile.createdate, mediafile.language, mediafile.study_id ')
		);

		$query->from($db->quoteName('#__bsms_mediafiles') . ' AS mediafile');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = mediafile.language');

		// Join over the studies
		$query->select('study.studytitle AS studytitle');
		$query->join('LEFT', '#__bsms_studies AS study ON study.id = mediafile.study_id');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = mediafile.access');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('mediafile.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(mediafile.published = 0 OR mediafile.published = 1)');
		}
		// Filter by access level.
		$access = $this->getState('filter.access');

		if ($access)
		{
			$query->where('mediafile.access = ' . (int) $access);
		}

		// Filter by study title
		$study = $this->getState('filter.study_id');

		if (!empty($study))
		{
			$query->where('study.id LIKE "%' . $study . '%"');
		}

		// Filter by media years
		$mediaYears = $this->getState('filter.mediaYears');

		if (!empty($mediaYears))
		{
			$query->where('YEAR(mediafile.createdate) = ' . (int) $mediaYears);
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get('list.ordering', 'ordering');
		$orderDirn = $this->state->get('list.direction', 'desc');

		// Sqlsrv change
		if ($orderCol == 'study_id')
		{
			$orderCol = 'mediafile.study_id';
		}
		if ($orderCol == 'ordering')
		{
			$orderCol = 'mediafile.study_id, mediafile.ordering';
		}
		if ($orderCol == 'published')
		{
			$orderCol = 'mediafile.published';
		}
		if ($orderCol == 'id')
		{
			$orderCol = 'mediafile.id';
		}
		if ($orderCol == 'mediafile.ordering')
		{
			$orderCol = 'mediafile.study_id ' . $orderDirn . ', mediafile.ordering';
		}
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

}
