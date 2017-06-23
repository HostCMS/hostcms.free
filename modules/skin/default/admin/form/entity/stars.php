<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Stars extends Skin_Default_Admin_Form_Entity_Select {

	public function execute()
	{
		$this
			->options(
				array(
					1 => 'Poor',
					2 => 'Fair',
					3 => 'Average',
					4 => 'Good',
					5 => 'Excellent',
				)
			);
		return parent::execute();
	}
}