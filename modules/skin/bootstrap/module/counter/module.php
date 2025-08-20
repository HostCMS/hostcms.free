<?php

/**
 * Counter. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Counter_Module extends Counter_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Counter.menu'))
		);
	}

	/**
	 * Widget path
	 * @var string|NULL
	 */
	protected $_path = NULL;

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$oModule = Core_Entity::factory('Module')->getByPath($this->getModuleName());

		$type = intval($type);
		$this->_path = Admin_Form_Controller::correctBackendPath("/{admin}/index.php?ajaxWidgetLoad&moduleId={$oModule->id}&type={$type}");

		if ($ajax)
		{
			$this->_content();
		}
		else
		{
			?><div class="col-xs-12" id="counterAdminPage">
				<script>
				$.widgetLoad({ path: '<?php echo Core_Str::escapeJavascriptVariable($this->_path)?>', context: $('#counterAdminPage') });
				</script>
			</div><?php
		}

		return TRUE;
	}

	/**
	 * Show content
	 * @return self
	 */
	protected function _content()
	{
		$month = Core_Array::get(Core::$config->get('counter_config'), 'chartMonths', 12) ;
		
		echo Counter_Controller_Chart::show($month, TRUE, $this->_path);
		return $this;
	}
}