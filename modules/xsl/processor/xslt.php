<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSLT driver
 *
 * http://www.php.net/manual/en/class.domdocument.php
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Processor_Xslt extends Xsl_Processor
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

		// Load the xml file and stylesheet as domdocuments
		$xsl = new DomDocument();
		$xsl->substituteEntities = TRUE;
		$xsl->resolveExternals = TRUE;

		$sXsl = $this->_xsl->loadXslFile();

		!Core::checkPanel() && $sXsl = $this->_clearXmlns($sXsl);

		if ($xsl->loadXML($sXsl))
		{
			$inputdom = new DomDocument();

			if ($inputdom->loadXML($this->_xml, defined('LIBXML_PARSEHUGE') ? LIBXML_PARSEHUGE : 0))
			{
				// Create the processor and import the stylesheet
				$XsltProcessor = new XsltProcessor();

				$XsltProcessor->registerPHPFunctions();
				$XsltProcessor->importStylesheet($xsl);
				$XsltProcessor->setParameter(NULL, "titles", "Titles");

				libxml_use_internal_errors(TRUE);
				// Transform and output the xml document
				$newdom = $XsltProcessor->transformToDoc($inputdom);

				foreach (libxml_get_errors() as $error)
				{
					// Bug with libxml 2.9.2 + edit-in-place = error with same IDs
					if ($error->code != 513)
					{
						echo "Libxml error {$error->code}: <strong>{$error->message}</strong>, Line: {$error->line}\n";
					}
				}

				libxml_clear_errors();
				libxml_use_internal_errors(FALSE);

				if ($newdom)
				{
					$newdom->formatOutput = !is_null($this->_formatOutput)
						? $this->_formatOutput
						: Core_Array::get(Core::$config->get('xsl_config'), 'formatOutput', TRUE);

					$content = $newdom->saveXML();

					$return = $this->_deleteXmlHeader($content);
				}
			}
		}

		Core_Event::notify('Xsl_Processor.onAfterProcess', $this);

		return $return;
	}
}
