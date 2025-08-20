<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
abstract class Admin_Form_Entity extends Core_Html_Entity
{
	/**
	 * Form controller
	 * @var Admin_Form_Controller
	 */
	protected $_Admin_Form_Controller = NULL;

	/**
	 * Set controller
	 * @param Admin_Form_Controller controller
	 * @return self
	 */
	public function controller($controller)
	{
		if (is_null($this->_Admin_Form_Controller))
		{
			$this->_Admin_Form_Controller = $controller;

			foreach ($this->_children as $oAdmin_Form_Entity)
			{
				method_exists($oAdmin_Form_Entity, 'controller') && $oAdmin_Form_Entity->controller($controller);
			}
		}

		return $this;
	}

	/**
	 * Get admin form controller
	 * @return Admin_Form_Controller
	 */
	public function getAdminFormController()
	{
		return $this->_Admin_Form_Controller;
	}

	/**
	 * Create and return an object of Admin_Form_Entity for current skin
	 * @param string $className name of class
	 * @return object
	 */
	static public function factory($className)
	{
		$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__ . '_' . ucfirst($className);

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist",
				array('%className' => $className));
		}

		return new $className();
	}

	/**
	 * Move entity to another tab
	 * @param Admin_Form_Entity $oAdmin_Form_Entity entity you want to move
	 * @param Admin_Form_Entity $oTabTo target tab
	 * @return self
	 */
	public function move(Admin_Form_Entity $oAdmin_Form_Entity, Admin_Form_Entity $oTabTo)
	{
		$this->delete($oAdmin_Form_Entity);
		$oTabTo->add($oAdmin_Form_Entity);
		return $this;
	}

	/**
	 * Move entity before some another entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity entity you want to move
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_Before entity before which you want to place
	 * @param Admin_Form_Entity $oTabTo target tab
	 * @return self
	 */
	public function moveBefore(Admin_Form_Entity $oAdmin_Form_Entity, Admin_Form_Entity $oAdmin_Form_Entity_Before, $oTabTo = NULL)
	{
		if (is_null($oTabTo))
		{
			$oTabTo = $this;
		}
		$this->delete($oAdmin_Form_Entity);
		$oTabTo->addBefore($oAdmin_Form_Entity, $oAdmin_Form_Entity_Before);
		return $this;
	}

	/**
	 * Move entity after some another entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity entity you want to move
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_After entity after which you want to place
	 * @param Admin_Form_Entity $oTabTo target tab
	 * @return self
	 */
	public function moveAfter(Admin_Form_Entity $oAdmin_Form_Entity, Admin_Form_Entity $oAdmin_Form_Entity_After, $oTabTo = NULL)
	{
		if (is_null($oTabTo))
		{
			$oTabTo = $this;
		}
		$this->delete($oAdmin_Form_Entity);
		$oTabTo->addAfter($oAdmin_Form_Entity, $oAdmin_Form_Entity_After);
		return $this;
	}

	/**
	 * Add new entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity new entity
	 * @return Core_Html_Entity
	 */
	public function add($oAdmin_Form_Entity)
	{
		if (!is_object($oAdmin_Form_Entity))
		{
			throw new Core_Exception("Wrong variable type '%type'. Expecting object.",
					array('%type' => gettype($oAdmin_Form_Entity)));
		}
		// Set link to controller
		method_exists($oAdmin_Form_Entity, 'controller') && $oAdmin_Form_Entity->controller($this->_Admin_Form_Controller);

		return parent::add($oAdmin_Form_Entity);
	}

	/**
	 * Add new entity before $oAdmin_Form_Entity_Before
	 * @param Admin_Form_Entity $oAdmin_Form_Entity new entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_Before entity before which to add the new entity
	 * @return Core_Html_Entity
	 */
	public function addBefore($oAdmin_Form_Entity, $oAdmin_Form_Entity_Before)
	{
		// Set link to controller
		$oAdmin_Form_Entity->controller($this->_Admin_Form_Controller);
		return parent::addBefore($oAdmin_Form_Entity, $oAdmin_Form_Entity_Before);
	}

	/**
	 * Add new entity after $oAdmin_Form_Entity_After
	 * @param Admin_Form_Entity $oAdmin_Form_Entity new entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_After entity after which to add the new entity
	 * @return Core_Html_Entity
	 */
	public function addAfter($oAdmin_Form_Entity, $oAdmin_Form_Entity_After)
	{
		// Set link to controller
		$oAdmin_Form_Entity->controller($this->_Admin_Form_Controller);
		return parent::addAfter($oAdmin_Form_Entity, $oAdmin_Form_Entity_After);
	}

	/**
	 * Available formats for lib
	 * Доступные форматы для lib
	 * @var array
	 */
	protected $_format = array(
		// IP v4 or v6
		'ip' => '/(^([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$)|(^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$)/',
		'email' => '/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/',
		// 'url' => '/^([A-Za-z]+:\/\/)?([A-Za-z0-9]+(:[A-Za-z0-9]+)?@)?([a-zA-Z0-9][-A-Za-z0-9.]*\.[A-Za-z]{2,7})(:[0-9]+)?(\/[-_.A-Za-z0-9]+)?(\?[A-Za-z0-9%&=]+)?(#\w+)?$/',
		'url' => "/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/",
		'integer' => '/^[-+]?[0-9]*$/',
		'positiveInteger' => '/^(0*[1-9])+[0-9]*$/',
		'path' => '/^[а-яіїєґА-ЯІЇЄҐёЁA-Za-z0-9_ \-\.\/]+$/',
		'latinBase' => '/^[A-Za-z0-9_\-]+$/',
		'decimal' => '/^[-+]?[0-9]{1,}\\.{0,1}[0-9]*$/',
		'date' => '/^([0-2][0-9]|[3][0-1])\.([0][0-9]|[1][0-2])\.\d{2,4}$/',
		'datetime' => '/^([0-2][0-9]|[3][0-1])\.([0][0-9]|[1][0-2])\.\d{2,4} ([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$/'
	);

	/**
	 * Get format by name
	 * @param string $name name
	 * @return string
	 */
	public function getFormat($name)
	{
		return isset($this->_format[$name]) ? $this->_format[$name] : NULL;
	}

	/**
	 * Get allowed properties
	 * @return array
	 */
	public function getAttrsString()
	{
		$aAttr = parent::getAttrsString();

		if (isset($this->format) && !is_null($this->format))
		{
			$aAttr[] = 'data-required="1"';

			if (isset($this->format['minlen']['value']))
			{
				$aAttr[] = "data-min=\"" . intval($this->format['minlen']['value']) . "\"";
			}

			if (isset($this->format['maxlen']['value']))
			{
				$aAttr[] = "data-max=\"" . intval($this->format['maxlen']['value']) . "\"";
			}

			if (isset($this->format['lib']['value']))
			{
				$reg = $this->getFormat($this->format['lib']['value']);

				// Соответствие было найдено
				if (!is_null($reg))
				{
					$aAttr[] = "data-reg=\"" . htmlspecialchars(trim($reg, '/')) . "\"";

					// Было указано сообщение для формата
					if (isset($this->format['lib']['message']))
					{
						$aAttr[] = "data-reg-message=\"" . htmlspecialchars($this->format['lib']['message']) . "\"";
					}
				}
			}
			elseif (isset($this->format['reg']['value']))
			{
				$aAttr[] = "data-reg=\"" . htmlspecialchars(trim($this->format['reg']['value'], '/')) . "\"";

				// Было указано сообщение для формата
				if (isset($this->format['reg']['message']))
				{
					$aAttr[] = "data-reg-message=\"" . htmlspecialchars($this->format['reg']['message']) . "\"";
				}
			}

			if (isset($this->format['fieldEquality']['value']))
			{
				$aAttr[] = "data-equality=\"" . htmlspecialchars($this->format['fieldEquality']['value']) . "\"";

				// Было указано сообщение для формата
				if (isset($this->format['fieldEquality']['message']))
				{
					$aAttr[] = "data-equality-message=\"" . htmlspecialchars($this->format['fieldEquality']['message']) . "\"";
				}
			}
		}

		return $aAttr;
	}

	/**
	 * Apply format for field
	 */
	protected function _showFormat()
	{
		if (!is_null($this->format))
		{
			// Блок для ошибок выводим только при указании условий формата
			?><div id="<?php echo $this->id?>_error" class="fieldcheck-error"></div><?php
		}
	}
}