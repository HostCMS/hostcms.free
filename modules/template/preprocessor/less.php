<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * LESS Preprocessor
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Preprocessor_Less extends Template_Preprocessor
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		require_once(CMS_FOLDER . '/modules/vendor/lessphp/lessc.inc.php');

		$this->_preprocessor = new lessc();
		$this->_preprocessor->setImportDir(array(CMS_FOLDER));
	}

	/**
	 * Compile
	 * @param string $content
	 * @return string
	 */
	public function compile($content)
	{
		return $this->_preprocessor->compile($content);
	}
}