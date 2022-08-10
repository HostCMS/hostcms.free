<?php

// Запрещаем индексацию страницы
Core_Page::instance()->response->header('X-Robots-Tag', 'none');

$id = Core_Array::getGet('id', 0, 'int');
if (Core::moduleIsActive('advertisement') && $id)
{
	$oAdvertisement_Controller = Advertisement_Controller::instance()
		// Время хранения информации о показе
		->keep_days(3);

	$location = $oAdvertisement_Controller->getLocation($id);

	$advertisement_id = Core_Array::getGet('banner_id', 0, 'int');

	if (!$location && $advertisement_id)
	{
		$oAdvertisement = Core_Entity::factory('Advertisement')->find($advertisement_id);

		// Баннер найден
		!is_null($oAdvertisement) && $location = $oAdvertisement->href;
	}

	if ($location)
	{
		header('HTTP/1.0 301 Redirect');
		header('Location: ' . $location);
		exit();
	}
}

// 404 Not found
Core_Page::instance()->error404();