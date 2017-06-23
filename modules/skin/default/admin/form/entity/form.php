<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Form extends Admin_Form_Entity
{
	/**
	 * Constructor.
	 * @param Admin_Form_Controller $oAdmin_Form_Controller controller
	 */
	public function __construct(Admin_Form_Controller $oAdmin_Form_Controller = NULL)
	{
		$this->controller($oAdmin_Form_Controller);

		$oCore_Html_Entity_Form = new Core_Html_Entity_Form();
		$this->_allowedProperties += $oCore_Html_Entity_Form->getAllowedProperties();

		parent::__construct();

		$this->id = $this->name = 'Form' . rand(0, 99999);
		$this->method = 'post';
		$this->enctype = 'multipart/form-data';
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Warning: fieldType, fieldMessage, fieldsStatus (ниже !) переделать на JS-класс + заполнение в модели input-а
		?><div id="box0">
		<script type="text/javascript">
		var fieldType = new Array(), fieldMessage = new Array(), fieldsStatus = new Array();
		</script>
		<form <?php echo implode(' ', $aAttr) ?>><?php
		
		$this->executeChildren();
		
		?></form>
		<script type="text/javascript">
		$(function() {
		var jForm = $("#<?php echo $windowId?> #<?php echo htmlspecialchars($this->id)?>");
		if (jForm.length > 0)
		{
			jForm.css('display', 'none');
			// fix bug t.win.document has no properties
			window.setTimeout(function() {
				$.showTab('<?php echo $windowId?>', 'tab_page_0');
				jForm.css('display', 'block');
			}, 500);
		}
		CheckAllField('<?php echo $windowId?>', "<?php echo htmlspecialchars($this->id)?>");
		});
		</script>
		</div><?php
	}
}