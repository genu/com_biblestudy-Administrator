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

jimport('joomla.application.component.modeladmin');

/**
 * Serie admin model
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelSerie extends JModelAdmin
{

	/**
	 * @var  string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer $value     The new category.
	 * @param   array   $pks       An array of row IDs.
	 * @param   array   $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since    11.1
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		$app   = JFactory::getApplication();
		$table = $this->getTable();
		$i     = 0;

		// Check that the user has create permission for the component
		$extension = $app->input->get('option', '');
		$user      = JFactory::getUser();

		if (!$user->authorise('core.create', $extension))
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'), 'error');

			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$table->reset();

			// Check that the row actually exists
			if (!$table->load($pk))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$app->enqueueMessage($error, 'error');

					return false;
				}
				else
				{
					// Not fatal error
					$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$data         = $this->generateNewTitle('', $table->alias, $table->title);
			$table->title = $data['0'];
			$table->alias = $data['1'];

			// Reset the ID because we are making a copy
			$table->id = 0;

			// Check the row.
			if (!$table->check())
			{
				$app->enqueueMessage($table->getError(), 'error');

				return false;
			}

			// Store the row.
			if (!$table->store())
			{
				$app->enqueueMessage($table->getError(), 'error');

				return false;
			}

			// Get the new item ID
			$newId = $table->get('id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object $record  A record object.
     *
     * @return    boolean    True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since    1.6
     */
    protected function canDelete($record)
    {
        if (!empty($record->id))
        {
            if ($record->published != -2)
            {
                return false;
            }
            $user = JFactory::getUser();

            return $user->authorise('core.delete', 'com_biblestudy.serie.' . (int) $record->id);
        }

        return false;
    }

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since    1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing article.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_biblestudy.serie.' . (int) $record->id);
		}

		// Default to component settings if serie known.
		return parent::canEditState($record);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable $table  A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->series_text = htmlspecialchars_decode($table->series_text, ENT_QUOTES);
		$table->alias       = JApplication::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplication::stringURLSafe($table->series_text);
		}

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('MAX(ordering)')->from('#__bsms_series');
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		if ($table->ordering == 0)
		{
			$table->ordering = 1;
			$table->reorder('id = ' . (int) $table->id);
		}
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string $name     The table name. Optional.
	 * @param   string $prefix   The class prefix. Optional.
	 * @param   array  $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 */
	public function getTable($name = 'Serie', $prefix = 'Table', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   int $pk  The id of the primary key.
	 *
	 * @return    mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item)
		{
			$item->admin = JBSMParams::getAdmin();
		}

		return $item;
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array   $data      Data for the form.
	 * @param   boolean $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_biblestudy.serie', 'serie', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);

		} // The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}

		$user = JFactory::getUser();

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_biblestudy.serie.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_biblestudy'))
		)
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');

		}

		return $form;
	}

	/**
	 * Get Teacher data
	 *
	 * @return object
	 */
	public function getTeacher()
	{
		if (empty($this->_teacher))
		{
			$query          = 'SELECT id AS value, teachername AS text'
				. ' FROM #__bsms_teachers'
				. ' WHERE published = 1';
			$this->_teacher = $this->_getList($query);
		}

		return $this->_teacher;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = JFactory::getApplication();
		$data = $app->getUserState('com_biblestudy.edit.serie.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data  The form data.
	 *
	 * @return    boolean    True on success.
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle('0', $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}

		if (parent::save($data))
		{

			return true;
		}

		return false;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable $table  A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since    1.6
	 */
	protected function getReorderConditions($table)
	{
		return array();
	}

	/**
	 * Method to allow derived classes to prepossess the form.
	 *
	 * @param   JForm  $form   A JForm object.
	 * @param   mixed  $data   The data expected for the form.
	 * @param   string $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since    3.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		return $pk;
	}

	/**
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string  $group      The cache group
	 * @param   integer $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_biblestudy');
		parent::cleanCache('mod_biblestudy');
		parent::cleanCache('mod_biblestudy_podcast');
	}

}
