<?php

$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::get(Core_Page::instance()->libParams, 'informationsystemId'));

$Informationsystem_Controller_Show = new Informationsystem_Controller_Show($oInformationsystem);

$Informationsystem_Controller_Show
	->limit($oInformationsystem->items_on_page)
	->parseUrl();

// При передаче данных методом GET /guestbook/?-????????????????-????-????????????/
count($_GET) && $Informationsystem_Controller_Show->error404();

Core_Page::instance()->object = $Informationsystem_Controller_Show;