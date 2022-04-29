<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core_Templater
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Templater extends Core_Meta
{
	/**
	 * Template string
	 * @var string
	 */
	protected $_template = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this
			->addFunction('day', array('Core_Templater', 'day'))
			->addFunction('month', array('Core_Templater', 'month'))
			->addFunction('year', array('Core_Templater', 'year'))
			->addFunction('generateChars', array('Core_Str', 'generateChars'));
	}

	/**
	 * Get current day
	 * @return string
	 */
	static public function day()
	{
		return date('d');
	}

	/**
	 * Get current month
	 * @return string
	 */
	static public function month()
	{
		return date('m');
	}

	/**
	 * Get current year
	 * @return string
	 */
	static public function year()
	{
		return date('Y');
	}

	/**
	 * Set template number string
	 * @param string $string
	 * @return self
	 */
	public function setTemplate($string)
	{
		$this->_template = $string;
		return $this;
	}

	/**
	 * Get template number string
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->_template;
	}

	/*
	 * Execute business logic
	 */
	public function execute()
	{
		return $this->apply($this->_template);
	}

	/**
	 * Decorate template number input
	 * @param object $oInput
	 * @param array $aOptions
	 * @return self
	 */
	static public function decorateInput($oInput, array $aOptions = array())
	{
		$divId = htmlspecialchars($oInput->id . '_template');

		if (!count($aOptions))
		{
			$aOptions = array(
				'{day}' => array(
					'caption' => Core::_('Core.day'),
					'color' => 'success'
				),
				'{month}' => array(
					'caption' => Core::_('Core.month'),
					'color' => 'info'
				),
				'{year}' => array(
					'caption' => Core::_('Core.year'),
					'color' => 'warning'
				),
				'{rand(10000,99999)}' => array(
					'caption' => Core::_('Core.random'),
					'color' => 'danger'
				),
				'{generateChars(7)}' => array(
					'caption' => Core::_('Core.generateChars'),
					'color' => 'maroon'
				)
			);
		}

		$sData = '<div class="input-group-btn">
			<div id="' . $divId . '" class="margin-left-10">';

		$i = 0;

		foreach ($aOptions as $key => $aParam)
		{
			$bMargin = $i ? 'margin-left-5' : '';

			$sData .= '<a href="javascript:void(0)" data-content="' . $key . '" class="btn btn-' . $aParam['color'] . ' btn-xs ' . $bMargin . '">' . $aParam['caption'] . '</a>';

			$i++;
		}

		$sData .= '</div>
			</div>
			<script>$(function(){
				$("#' . $divId . ' a[data-content]").on("click", function(){
					var oInput = $(this).parents(".input-group").find("input#' . htmlspecialchars($oInput->id) . '");
					oInput.val(oInput.val() + $(this).data("content"));
				});
			});</script>';

		$oInput->add(Core_Html_Entity::factory('Code')->value($sData));
	}
}