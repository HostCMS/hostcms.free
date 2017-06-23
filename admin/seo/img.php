<?php 
/**
 * SEO.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization('seo');

$id_return = intval(Core_Array::getGet('id_report', 0));

$start_datetime = Core_Array::getGet('start_datetime', Core_Date::timestamp2datetime(time() - 2678400));
$end_datetime = Core_Array::getGet('end_datetime', Core_Date::timestamp2datetime(time()));

if ($id_return != 4)
{
	$site_id = intval(Core_Array::getGet('site_id', CURRENT_SITE));

	$aSeo = Core_Entity::factory('Site', $site_id)->Seos->getByDatetime($start_datetime, $end_datetime);

	// Кол-во элементов массива
	$count = count($aSeo);
}

/**
 * Построение графика
 *
 * @param array $report Массив значений
 * @param int $query_id Идентификатор поискового запроса
 * @param int $id_return Идентификатор данных, для которых строится график
 * - $id_return = 0 Пустой рисунок
 * - $id_return = 1 ТИЦ
 * - $id_return = 2 Ссылающиеся страницы
 * - $id_return = 3 Проиндексированно страниц
 * - $id_return = 4 Позиции по поисковым запросам
 * - $id_return = 5 PR
 * @param str $select_pos_search Строка запроса
 * @param array $param_array Массив дополнительных параметров
 * - str $param_array['date_start'] Дата начала отчетного периода
 * - str $param_array['date_end'] Дата окончания отчетного периода
 * @return bool
 */

$oCore_Diagram = new Core_Diagram();

$data = array();
$legend = array();
$param = array();
$abscissa = array();

switch ($id_return)
{
	default:
	case 0:
		// Нарисуем пустое изображение
		$im = imagecreate(100, 100);

		$white = imagecolorallocate($im, 255, 255, 255);
		imagefill($im, 0, 0, $white);

		// Генерация изображения
		ImagePNG($im);
		imagedestroy($im);

		break;
	case 1:
		// тИЦ
		foreach ($aSeo as $oSeo)
		{
			$data[0][] = $oSeo->tcy;
			$abscissa[] = Core_Date::sql2date($oSeo->datetime);
			$legend[] = Core::_('Seo.tcy');
		}

		break;
	case 2:
		$google_links = intval(Core_Array::getGet('google_links', 0));
		$yandex_links = intval(Core_Array::getGet('yandex_links', 0));
		//$yahoo_links = intval(Core_Array::getGet('yahoo_links', 0));
		$bing_links = intval(Core_Array::getGet('bing_links', 0));

		foreach ($aSeo as $oSeo)
		{
			$i = 0;

			// По данным Google
			if ($google_links)
			{
				$data[$i][] = $oSeo->google_links;
				$legend[$i++] = Core::_('Seo.google');
			}

			// По данным Yandex
			if ($yandex_links)
			{
				$data[$i][] = $oSeo->yandex_links;
				$legend[$i++] = Core::_('Seo.yandex');
			}

			// По данным Yahoo
			/*if ($yahoo_links)
			{
				$data[$i][] = $oSeo->yahoo_links;
				$legend[$i++] = Core::_('Seo.yahoo');
			}*/

			// По данным Bing.com
			if ($bing_links)
			{
				$data[$i][] = $oSeo->bing_links;
				$legend[$i++] = Core::_('Seo.bing');
			}

			// Дата
			$abscissa[] = Core_Date::sql2date($oSeo->datetime);
		}
		break;
	case 3:
		$yandex_indexed = intval(Core_Array::getGet('yandex_indexed', 0));
		$yahoo_indexed = intval(Core_Array::getGet('yahoo_indexed', 0));
		$bing_indexed = intval(Core_Array::getGet('bing_indexed', 0));
		//$rambler_indexed = intval(Core_Array::getGet('rambler_indexed', 0));
		$google_indexed = intval(Core_Array::getGet('google_indexed', 0));

		foreach ($aSeo as $oSeo)
		{
			$i = 0;

			// Сервисом Yandex
			if ($yandex_indexed)
			{
				$data[$i][] = $oSeo->yandex_indexed;
				$legend[$i++] = Core::_('Seo.yandex');
			}

			// Сервисом Rambler
			/*if ($rambler_indexed)
			{
				$data[$i][] = $oSeo->rambler_indexed;
				$legend[$i++] = Core::_('Seo.rambler');
			}*/

			// Сервисом Google
			if ($google_indexed)
			{
				$data[$i][] = $oSeo->google_indexed;
				$legend[$i++] = Core::_('Seo.google');
			}

			// Сервисом Yahoo
			if ($yahoo_indexed)
			{
				$data[$i][] = $oSeo->yahoo_indexed;
				$legend[$i++] = Core::_('Seo.yahoo');
			}

			// Сервисом Bing.com
			if ($bing_indexed)
			{
				$data[$i][] = $oSeo->bing_indexed;
				$legend[$i++] = Core::_('Seo.bing');
			}

			// Дата
			$abscissa[] = Core_Date::sql2date($oSeo->datetime);
		}
		break;
	case 4:
		$yandex = intval(Core_Array::getGet('yandex_position', 0));
		$rambler = intval(Core_Array::getGet('rambler_position', 0));
		$google = intval(Core_Array::getGet('google_position', 0));
		$yahoo = intval(Core_Array::getGet('yahoo_position', 0));
		$bing = intval(Core_Array::getGet('bing_position', 0));

		$seo_query_id = intval(Core_Array::getGet('seo_query_id', 0));

		$aSeo_Query_Positions = Core_Entity::factory('Seo_Query', $seo_query_id)
			->Seo_Query_Positions
			->getByDatetime($start_datetime, $end_datetime);

		$count = count($aSeo_Query_Positions);

		// Позиции по поисковым запросам
		if ($count)
		{
			$j = 0;

			foreach ($aSeo_Query_Positions as $oSeo_Query_Position)
			{
				$i = 0;

				if ($yandex)
				{
					$data[$i][] = $oSeo_Query_Position->yandex;
					$legend[$i++] = Core::_('Seo.yandex');
				}

				if ($rambler)
				{
					$data[$i][] = $oSeo_Query_Position->rambler;
					$legend[$i++] = Core::_('Seo.rambler');
				}

				if ($google)
				{
					$data[$i][] = $oSeo_Query_Position->google;
					$legend[$i++] = Core::_('Seo.google');
				}

				if ($yahoo)
				{
					$data[$i][] = $oSeo_Query_Position->yahoo;
					$legend[$i++] = Core::_('Seo.yahoo');
				}

				if ($bing)
				{
					$data[$i][] = $oSeo_Query_Position->bing;
					$legend[$i++] = Core::_('Seo.bing');
				}

				$abscissa[] = Core_Date::sql2date($oSeo_Query_Position->datetime);
			}

			$oCore_Diagram->inversion(TRUE);
		}
		else
		{
			return false;
		}

		break;
	case 5:
		// PageRank
		foreach ($aSeo as $oSeo)
		{
			$data[0][] = $oSeo->pr;
			$abscissa[] = Core_Date::sql2date($oSeo->datetime);
			$legend[] = Core::_('Seo.pr');
		}
		break;
}

if (isset($count) && $count)
{
	// Строим график
	$oCore_Diagram
		->abscissa($abscissa)
		->legend($legend)
		->values($data)
		->showPoints(TRUE)
		->showOrigin(TRUE)
		->lineChart();
}
else
{
	$oCore_Diagram->emptyImage();
}

return true;