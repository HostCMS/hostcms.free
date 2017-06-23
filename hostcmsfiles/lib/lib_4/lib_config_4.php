<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_Show = new Shop_Controller_Show($oShop);

$Shop_Controller_Show
	->limit($oShop->items_on_page)
	->parseUrl();

if (!is_null(Core_Array::getGet('vote')))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
	$entity_id = intval(Core_Array::getGet('id'));

	if ($entity_id && !is_null($oSiteuser))
	{
		$entity_type = strval(Core_Array::getGet('entity_type'));
		$vote = intval(Core_Array::getGet('vote'));

		$oObject = Vote_Controller::instance()->getVotedObject($entity_type, $entity_id);

		if (!is_null($oObject))
		{
			$oVote = $oObject->Votes->getBySiteuser_Id($oSiteuser->id);

			$vote_value = $vote ? 1 : -1;

			$deleteVote = 0;
			// Пользователь не голосовал ранее
			if (is_null($oVote))
			{
				$oVote = Core_Entity::factory('Vote');
				$oVote->siteuser_id = $oSiteuser->id;
				$oVote->value = $vote_value;

				$oObject->add($oVote);
			}
			// Пользователь голосовал ранее, но поставил противоположную оценку
			elseif ($oVote->value != $vote_value)
			{
				$oVote->value = $vote_value;
				$oVote->save();
			}
			// Пользователь голосовал ранее и поставил такую же оценку как и ранее, обнуляем его голосование, как будто он вообще не голосовал
			else
			{
				$deleteVote = 1;
				$oVote->delete();
			}

			$aVotingStatistic = Vote_Controller::instance()->getRate($entity_type, $entity_id);

			Core_Page::instance()->response
			->body(
				json_encode(array('value' => $oVote->value, 'item' => $oObject->id, 'entity_type' => $entity_type,
					'likes' => $aVotingStatistic['likes'], 'dislikes' => $aVotingStatistic['dislikes'],
					'rate' => $aVotingStatistic['rate'], 'delete_vote' => $deleteVote)
				)
			);
		}
	}

	Core_Page::instance()->response
			->status(200)
			->header('Pragma', "no-cache")
			->header('Cache-Control', "private, no-cache")
			->header('Vary', "Accept")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS')
			->header('Content-Disposition', 'inline; filename="files.json"');

	if (strpos(Core_Array::get($_SERVER, 'HTTP_ACCEPT', ''), 'application/json') !== FALSE)
	{
		Core_Page::instance()->response->header('Content-type', 'application/json; charset=utf-8');
	}
	else
	{
		Core_Page::instance()->response
			->header('X-Content-Type-Options', 'nosniff')
			->header('Content-type', 'text/plain; charset=utf-8');
	}

	if(Core_Array::getRequest('_'))
	{
		Core_Page::instance()->response
			->sendHeaders()
			->showBody();
		exit();
	}
}
	
// Текстовая информация для указания номера страницы, например "страница"
$pageName = Core_Array::get(Core_Page::instance()->libParams, 'page')
	? Core_Array::get(Core_Page::instance()->libParams, 'page')
	: 'страница';

// Разделитель в заголовке страницы
$pageSeparator = Core_Array::get(Core_Page::instance()->libParams, 'separator')
	? Core_Page::instance()->libParams['separator']
	: ' / ';

$aTitle = array($oShop->name);
$aDescription = array($oShop->name);
$aKeywords = array($oShop->name);

if (!is_null($Shop_Controller_Show->tag) && Core::moduleIsActive('tag'))
{
	$oTag = Core_Entity::factory('Tag')->getByPath($Shop_Controller_Show->tag);
	if ($oTag)
	{
		$aTitle[] = $oTag->seo_title != '' ? $oTag->seo_title : Core::_('Shop.tag', $oTag->name);
		$aDescription[] = $oTag->seo_description != '' ? $oTag->seo_description : $oTag->name;
		$aKeywords[] = $oTag->seo_keywords != '' ? $oTag->seo_keywords : $oTag->name;
	}
}

if ($Shop_Controller_Show->group)
{
	$oShop_Group = Core_Entity::factory('Shop_Group', $Shop_Controller_Show->group);

	do {
		$aTitle[] = $oShop_Group->seo_title != ''
			? $oShop_Group->seo_title
			: $oShop_Group->name;

		$aDescription[] = $oShop_Group->seo_description != ''
			? $oShop_Group->seo_description
			: $oShop_Group->name;

		$aKeywords[] = $oShop_Group->seo_keywords != ''
			? $oShop_Group->seo_keywords
			: $oShop_Group->name;

	} while($oShop_Group = $oShop_Group->getParent());
}

if ($Shop_Controller_Show->item)
{
	$oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

	$aTitle[] = $oShop_Item->seo_title != ''
		? $oShop_Item->seo_title
		: $oShop_Item->name;

	$aDescription[] = $oShop_Item->seo_description != ''
		? $oShop_Item->seo_description
		: $oShop_Item->name;

	$aKeywords[] = $oShop_Item->seo_keywords != ''
		? $oShop_Item->seo_keywords
		: $oShop_Item->name;
}

if ($Shop_Controller_Show->producer)
{
	$oShop_Producer = Core_Entity::factory('Shop_Producer', $Shop_Controller_Show->producer);
	$aKeywords[] = $aDescription[] = $aTitle[] = $oShop_Producer->name;
}

if ($Shop_Controller_Show->page)
{
	array_unshift($aTitle, $pageName . ' ' . ($Shop_Controller_Show->page + 1));
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

Core_Page::instance()->object = $Shop_Controller_Show;