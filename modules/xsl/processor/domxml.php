<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * DOM XML (PHP 4) driver
 *
 * http://www.php.net/manual/en/book.domxml.php
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Processor_DomXml extends Xsl_Processor
{
	/**
	 * Execute processor
	 * @return mixed
	 * @hostcms-event Xsl_Processor.onBeforeProcess
	 * @hostcms-event Xsl_Processor.onAfterProcess
	 */
	public function process()
	{
		Core_Event::notify('Xsl_Processor.onBeforeProcess', $this);

		$return = NULL;

		$xmlData = $this->_xml;
		$sXsl = $this->_xsl->loadXslFile();

		if (!Core::checkPanel())
		{
			$sXsl = $this->_clearXmlns($sXsl);
		}
		
		$xmldoc = domxml_open_mem($xmlData);
		if ($xmldoc)
		{
			$xsldoc = domxml_xslt_stylesheet($sXsl);
			if ($xsldoc)
			{
				$return = $xsldoc->process($xmldoc)->dump_mem();
			}
		}

		Core_Event::notify('Xsl_Processor.onAfterProcess', $this);

		return $return;
	}
}