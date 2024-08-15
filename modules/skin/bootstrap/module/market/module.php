<?php

/**
 * Market. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Market_Module extends Market_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Market.menu'))
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
		$this->_path = "/admin/index.php?ajaxWidgetLoad&moduleId={$oModule->id}&type={$type}";

		if ($ajax)
		{
			$this->_content();
		}
		else
		{
			?><div class="col-xs-12" id="marketAdminPage">
				<script>
				$.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#marketAdminPage') });
				</script>
			</div><?php
		}

		return TRUE;
	}

	protected function _content()
	{
		try
		{
			$oMarket_Controller = Market_Controller::instance();
			$oMarket_Controller
				->setMarketOptions()
				->limit(3)
				->order('rand')
				->getMarket();

			if ($oMarket_Controller->error == 0)
			{
				?><div class="widget market">
					<div class="widget-header bordered-bottom bordered-themesecondary">
						<i class="widget-icon fa fa-cogs themesecondary"></i>
						<span class="widget-caption themesecondary"><?php echo Core::_('Market.title')?></span>
						<div class="widget-buttons">
							<a data-toggle="maximize">
								<i class="fa fa-expand gray"></i>
							</a>
							<a data-toggle="refresh" onclick="$(this).find('i').addClass('fa-spin'); $.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#marketAdminPage'), 'button': $(this).find('i') });">
								<i class="fa-solid fa-rotate gray"></i>
							</a>
						</div>
					</div>
					<div class="widget-body">
						<div class="row">
						<?php echo $oMarket_Controller->getMarketItemsHtml()?>
						</div>
					</div>
				</div><?php
			}
		}
		catch (Exception $e)
		{

		}

		return $this;
	}
}