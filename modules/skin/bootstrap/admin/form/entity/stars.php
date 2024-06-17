<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Stars extends Skin_Default_Admin_Form_Entity_Input {

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_allowedProperties[] = 'step';
		$this->_allowedProperties[] = 'stars';

		$this->_skipProperties[] = 'step';
		$this->_skipProperties[] = 'stars';
		$this->_skipProperties[] = 'size';

		parent::__construct();

		$this->size('xs')
			->step(1)
			->stars(5);
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Stars.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Stars.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$oScript = Admin_Form_Entity::factory('Script')
			->value("$('#" . Core_Str::escapeJavascriptVariable($this->id) . "').rating({'stars':{$this->stars}, 'max':{$this->stars}, 'step':{$this->step}, 'size':'{$this->size}', 'showCaption': false, 'ratingClass':' rating-star', 'clearButtonTitle': '" . Core_Str::escapeJavascriptVariable(Core::_('Admin_Form.clear')) . "'});");

		$this->add($oScript);

		return parent::execute();

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}