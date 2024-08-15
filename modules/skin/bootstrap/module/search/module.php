<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Search. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Search_Module extends Search_Module
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'bootstrap';

	/**
	 * Name of the module
	 * @var string
	 */
	protected $_moduleName = 'search';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Search.title'))
		);
	}

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$type = intval($type);

		switch ($type)
		{
			case 1:
				if (!is_null(Core_Array::getGet('autocomplete')) && !is_null(Core_Array::getGet('queryString')))
				{
					$sQuery = trim(Core_Str::stripTags(Core_Array::getGet('queryString', '', 'trim')));

					$aJson = array();

					if (strlen($sQuery))
					{
						$Search_Controller = Search_Controller::instance();

						// Current Site
						$Search_Controller
							->site_id(CURRENT_SITE)
							->limit(10)
							->inner('all');

						$aSearch_Pages = $Search_Controller->find($sQuery);

						$aJson = $this->_getJson($aSearch_Pages);

						// Zero Site
						$Search_Controller
							->site_id(0)
							->limit(10)
							->inner('all');

						$aSearch_Pages = $Search_Controller->find($sQuery);

						$aJson = array_merge($aJson, $this->_getJson($aSearch_Pages));
					}

					Core::showJson($aJson);
				}
			break;
		}
	}

	protected function _getJson($aSearch_Pages)
	{
		$aJson = array();

		$aConfig = Core::$config->get('search_config', array()) + array(
			'modules' => array()
		);

		foreach ($aSearch_Pages as $oSearch_Page)
		{
			if (isset($aConfig['modules'][$oSearch_Page->module]))
			{
				$oCore_Module = Core_Module_Abstract::factory($aConfig['modules'][$oSearch_Page->module]);

				if ($oCore_Module && method_exists($oCore_Module, 'backendSearchCallback'))
				{
					$aReturn = $oCore_Module->backendSearchCallback($oSearch_Page);

					if (isset($aReturn['onclick']))
					{
						$aJson[] = array(
							'id' => $oSearch_Page->id,
							'label' => strlen($oSearch_Page->title) ? $oSearch_Page->title : Core::_('Admin.no_title'),
							'href' => Core_Array::get($aReturn, 'href'),
							'onclick' => Core_Array::get($aReturn, 'onclick'),
							'icon' => 'fa ' . Core_Array::get($aReturn, 'icon')
						);
					}
				}
			}
		}

		return $aJson;
	}

	public function widget()
	{
		?><!-- Search -->
		<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
			<div class="databox radius-bordered databox-shadowed hostcms-widget-databox">
				<div class="databox-left bg-palegreen">
					<div class="databox-piechart">
						<a href="/admin/search/index.php" onclick="$.adminLoad({path: '/admin/search/index.php'}); return false"><i class="fa-solid fa-magnifying-glass fa-3x"></i></a>
					</div>
				</div>
				<div class="databox-right">
					<?php
					$iSearchPagesOnCurrentSite = Search_Controller::instance()->getPageCount(CURRENT_SITE);
					?>
					<span class="databox-number palegreen"><?php echo number_format($iSearchPagesOnCurrentSite, 0, '.', ' ')?></span>
					<div class="databox-text"><?php echo Core::_('Search.indexed')?></div>
					<div class="databox-stat palegreen radius-bordered">
						<i class="stat-icon icon-lg fa-solid fa-magnifying-glass"></i>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}