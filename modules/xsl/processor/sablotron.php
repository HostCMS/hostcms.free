<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sablotron XSLT driver
 *
 * 
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */ 
class Xsl_Processor_Sablotron extends Xsl_Processor
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
		
		$xmlData = $this->_xml;
		$sXsl = $this->_xsl->loadXslFile();

		if (!Core::checkPanel())
		{
			$sXsl = $this->_clearXmlns($sXsl);
		}
		
		$xh = xslt_create();

		$arguments = array('/_xml' => $xmlData, '/_xsl' => $sXsl);

		if (function_exists('xslt_set_encoding'))
		{
			xslt_set_encoding($xh, 'UTF-8');
		}

		$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);

		if (!$result)
		{
			throw new Core_Exception("XSLT error '%errno': %error.",
				array(
					'%error' => xslt_error($xh),
					'%errno' => xslt_errno($xh)
				),
				xslt_errno($xh)
				);
		}
		
		Core_Event::notify('Xsl_Processor.onAfterProcess', $this);
		
		return $result;
	}
}