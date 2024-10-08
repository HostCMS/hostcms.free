<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Controller_Tab
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
abstract class Directory_Controller_Tab extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
		'relation',
		'showPublicityControlElement',
		'prefix'
	);


	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->prefix = '';
	}

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name)
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$driver = __CLASS__ . '_' . ucfirst($name);
			self::$instance[$name] = new $driver();
		}

		return self::$instance[$name];
	}

	/**
	 * Directory relations
	 * @var array|NULL
	 */
	protected $_aDirectory_Relations = NULL;

	/**
	 * Execute
	 * @return Admin_Form_Entity
	 */
	public function execute()
	{
		ob_start();

		?><div class="row">
			<div class="col-xs-12">
				<div class="widget directory-widget">
					<div class="widget-header" data-toggle="collapse" data-target="#collapse<?php echo $this->_directoryTypeName?>" aria-expanded="true" aria-controls="collapse<?php echo $this->_directoryTypeName?>" onclick="$(this).find('.widget-buttons i').toggleClass('fa-chevron-down fa-chevron-up')">
						<i class="widget-icon <?php echo $this->_faTitleIcon?> <?php echo $this->_titleHeaderColor?>"></i>
						<span class="widget-caption"><?php echo $this->title?></span>
						<div class="widget-buttons">
							<i class="fa-solid fa-chevron-down"></i>
						</div><!--Widget Buttons-->
					</div><!--Widget Header-->
					<div id="collapse<?php echo $this->_directoryTypeName?>" class="collapse in" aria-expanded="true">
						<div class="widget-body">
							<?php
								// Телефоны
								$oPersonalDataInnerWrapper = Admin_Form_Entity::factory('Div')
									->class('well well-sm margin-bottom-10 directory-well')
									/*->add(
										Admin_Form_Entity::factory('Code')
											->html('<p class="semi-bold"><i class="widget-icon ' . $this->_faTitleIcon . ' icon-separator ' . $this->_titleHeaderColor . '"></i>' . $this->title . '</p>')
									)*/
									;

								$this->_aDirectory_Relations = $this->relation->findAll();

								$this->_execute($oPersonalDataInnerWrapper);

								Admin_Form_Entity::factory('Div')->add($oPersonalDataInnerWrapper)->execute();
							?>
						</div><!--Widget Body-->
					</div>
				</div><!--Widget-->
			</div>
		</div><?php

		// return $oPersonalDataInnerWrapper;
		return Admin_Form_Entity::factory('Code')->html(ob_get_clean());
	}

	/**
	 * Publicity control element
	 * @return Admin_Form_Entity
	 */
	protected function _publicityControlElement()
	{
		return Admin_Form_Entity::factory('Checkbox')
			->divAttr(array('class' => 'col-xs-2 no-padding margin-top-23'))
			->caption('<acronym title="" data-original-title="' . Core::_('Core.data_show_title') . '">' . Core::_('Core.show_title') . '</acronym>');
	}

	/**
	 * Buttons
	 * @param string $className
	 * @return Admin_Form_Entity
	 */
	protected function _buttons($className = '')
	{
		$margin_top_23 = $this->showPublicityControlElement
			? 'margin-top-23-lg'
			: 'margin-top-23';

		return Admin_Form_Entity::factory('Div') // div с кноками + и -
			->class('add-remove-property ' . $margin_top_23 . ' pull-right' . (count($this->_aDirectory_Relations) ? ' btn-group' : '') . ($className ? ' ' . $className : ''))
			->add(
				Admin_Form_Entity::factory('Code')
					->html('<div class="btn btn-palegreen inverted" onclick="$.cloneFormRow(this); event.stopPropagation();"><i class="fa fa-plus-circle close"></i></div><div class="btn btn-darkorange btn-delete inverted' . (count($this->_aDirectory_Relations) ? '' : ' hide') . '" onclick="$.deleteFormRow(this); event.stopPropagation();"><i class="fa fa-minus-circle close"></i></div>')
			);
	}

	/**
	 * Get directory types
	 * @return array
	 */
	protected function _getDirectoryTypes()
	{
		$aDirectory_Types = Core_Entity::factory($this->_directoryTypeName)->findAll();

		$aMasDirectoryTypes = array();

		foreach ($aDirectory_Types as $oDirectory_Type)
		{
			$aMasDirectoryTypes[$oDirectory_Type->id] = $oDirectory_Type->name;
		}

		return $aMasDirectoryTypes;
	}
}