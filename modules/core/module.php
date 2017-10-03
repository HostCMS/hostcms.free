<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract module. Use _adminForms to create forms with modules.
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = NULL;

	/**
	 * Module date
	 * @var date
	 */
	public $date = NULL;

	/**
	 * Module menu
	 * @var array
	 */
	public $menu = array();

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = NULL;

	/**
	 * Module
	 * @var Core_Entity
	 */
	//protected $_module = NULL;

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Description of Admin Forms
	 *
	 * <code>array(
			'form1' => array(										//-- Форма
				'guid' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',		//-- guid формы
				'key_field' => 'id',									//-- Наименование ключевого поля из БД
				'default_order_field' => 'id',							//-- Поле сортировки по умолчанию
				'on_page' => 30,										//-- Количество строк на странице
				'show_operations' => 0,									//-- Показывать операции
				'show_group_operations' => 0,							//-- Показывать групповые операции
				'default_order_direction' => 0,							//-- Направление сортировки: 1 - по возрастанию, 0 - по убыванию
				'name' => array(										//-- название формы
					1 => 'Наименование модуля',							//-- по-русски - 1=идентификатор языка
					2 => 'Module name'									//-- по-английски - 2=идентификатор языка
				),
				'fields' => array(										//-- поля на отображаемой форме
					'id' => array(										//-- наименование поля из сущности БД(название столбца)
						'name' => array(								//-- название поля в админке
							1 => 'Код',									//-- по-русски - 1=идентификатор языка
							2 => 'ID'									//-- по-английски - 2=идентификатор языка
						),
						'sorting' => 10,								//-- поле сортировки
						'ico' => '',									//-- иконка поля
						'type' => 1,									//-- тип поля
						'format' => '',									//-- формат поля
						'allow_sorting' => 1,							//-- разрешить сортировку по полю 0-нет, 1-да
						'allow_filter' => 1,							//-- разрешить фильтрацию по полю 0-нет, 1-да
						'editable' => 1,								//-- разрешить inline-редактирование по полю 0-нет, 1-да
						'filter_type' => 0,								//-- тип фильтрации
						'class' => '',									//-- класс для поля
						'width' => '',									//-- ширина поля, например '55px'
						'image' => '',									//-- картинка для поля
						'link' => '',									//-- ссылка для поля
						'onclick' => '',								//-- событие нажатия на поле
						'list' => '',									//--
					),
					'field2' =>  array(
						'name' => array(								//-- название поля в админке
							1 => 'Наименование кампании',				//-- по-русски - 1=идентификатор языка
							2 => 'Campaign name'						//-- по-английски - 2=идентификатор языка
						),
						'sorting' => 20,
						.....
					),
				),
				'actions' => array(
					'edit' => array(									//-- ключевое наименование действия для формы
						'name' => array(								//-- название действия в админке
							1 => 'Редактировать',						//-- по-русски - 1=идентификатор языка
							2 => 'Edit'									//-- по-английски - 2=идентификатор языка
						),
						'sorting' => 10,								//-- сортировка для действий
						'picture' => '',								
						'icon' => 'fa fa-pencil',
						'color' => 'palegreen',
						'single' => 1,
						'group' => 0,
						'dataset' => 0,
						'confirm' => 0,
					),
				),
			),
	 		'form2' => array( ... )
		)
		</code>
		@var array
	 */
	protected $_adminForms = array();

	/**
	 * Create module instance
	 * @param string $moduleName module name
	 * @return mixed
	 */
	static public function factory($moduleName)
	{
		if (isset(self::$instance[$moduleName]))
		{
			return self::$instance[$moduleName];
		}

		$modelName = ucfirst($moduleName) . '_Module';

		if (class_exists($modelName))
		{
			$oReflectionClass = new ReflectionClass($modelName);

			return self::$instance[$moduleName] = !$oReflectionClass->isAbstract()
				? new $modelName()
				: NULL;
		}

		return NULL;
	}

	/**
	 * Set module
	 * @param Core_Entity Module
	 */
	/*public function setModule(Core_Entity $Module)
	{
		$this->_module = $Module;
		return $this;
	}*/

	/**
	 * Get module name
	 * @return array
	 */
	public function getModuleName()
	{
		return $this->_moduleName;
	}

	/**
	 * List of admin pages
	 * @var array
	 */
	protected $_adminPages = array();

	/**
	 * Get list of admin pages
	 * @return array
	 */
	public function getAdminPages()
	{
		return $this->_adminPages;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * List of Schedule Actions
	 * @var array
	 */
	protected $_scheduleActions = array();

	/**
	 * Get List of Schedule Actions
	 * @return array
	 */
	public function getScheduleActions()
	{
		return $this->_scheduleActions;
	}

	/**
	 * Get List of Notification
	 * @param int $type
	 * @param int $entityId
	 * @return array
	 */
	public function getNotifications($type, $entityId)
	{
		return array(
			'icon' => array(
				'ico' => 'fa fa-check',
				'color' => 'white',
				'background-color' => 'bg-themeprimary'
			),
			'href' => '',
			'onclick' => '',
			'extra' => array(
				'icons' => array(),
				'description' => NULL
			)
		);
	}

	/**
	 * Notify module on the action on schedule
	 * @param int $action action number
	 * @param int $entityId entity ID
	 * @return array
	 */
	public function callSchedule($action, $entityId)
	{
		// do smth
	}

	/**
	 * Call new notifications
	 */
	public function callNotifications()
	{
		// do smth
	}

	/**
	 * Install module
	 * @return self
	 * @hostcms-event Core_Module.onBeforeInstall
	 */
	public function install()
	{
		Core_Event::notify(get_class($this) . '.onBeforeInstall', $this);

		foreach ($this->_adminForms as $aForm)
		{
			$this->_addAdminForm($aForm['name'], $aForm);
		}

		return $this;
	}

	/**
	 * Uninstall module
	 * @return self
	 * @hostcms-event Core_Module.onBeforeUninstall
	 */
	public function uninstall()
	{
		Core_Event::notify(get_class($this) . '.onBeforeUninstall', $this);

		foreach ($this->_adminForms as $aForm)
		{
			$oAdmin_Form = Core_Entity::factory('Admin_Form')->getByGuid($aForm['guid']);
			!is_null($oAdmin_Form) && $oAdmin_Form->delete();
		}

		return $this;
	}

	/**
	 * Add Admin Form
	 *
	 * @param string $name
	 * @param array $aForm Array of attributes
	 * @return Admin_Form_Model
	 */
	protected function _addAdminForm($name, $aForm)
	{
		$oAdmin_Form = NULL;

		if (isset($aForm['guid']))
		{
			$oAdmin_Form = Core_Entity::factory('Admin_Form')->getByGuid($aForm['guid']);

			if (is_null($oAdmin_Form))
			{
				$oAdmin_Word_Form = $this->_addAdminWord($name);

				$oAdmin_Form = Core_Entity::factory('Admin_Form');
				$oAdmin_Form->admin_word_id = $oAdmin_Word_Form->id;
				$oAdmin_Form->key_field = $aForm['key_field'];
				$oAdmin_Form->default_order_field = Core_Array::get($aForm, 'default_order_field', $aForm['key_field']);

				$oAdmin_Form->on_page = Core_Array::get($aForm, 'on_page', 20);
				$oAdmin_Form->show_operations = Core_Array::get($aForm, 'show_operations', 1);
				$oAdmin_Form->show_group_operations = Core_Array::get($aForm, 'show_group_operations', 0);
				$oAdmin_Form->default_order_direction = Core_Array::get($aForm, 'default_order_direction', 0);
				$oAdmin_Form->guid = $aForm['guid'];
				$oAdmin_Form->save();

				if (isset($aForm['fields']))
				{
					foreach ($aForm['fields'] as $fieldName => $aField)
					{
						$oAdmin_Form->add(
							$this->_addAdminFormField($fieldName, $aField)
						);
					}
				}

				if (isset($aForm['actions']))
				{
					foreach ($aForm['actions'] as $functionName => $aAction)
					{
						$oAdmin_Form->add(
							$this->_addAdminFormAction($functionName, $aAction)
						);
					}
				}
			}
		}

		return $oAdmin_Form;
	}

	/**
	 * Add Admin Form Filed
	 *
	 * @param string $name
	 * @param array $aField Array of attributes
	 * @return Admin_Form_Field_Model
	 */
	protected function _addAdminFormField($name, $aField)
	{
		$oAdmin_Word_Form = $this->_addAdminWord($aField['name']);

		$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field');
		$oAdmin_Form_Field->admin_word_id = $oAdmin_Word_Form->id;
		$oAdmin_Form_Field->name = $name;
		$oAdmin_Form_Field->sorting = Core_Array::get($aField, 'sorting', 1000);
		$oAdmin_Form_Field->ico = Core_Array::get($aField, 'ico', '');
		$oAdmin_Form_Field->type = Core_Array::get($aField, 'type', 1);
		$oAdmin_Form_Field->format = Core_Array::get($aField, 'format', '');
		$oAdmin_Form_Field->allow_sorting = Core_Array::get($aField, 'allow_sorting', 1);
		$oAdmin_Form_Field->allow_filter = Core_Array::get($aField, 'allow_filter', 1);
		$oAdmin_Form_Field->editable = Core_Array::get($aField, 'editable', 1);
		$oAdmin_Form_Field->filter_type = Core_Array::get($aField, 'filter_type', 0);
		$oAdmin_Form_Field->class = Core_Array::get($aField, 'class', '');
		$oAdmin_Form_Field->width = Core_Array::get($aField, 'width', '');
		$oAdmin_Form_Field->image = Core_Array::get($aField, 'image', '');
		$oAdmin_Form_Field->link = Core_Array::get($aField, 'link', '');
		$oAdmin_Form_Field->onclick = Core_Array::get($aField, 'onclick', '');
		$oAdmin_Form_Field->list = Core_Array::get($aField, 'list', '');
		$oAdmin_Form_Field->save();

		return $oAdmin_Form_Field;
	}

	/**
	 * Add Admin Form Action
	 *
	 * @param string $functionName
	 * @param array $aAction Array of attributes
	 * @return Admin_Form_Action_Model
	 */
	protected function _addAdminFormAction($functionName, $aAction)
	{
		$oAdmin_Word_Form = $this->_addAdminWord($aAction['name']);

		$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
		$oAdmin_Form_Action->admin_word_id = $oAdmin_Word_Form->id;
		$oAdmin_Form_Action->name = $functionName;
		$oAdmin_Form_Action->picture = Core_Array::get($aAction, 'picture', '');
		$oAdmin_Form_Action->icon = Core_Array::get($aAction, 'icon', '');
		$oAdmin_Form_Action->color = Core_Array::get($aAction, 'color', '');
		$oAdmin_Form_Action->single = Core_Array::get($aAction, 'single', 0);
		$oAdmin_Form_Action->group = Core_Array::get($aAction, 'group', 0);
		$oAdmin_Form_Action->sorting = Core_Array::get($aAction, 'sorting', 1000);
		$oAdmin_Form_Action->dataset = Core_Array::get($aAction, 'dataset', '-1');
		$oAdmin_Form_Action->confirm = Core_Array::get($aAction, 'confirm', 0);
		$oAdmin_Form_Action->save();

		return $oAdmin_Form_Action;
	}

	/**
	 * Add Admin Form Words
	 *
	 * @param array $aWords Array of words ($lngId => $word)
	 * @retun Admin_Word_Model
	 */
	protected function _addAdminWord(array $aWords)
	{
		$oAdmin_Word_Form = Core_Entity::factory('Admin_Word')->save();

		foreach ($aWords as $admin_language_id => $value)
		{
			$oAdmin_Word_Form->add(
				$this->_addAdminWordValue($admin_language_id, $value)
			);
		}
		return $oAdmin_Word_Form;
	}

	/**
	 * Add Admin_Word_Value
	 *
	 * @param int $admin_language_id Language ID
	 * @param string $value value
	 * @return Admin_Word_Value_Model
	 */
	protected function _addAdminWordValue($admin_language_id, $value)
	{
		$oAdmin_Word_Value = Core_Entity::factory('Admin_Word_Value');
		$oAdmin_Word_Value->admin_language_id = $admin_language_id;
		$oAdmin_Word_Value->name = $value;
		$oAdmin_Word_Value->save();

		return $oAdmin_Word_Value;
	}
}