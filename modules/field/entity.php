<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Field_Entity extends Core_Empty_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $id = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $table_name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $model = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $count = NULL;

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'field';

	/**
	 * Backend badge
	 */
	public function countBackend()
	{
		$this->count && Core_Html_Entity::factory('Span')
			->class('badge badge-azure badge-square')
			->value($this->count)
			->execute();
	}
}