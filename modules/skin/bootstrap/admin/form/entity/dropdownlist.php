<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Dropdown list.
 *
 * - options(array()) Массив значений
 * - value(string) Значение
 *
 * <code>
 * $oController->options(
 * 	array(
 * 		0 => array('value' => 'Default', 'ico' => 'fa fa-user', 'color' => '#eee'),
 * 		1 => array('value' => 'Second', 'ico' => 'fa fa-phone', 'color' => '#aaa'),
 * 		2 => 'Third',
 * )
 * );
 * </code>
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Dropdownlist extends Skin_Default_Admin_Form_Entity_Dropdownlist {}
