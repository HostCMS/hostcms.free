<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Import Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Import_Controller extends Core_Servant_Properties
{
	/**
	 * Convert url to Punycode
	 * @param string $url
	 * @return string
	 */
	protected function _convertToPunycode($url)
	{
		return preg_replace_callback('~(https?://)([^/]*)(.*)~', function($a) {
			$aTmp = array_map('rawurlencode', explode('/', $a[3]));

			return (preg_match('/[А-Яа-яЁё]/u', $a[2])
				? $a[1] . Core_Str::idnToAscii($a[2])
				: $a[1] . $a[2]
			) . implode('/', $aTmp);

			}, $url
		);
	}

	/**
	 * Download file to the TMP dir
	 * @param string $sSourceFile
	 * @return path to the file
	 */
	public function _downloadHttpFile($sSourceFile)
	{
		$sSourceFile = $this->_convertToPunycode($sSourceFile);

		$Core_Http = Core_Http::instance()
			->clear()
			->url($sSourceFile)
			->timeout(10)
			->addOption(CURLOPT_FOLLOWLOCATION, TRUE)
			->execute();

		$aHeaders = $Core_Http->parseHeaders();
		$sStatus = Core_Array::get($aHeaders, 'status');
		$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

		if ($iStatusCode != 200 || isset($aHeaders['Content-Type']) && strtolower(substr($aHeaders['Content-Type'], 0, 9)) == 'text/html')
		{
			throw new Core_Exception("Shop_Item_Import_Csv_Controller::_downloadHttpFile error, code: %code, Content-Type: %contentType.\nSource URL: %url",
				array('%code' => $iStatusCode, '%contentType' => Core_Array::get($aHeaders, 'Content-Type', 'unknown'), '%url' => $sSourceFile));
		}

		$content = $Core_Http->getDecompressedBody();

		// Файл из WEB'а, создаем временный файл
		$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");

		Core_File::write($sTempFileName, $content);

		return $sTempFileName;
	}
}