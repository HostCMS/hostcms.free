<?php 
if (Core::moduleIsActive('poll'))
{
	$oPoll_Group = Core_Entity::factory('Poll_Group', Core_Page::instance()->libParams['pollGroupId']);

	$Poll_Group_Controller_Show = new Poll_Group_Controller_Show($oPoll_Group);

	$Poll_Group_Controller_Show
		->limit(Core_Page::instance()->libParams['count'])
		->parseUrl();

	if ($Poll_Group_Controller_Show->poll)
	{
		$oPoll = Core_Entity::factory('Poll', $Poll_Group_Controller_Show->poll);

		Core_Page::instance()->title($oPoll->name);
		Core_Page::instance()->description($oPoll->name);
		Core_Page::instance()->keywords($oPoll->name);
	}

	Core_Page::instance()->object = $Poll_Group_Controller_Show;
}