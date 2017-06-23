<?php

$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::get(Core_Page::instance()->libParams, 'informationsystemId'));

$Informationsystem_Controller_Show = new Informationsystem_Controller_Show($oInformationsystem);

$Informationsystem_Controller_Show
	->limit($oInformationsystem->items_on_page)
	->parseUrl();

// При передаче данных методом GET /guestbook/?-????????????????-????-????????????/
count($_GET) && $Informationsystem_Controller_Show->error404();
	
// Текстовая информация для указания номера страницы, например "страница"
$pageName = Core_Array::get(Core_Page::instance()->libParams, 'page')
	? Core_Array::get(Core_Page::instance()->libParams, 'page')
	: 'страница';

// Разделитель в заголовке страницы
$pageSeparator = Core_Array::get(Core_Page::instance()->libParams, 'separator')
	? Core_Page::instance()->libParams['separator']
	: ' / ';

$aTitle = array($oInformationsystem->name);
$aDescription = array($oInformationsystem->name);
$aKeywords = array($oInformationsystem->name);

if ($Informationsystem_Controller_Show->group)
{
	$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $Informationsystem_Controller_Show->group);

	do {
		$aTitle[] = $oInformationsystem_Group->seo_title != ''
			? $oInformationsystem_Group->seo_title
			: $oInformationsystem_Group->name;

		$aDescription[] = $oInformationsystem_Group->seo_description != ''
			? $oInformationsystem_Group->seo_description
			: $oInformationsystem_Group->name;

		$aKeywords[] = $oInformationsystem_Group->seo_keywords != ''
			? $oInformationsystem_Group->seo_keywords
			: $oInformationsystem_Group->name;

	} while($oInformationsystem_Group = $oInformationsystem_Group->getParent());
}

if ($Informationsystem_Controller_Show->item)
{
	$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $Informationsystem_Controller_Show->item);

	$aTitle[] = $oInformationsystem_Item->seo_title != ''
		? $oInformationsystem_Item->seo_title
		: $oInformationsystem_Item->name;

	$aDescription[] = $oInformationsystem_Item->seo_description != ''
		? $oInformationsystem_Item->seo_description
		: $oInformationsystem_Item->name;

	$aKeywords[] = $oInformationsystem_Item->seo_keywords != ''
		? $oInformationsystem_Item->seo_keywords
		: $oInformationsystem_Item->name;
}

if (count($aTitle) > 1)
{
	$aTitle = array_reverse($aTitle);
	$aDescription = array_reverse($aDescription);
	$aKeywords = array_reverse($aKeywords);

	Core_Page::instance()->title(implode($pageSeparator, $aTitle));
	Core_Page::instance()->description(implode($pageSeparator, $aDescription));
	Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
}

Core_Page::instance()->object = $Informationsystem_Controller_Show;