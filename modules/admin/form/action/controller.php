<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Admin_Form_Action_Controller extends Core_Servant_Properties
{
	/**
	 * Form Action
	 * @var Admin_Form_Action
	 */
	protected $_Admin_Form_Action = NULL;

	/**
	 * Form controller
	 * @var Admin_Form_Controller
	 */
	protected $_Admin_Form_Controller = NULL;

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		$this->_Admin_Form_Action = $oAdmin_Form_Action;

		if (is_null($this->_Admin_Form_Action->id))
		{
			throw new Core_Exception('Admin form action does not exist.');
		}

		parent::__construct();
	}

	/**
	* Create and return controller for current skin
	* @param string $className name of class
	* @param Admin_Form_Action_Model $oAdmin_Form_Action action
	* @return object
	*/
	static public function factory($className, Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		//$skinClassName = ucfirst($className) . '_' . ucfirst(Core_Skin::instance()->getSkinName());

		return /*class_exists($skinClassName)
			? new $skinClassName($oAdmin_Form_Action)
			: */new $className($oAdmin_Form_Action);
	}

	/**
	 * Set Admin_Form_Controller
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return self
	 */
	public function controller(Admin_Form_Controller $oAdmin_Form_Controller)
	{
		$this->_Admin_Form_Controller = $oAdmin_Form_Controller;
		return $this;
	}

	/**
	 * Children entities list
	 * @var array
	 */
	protected $_children = array();

	/**
	 * Add entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity
	 * @return self
	 */
	public function addEntity(Admin_Form_Entity $oAdmin_Form_Entity)
	{
		// Set link to controller
		$oAdmin_Form_Entity->controller($this);

		$this->_children[] = $oAdmin_Form_Entity;
		return $this;
	}

	/**
	 * Content
	 * @var string
	 */
	protected $_content = NULL;

	/**
	 * Message text
	 * @var string
	 */
	protected $_message = NULL;

	/**
	 * Get content
	 * @return object
	 */
	public function getContent()
	{
		return $this->_content;
	}

	/**
	 * Add content
	 * @param string $content content
	 * @return self
	 */
	public function addContent($content)
	{
		$this->_content .= $content;
		return $this;
	}

	/**
	 * Get message
	 * @return self
	 */
	public function getMessage()
	{
		return $this->_message;
	}

	/**
	 * Add message
	 * @param $message message
	 * @return self
	 */
	public function addMessage($message)
	{
		$this->_message .= $message;
		return $this;
	}

	/**
	 * Object
	 * @var object
	 */
	protected $_object = NULL;

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this->_object = $object;
		return $this;
	}

	/**
	 * Get object
	 * @return object
	 */
	public function getObject()
	{
		return $this->_object;
	}

	/**
	 * Dataset ID
	 * @var int
	 */
	protected $_datasetId = NULL;

	/**
	 * Set dataset ID
	 * @param int $datasetId ID of dataset
	 */
	public function setDatasetId($datasetId)
	{
		$this->_datasetId = $datasetId;
		return $this;
	}

	/**
	 * Get dataset ID
	 * @return int
	 */
	public function getDatasetId()
	{
		return $this->_datasetId;
	}

	/**
	 * Get action name
	 * @return string
	 */
	public function getName()
	{
		return $this->_Admin_Form_Action->name;
	}

	/**
	 * Execute operation $operation
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	abstract public function execute($operation = NULL);
}