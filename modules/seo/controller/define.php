<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SEO.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Controller_Define extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oSeo_Controller = Seo_Controller::instance();
		$oSeo_Controller->requestSiteCharacteristics($this->_object);
		
		return $this;
	}
}