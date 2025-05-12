<?php
if (Core::moduleIsActive('forum'))
{
	$oForum = Core_Entity::factory('Forum', Core_Array::get(Core_Page::instance()->libParams, 'forum_id'));

	$Forum_Controller_Show = new Forum_Controller_Show($oForum);

	$Forum_Controller_Show
		->myPostsOnPage(Core_Array::get(Core_Page::instance()->libParams, 'itemsOnPage', 0, 'int'))
		->parseUrl();

	if ($Forum_Controller_Show->rss)
	{
		$Forum_Controller_Rss_Show = new Forum_Controller_Rss_Show(
			Core_Entity::factory('Forum_Category', $Forum_Controller_Show->category)
		);

		$Forum_Controller_Rss_Show
			->limit($oForum->topics_on_page);

		$Forum_Controller_Rss_Show->show();
		exit();
	}

	// Текстовая информация для указания номера страницы, например "страница"
	$pageName = Core_Array::get(Core_Page::instance()->libParams, 'page')
		? Core_Array::get(Core_Page::instance()->libParams, 'page')
		: 'страница';

	// Разделитель в заголовке страницы
	$pageSeparator = Core_Array::get(Core_Page::instance()->libParams, 'separator')
		? Core_Page::instance()->libParams['separator']
		: ' / ';

	$aTitle = array($oForum->name);
	$aDescription = array($oForum->name);
	$aKeywords = array($oForum->name);

	if ($Forum_Controller_Show->category)
	{
		$oForum_Category = Core_Entity::factory('Forum_Category', $Forum_Controller_Show->category);

		$aTitle[] = $oForum_Category->name;
		$aDescription[] = $oForum_Category->name;
		$aKeywords[] = $oForum_Category->name;
	}

	if ($Forum_Controller_Show->topic)
	{
		$oForum_Topic = Core_Entity::factory('Forum_Topic', $Forum_Controller_Show->topic);

		$oForum_Topic_Post = $oForum_Topic->Forum_Topic_Posts->getFirstPost();

		if (!is_null($oForum_Topic_Post))
		{
			$aTitle[] = $oForum_Topic_Post->subject;
			$aDescription[] = $oForum_Topic_Post->subject;
			$aKeywords[] = $oForum_Topic_Post->subject;
		}
	}

	if ($Forum_Controller_Show->page)
	{
		array_unshift($aTitle, $pageName . ' ' . ($Forum_Controller_Show->page + 1));
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

	Core_Page::instance()->object = $Forum_Controller_Show;
}