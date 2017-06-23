<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Search. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
					$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
					$oSite = Core_Entity::factory('Site', CURRENT_SITE);

					$aConfig = Core::$config->get('search_config') + array(
						'modules' => array()
					);

					$aJson = array();

					if (strlen($sQuery))
					{
						$Search_Controller = Search_Controller::instance();

						$Search_Controller
							->site($oSite)
							->offset(0)
							->page(1)
							->limit(10)
							->inner('all');

						$aSearch_Pages = $Search_Controller->find($sQuery);

						foreach ($aSearch_Pages as $oSearch_Page)
						{
							$aReturn = array();

							if (isset($aConfig['modules'][$oSearch_Page->module]))
							{
								$oCore_Module = Core_Module::factory($aConfig['modules'][$oSearch_Page->module]);

								if ($oCore_Module && method_exists($oCore_Module, 'backendSearchCallback'))
								{
									$aReturn = $oCore_Module->backendSearchCallback($oSearch_Page);
								}
							}

							$aJson[] = array(
								'id' => $oSearch_Page->id,
								'label' => $oSearch_Page->title,
								'href' => Core_Array::get($aReturn, 'href'),
								'onclick' => Core_Array::get($aReturn, 'onclick'),
								'icon' => 'fa ' . Core_Array::get($aReturn, 'icon')
							);
						}
					}

					Core::showJson($aJson);
				}
			break;
		}
	}

	public function widget()
	{
		?><!-- Search -->
		<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
			<div class="databox radius-bordered databox-shadowed hostcms-widget-databox">
				<div class="databox-left bg-palegreen">
					<div class="databox-piechart">
						<?php
						$iSearchPagesOnCurrentSite = Search_Controller::instance()->getPageCount(CURRENT_SITE);
						// Общее количество проиндексированных страниц
						//$iSearchPagesTotal = Search_Controller::instance()->getPageCount(NULL);

						/*$iPercent = $iSearchPagesTotal > 0
							? floor($iSearchPagesOnCurrentSite * 100 / $iSearchPagesTotal)
							: 0;
						?><div id="searchWidget" class="easyPieChart" data-barcolor="#fff" data-linecap="butt" data-percent="<?php echo $iPercent?>" data-animate="500" data-linewidth="3" data-size="47" data-trackcolor="rgba(255,255,255,0.1)"><span class="white font-90"><?php echo $iPercent?>%</span></div>*/
						?>
						<a href="/admin/search/index.php" onclick="$.adminLoad({path: '/admin/search/index.php'}); return false"><i class="fa fa-search fa-3x"></i></a>
					</div>
				</div>
				<div class="databox-right">
					<span class="databox-number palegreen"><?php echo number_format($iSearchPagesOnCurrentSite, 0, '.', ' ')?></span>
					<div class="databox-text"><?php echo Core::_('Search.indexed')?></div>
					<div class="databox-stat palegreen radius-bordered">
						<i class="stat-icon icon-lg fa fa-search"></i>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}