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
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Form.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Form.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$aAttr = $this->getAttrsString();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		?><div id="box0">
		<form <?php echo implode(' ', $aAttr) ?>><?php
		$this->executeChildren();
		?></form>
		<script>
		$(function() {
			var jForm = $("#<?php echo Core_Str::escapeJavascriptVariable($windowId)?> #<?php echo htmlspecialchars((string) $this->id)?>");
			if (jForm.length > 0)
			{
				jForm.css('display', 'none');
				// fix bug t.win.document has no properties
				window.setTimeout(function() {
					$.showTab('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>', 'tab_page_0');
					jForm.css('display', 'block');
				}, 500);
			}

			mainFieldChecker.checkAll('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>', "<?php echo htmlspecialchars((string) $this->id)?>");
		});
		</script>
		</div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}