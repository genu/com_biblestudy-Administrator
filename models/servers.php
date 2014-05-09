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
jimport('joomla.filesystem.folder');

/**
 * Servers model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelServers extends JModelList
{

    /**
     * A reverse lookup of the Endpoint id to Endpoint name
     *
     * @var     array
     * @since   8.1.0
     */
    protected $rlu_id = array();

    /**
     * A reverse lookup of the Endpoint type to Endpoint name
     *
     * @var     array
     * @sine    8.1.0
     */
    protected $rlu_type = array();

    /**
     * Method to get the reverse lookup of the server_id to server_name
     *
     * @return  array
     * @since   8.1.0
     */
    public function getIdToNameReverseLookup() {
        if(empty($this->rlu_id)) {
            $_rlu = array();
            foreach($this->getItems() as $server) {
                $_rlu[$server->id] = $server->server_name;
            }
            $this->rlu_id = $_rlu;

        }
        return $this->rlu_id;
    }

    /**
     * Method to get the reverse lookup of the Endpoint type to Endpoint name
     *
     * @return  array   Array of reverse lookup
     * @since   8.1.0
     */
    public function getTypeReverseLookup() {
        if(empty($this->rlu_type)) {
            $this->getServerOptions();
        }
        return $this->rlu_type;
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
	 * @since   7.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Adjust the context to support modal layouts.
		$input  = new JInput;
		$layout = $input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		parent::populateState('server.server_name', 'DESC');
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   7.0.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'server.id, server.published, server.server_name'));
		$query->from('#__bsms_servers AS server');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('server.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(server.published = 0 OR server.published = 1)');
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

    /**
     * Get a list of available endpoints
     *
     * @return  array|bool   Array of available endpoints options grouped by type or false if there aren't any
     * @since   8.1.0
     */
    public function getServerOptions() {

        $options = array();

        // Path to endpoints
        $path = JPATH_ADMINISTRATOR.'/components/com_biblestudy/addons/servers';

        if(JFolder::exists($path))
            $servers = JFolder::folders($path);
        else
            return false;

        foreach($servers as $server) {
            $file = $path.'/'.$server.'/'.$server.'.xml';

            if(is_file($file)) {
                if($xml = simplexml_load_file($file)) {
                    //Create the reverse lookup for Endpoint type to Endpoint name
                    $this->rlu_type[$server] = (string)$xml->name;

                    $o = new JObject();
                    $o->type = (string)$xml['type'];
                    $o->name = (string)$server;
                    $o->image_url = JURI::base().'/components/com_biblestudy/addons/servers/'.$server.'/'.$server.'.png';
                    $o->title = (string)$xml->name;
                    $o->description = (string)$xml->description;
                    $o->path = $path.'/'.$server.'/';

                    $options[$o->type][] = $o;
                    unset($xml);
                }
            }
        }
        return $options;
    }

}
