<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

require_once(CMS_FOLDER . '/modules/vendor/PHPOffice/autoload.php');
require_once(CMS_FOLDER . '/modules/vendor/zendframework/zend-escaper/Escaper.php');

use PhpOffice\PhpWord\PhpWord;
use Zend\Escaper;

/**
 * Printlayout_Driver_Html
 *
 * @package HostCMS 6
 * @subpackage Printlayout
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Driver_Html extends Printlayout_Driver_Controller
{
	protected $_extension = 'html';

	protected $_filePath = NULL;

	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		\PhpOffice\PhpWord\Settings::setTempDir(CMS_FOLDER . TMP_DIR);
		$phpWord = \PhpOffice\PhpWord\IOFactory::load($this->_sourceDocx);
		$htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

		$docProps = $phpWord->getDocInfo();
		$docProps->setTitle($this->_title);

		$this->_filePath = tempnam(CMS_FOLDER . TMP_DIR, 'HTM');
		$htmlWriter->save($this->_filePath);

		Core_File::delete($this->_sourceDocx);

		return $this;
	}

	/**
	 * Get file
	 * @return string
	 */
	public function getFile()
	{
		return $this->_filePath;
	}

	/**
	 * Check available
	 */
	public function available()
	{
		return TRUE;
	}
}