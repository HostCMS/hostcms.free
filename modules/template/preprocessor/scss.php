<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SCSS Preprocessor
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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