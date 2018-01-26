<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Create an RSS 2.0 feed
 *
 * @package HostCMS
 * @subpackage Core\Rss
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		$this->_xmlns[] = 'xmlns:' . $name . '="' . htmlspecialchars($value) . '"';
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

			// if isset namespace
			$newChild = isset($aTmp[1])
				? $object->addChild($name, !is_array($aSubitem['value']) ? $aSubitem['value'] : NULL, $aTmp[0])
				: $object->addChild($name, !is_array($aSubitem['value']) ? $aSubitem['value'] : NULL);

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
			->compress()
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
		$oRss = simplexml_load_string('<?xml version="1.0" encoding="' . $this->_encoding . '"?>' .
			'<rss version="2.0"' . (
				count($this->_xmlns)
					? ' ' . implode(' ', $this->_xmlns)
					: ''
				) . '>' .
			'<channel></channel>' .
			'</rss>');

		$this->_addChild($oRss->channel, $this->_entities);

		// $xml = $oRss->asXML();
		$dom = dom_import_simplexml($oRss)->ownerDocument;
		$dom->formatOutput = TRUE;
		$xml = $dom->saveXML();

		return $xml;
	}
}