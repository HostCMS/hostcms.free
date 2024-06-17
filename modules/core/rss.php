<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Create an RSS 2.0 feed
 *
 * @package HostCMS
 * @subpackage Core\Rss
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Rss
{
	/**
	 * Encoding
	 * @var string
	 */
	protected $_encoding = 'UTF-8';

	/**
	 * XMLNS
	 * @var array
	 */
	protected $_xmlns = array();

	/**
	 * Set XMLNS value
	 * @param string $name name
	 * @param string $value value
	 * @return self
	 */
	public function xmlns($name, $value)
	{
		//$this->_xmlns[] = 'xmlns:' . $name . '="' . htmlspecialchars($value) . '"';
		$this->_xmlns[$name] = $value;
		return $this;
	}

	/**
	 * Entities list
	 * @var array
	 */
	protected $_entities = array();

	/**
	 * Add entity.
	 *
	 * @param string $name entity name
	 * @param string $value entity value
	 * @param string $attributes array attributes
	 * @return self
	 */
	public function add($name, $value, array $attributes = array())
	{
		$this->_entities[] = array(
			'name' => $name,
			'value' => $value,
			'attributes' => $attributes
		);

		return $this;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this
			->add('pubDate', date('r'))
			->add('generator', 'HostCMS');
	}

	/**
	 * Add children nodes
	 * @param object $object object
	 * @param array $children children nodes
	 * @return self
	 */
	protected function _addChild($object, array $children)
	{
		foreach ($children as $aSubitem)
		{
			$name = $aSubitem['name'];

			$aTmp = explode(':', $name);

			$sTmpValue = !is_array($aSubitem['value']) ? $aSubitem['value'] : NULL;
			$bCDATA = isset($aSubitem['CDATA']) && $aSubitem['CDATA'];

			// if isset namespace
			$newChild = isset($aTmp[1])
				? $object->addChild($name, $bCDATA ? NULL : $sTmpValue, isset($this->_xmlns[$aTmp[0]])
					? $this->_xmlns[$aTmp[0]]
					: $aTmp[0]
				)
				: $object->addChild($name, $bCDATA ? NULL : $sTmpValue);

			if ($bCDATA)
			{
				$domNewChild = dom_import_simplexml($newChild);
				$domNewChildOwner = $domNewChild->ownerDocument;
				$domNewChild->appendChild($domNewChildOwner->createCDATASection($sTmpValue));
			}

			if (isset($aSubitem['attributes']))
			{
				foreach ($aSubitem['attributes'] as $attrName => $attrValue)
				{
					$newChild->addAttribute($attrName, $attrValue);
				}
			}

			if (isset($aSubitem['value']) && is_array($aSubitem['value']))
			{
				foreach ($aSubitem['value'] as $key => $value)
				{
					$this->_addChild($newChild, array(
						is_array($value) && isset($value['name'])
							? $value + array('value' => NULL, 'attributes' => array())
							: array(
								'name' => $key,
								'value' => $value,
								'attributes' => array()
							)
					));
				}
			}
		}

		return $this;
	}

	/**
	 * Show RSS with headers
	 * @param string $rss content
	 */
	public function showWithHeader($rss)
	{
		$oCore_Response = new Core_Response();

		$oCore_Response
			->status(200)
			->header('Content-Type', 'text/xml; charset=' . $this->_encoding)
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS');

		$oCore_Response
			->body($rss)
			->sendHeaders()
			->showBody();
	}

	/**
	 * Show RSS
	 * @return void
	 */
	public function show()
	{
		$this->showWithHeader($this->get());
	}

	/**
	 * Get RSS
	 * @return string
	 */
	public function get()
	{
		$aXmlns = array();
		foreach ($this->_xmlns as $name => $url)
		{
			$aXmlns[] = 'xmlns:' . $name . '="' . htmlspecialchars($url) . '"';
		}
		
		$oRss = simplexml_load_string('<?xml version="1.0" encoding="' . $this->_encoding . '"?>' .
			'<rss version="2.0"' . (
				count($this->_xmlns)
					? ' ' . implode(' ', $aXmlns)
					: ''
				) . '>' .
			'<channel></channel>' .
			'</rss>');
			
		$this->_addChild($oRss->channel, $this->_entities);

		// $xml = $oRss->asXML();
		$dom = dom_import_simplexml($oRss)->ownerDocument;
		$dom->formatOutput = TRUE;
		//$xml = $dom->saveXML();
		$xml = $dom->saveXML(NULL, LIBXML_NOEMPTYTAG);

		return $xml;
	}
}