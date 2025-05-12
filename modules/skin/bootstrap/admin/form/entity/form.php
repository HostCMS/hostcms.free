<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Form extends Skin_Default_Admin_Form_Entity_Form {
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Form.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Form.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$this->action = Admin_Form_Controller::correctBackendPath($this->action);

		$aAttr = $this->getAttrsString();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		?><div id="box0">
		<form <?php echo implode(' ', $aAttr) ?>><?php
		$this->executeChildren();
		?></form>
		<?php
		if (!is_null($this->id))
		{
		?><script>$(function() {
			mainFieldChecker.checkAll('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>', "<?php echo htmlspecialchars((string) $this->id)?>"); });
			$.showAutosave($('#<?php echo $windowId?> form.adminForm'));
		</script><?php
		}
		?>
		</div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}