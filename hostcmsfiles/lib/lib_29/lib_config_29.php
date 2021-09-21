<?php

Core_Session::close();

// Stop buffering
ob_get_clean();

// Создавать индекс
$createIndex = Core_Array::get(Core_Page::instance()->libParams, 'createIndex', FALSE);

// Количество страниц в каждый файл
$perFile = 50000;

$oSite = Core_Entity::factory('Site')->getByAlias(Core::$url['host']);

$oSite_Alias = $oSite->getCurrentAlias();

if (is_null($oSite_Alias))
{
	?>Site hasn't had a default alias!<?php
	exit();
}

// Добавление экспорта производителей в Google Sitemap
/*class My_Core_Sitemap extends Core_Sitemap
{
	protected function _fillShop(Structure_Model $oStructure, Shop_Model $oShop)
	{
		parent::_fillShop($oStructure, $oShop);
		
		$sProtocol = $this->getProtocol($oStructure);
		
		$path = $sProtocol . $this->_siteAlias . $oShop->Structure->getPath();

		$aShop_Producers = $oShop->Shop_Producers->findAll(FALSE);
		foreach ($aShop_Producers as $oShop_Producer)
		{
			$this->addNode($path . 'producers/' . $oShop_Producer->path . '/', $oStructure->changefreq, $oStructure->priority);
		}
	}
}*/

try
{
	$oCore_Sitemap = new Core_Sitemap($oSite);
	//$oCore_Sitemap = new My_Core_Sitemap($oSite);
	$oCore_Sitemap
		->createIndex($createIndex)
		->perFile($perFile)
		// Перегенерировать раз в 3 дня
		->rebuildTime(60*60*24 * 3);

	if (Core::moduleIsActive('informationsystem'))
	{
		$oCore_Sitemap
			// Показывать группы информационных систем в карте сайта
			->showInformationsystemGroups(Core_Page::instance()->libParams['showInformationsystemGroups'])
			// Показывать элементы информационных систем в карте сайта
			->showInformationsystemItems(Core_Page::instance()->libParams['showInformationsystemItems'])
			// Показывать метки информационных систем
			->showInformationsystemTags(TRUE);
	}

	if (Core::moduleIsActive('shop'))
	{
		$oCore_Sitemap
			// Показывать группы магазина в карте сайта
			->showShopGroups(Core_Page::instance()->libParams['showShopGroups'])
			// Показывать товары магазина в карте сайта
			->showShopItems(Core_Page::instance()->libParams['showShopItems'])
			// Показывать модификации в карте сайта
			->showModifications(Core_Array::get(Core_Page::instance()->libParams, 'showModifications', 1))
			// Показывать метки магазина
			->showShopFilter(TRUE)
			->showShopTags(TRUE);
	}

	$oCore_Sitemap
		// Раскомментируйте при наличии достаточного объема оперативной памяти
		//->limit(10000)
		->fillNodes()
		->execute();
}
catch (Exception $e) {
	echo "\nSitemap error. See Log.";
}

exit();