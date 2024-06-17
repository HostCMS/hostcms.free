<?php
/**
 * Calendar
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
return array(
	'model_name' => 'Calendar settings',
	'title' => 'Settings',
	'id' => 'ID',
	'name' => 'Name',
	'icon' => 'Icon',
	'driver' => 'Driver',
	'active' => 'Active',
	'sorting' => 'Sort',
	'add_title' => 'Add calendar settings',
	'edit_title' => 'Edit calendar settings',
	'edit_success' => "Driver settings added successfully!",
	'changeActive_success' => 'Status changed successfully!',
	'google' => 'После регистрации приложения, необходимо получить access token. Перейдите по <a href="https://accounts.google.com/o/oauth2/auth?response_type=code&client_id=%1$s&approval_prompt=force&access_type=offline&scope=https://www.googleapis.com/auth/calendar&redirect_uri=%2$s" target="_blank">cсылке</a>, подтвердите права, и после того как вернет на сайт, скопируйте адрес из адресной строки браузера в поле Access Token. После сохранения токен будет выделен из адреса.',
	'color' => 'Color'
);