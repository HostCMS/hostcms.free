<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Stars extends Skin_Default_Admin_Form_Entity_Input {

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_allowedProperties[] = 'step';
		$this->_allowedProperties[] = 'stars';

		$this->_skipProperies[] = 'step';
		$this->_skipProperies[] = 'stars';
		$this->_skipProperies[] = 'size';

		parent::__construct();

		$this->size('xs')
			->step(1)
			->stars(5);
	}

	public function execute()
	{
		$oScript = Admin_Form_Entity::factory('Script')
			->value("$('#" . $this->id . "').rating({'stars':{$this->stars}, 'max':{$this->stars}, 'step':{$this->step}, 'size':'{$this->size}', 'showCaption': false, 'ratingClass':' rating-star', 'clearButtonTitle': '" . Core::_('Admin_Form.clear') . "'});");

		$this->add($oScript);

		return parent::execute();
	}
}