<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core_Templater
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			->addFunction('year', array('Core_Templater', 'year'));
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
	 * @return self
	 */
	static public function decorateInput($oInput)
	{
		$divId = htmlspecialchars($oInput->id . '_template');

		$oInput
			->add(Core::factory('Core_Html_Entity_Code')
				->value('
					<div class="input-group-btn">
						<div id="' . $divId . '" class="margin-left-10">
							<a href="javascript:void(0)" data-content="{day}" class="btn btn-success btn-xs">' . Core::_('Core.day') . '</a>
							<a href="javascript:void(0)" data-content="{month}" class="btn btn-info btn-xs margin-left-5">' . Core::_('Core.month') . '</a>
							<a href="javascript:void(0)" data-content="{year}" class="btn btn-warning btn-xs margin-left-5">' . Core::_('Core.year') . '</a>
							<a href="javascript:void(0)" data-content="{rand(10000,99999)}" class="btn btn-danger btn-xs margin-left-5">' . Core::_('Core.random') . '</a>
						</div>
					</div>

					<script>
					$(function(){


						$("#'. $divId  .' a[data-content]").on("click", function(){
							var oInput = $(this).parents(".input-group").find("input#' . htmlspecialchars($oInput->id) . '");

							oInput.val(oInput.val() + $(this).data("content"));
						});
					});
					</script>
				')
			);
	}
}