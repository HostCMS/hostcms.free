<?php

/**
 * HostCMS captcha.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

require_once ('bootstrap.php');

$captchaId = Core_Array::getGet('id', Core_Array::getGet('get_captcha', 0, 'str'), 'str');

if ($captchaId != ''
	&& !preg_match('/http|bot|spide|craw|yandex|seach|seek|site|sogou|yahoo|msnbot|google|bing/iu', Core_Array::get($_SERVER, 'HTTP_USER_AGENT', ''))
)
{
	$Core_Captcha = new Core_Captcha();

	$width = Core_Array::getGet('width', 0, 'int');
	$height = Core_Array::getGet('height', 0, 'int');

	$width >= 50 && $width <= 100 && $Core_Captcha->setConfig('width', $width);
	$height >= 10 && $height <= 50 && $Core_Captcha->setConfig('height', $height);

	$Core_Captcha->build($captchaId);
}