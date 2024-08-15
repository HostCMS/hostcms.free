<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Breadcrumb extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'href',
		'onclick',
		'separator'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->separator = '<span class="divider"> / </span>';
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Breadcrumb.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Breadcrumb.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		?><li><a href="<?php echo htmlspecialchars((string) $this->href)?>" onclick="<?php echo htmlspecialchars((string) $this->onclick)?>"><?php echo htmlspecialchars((string) $this->name)?></a></li><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}