<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SCSS Preprocessor
 *
 * @package HostCMS 6
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Preprocessor_Scss extends Template_Preprocessor
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		require_once(CMS_FOLDER . '/modules/vendor/scssphp/scss.inc.php');

		$this->_preprocessor = new ScssPhp\ScssPhp\Compiler();
		$this->_preprocessor->setImportPaths(CMS_FOLDER);
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