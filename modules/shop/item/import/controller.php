<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Import Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Item_Import_Controller extends Core_Servant_Properties
{
	/**
	 * Download file to the TMP dir
	 * @param string $sSourceFile
	 * @return string path to the file
	 */
	public function _downloadHttpFile($sSourceFile)
	{
		$sSourceFile = Core_Http::convertToPunycode($sSourceFile);

		$Core_Http = Core_Http::instance()
			->clear()
			->url($sSourceFile)
			->timeout(10)
			->addOption(CURLOPT_FOLLOWLOCATION, TRUE)
			->execute();

		$aHeaders = $Core_Http->parseHeaders();
		$sStatus = Core_Array::get($aHeaders, 'status');
		$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

		$contentType = isset($aHeaders['Content-Type'])
			? strtolower(substr(is_array($aHeaders['Content-Type']) ? $aHeaders['Content-Type'][0] : $aHeaders['Content-Type'], 0, 9))
			: 'unknown';

		if ($iStatusCode != 200 || $contentType == 'text/html')
		{
			throw new Core_Exception("Shop_Item_Import_Csv_Controller::_downloadHttpFile error, code: %code, Content-Type: %contentType.\nSource URL: %url",
				array('%code' => $iStatusCode, '%contentType' => $contentType, '%url' => $sSourceFile));
		}

		$content = $Core_Http->getDecompressedBody();

		// Файл из WEB'а, создаем временный файл
		$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");

		Core_File::write($sTempFileName, $content);

		return $sTempFileName;
	}
}