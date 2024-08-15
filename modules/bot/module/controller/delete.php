<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot.
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Bot_Module_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$id = intval($this->_object->id);

		if ($id)
		{
			$oBot_Module = Core_Entity::factory('Bot_Module')->getById($id);

			$sJSUpdateBotCounts = '';

			if (!is_null($oBot_Module))
			{
				$oBot_Modules = Core_Entity::factory('Bot_Module');

				$oBot_Modules->queryBuilder()
					->where('bot_modules.module_id', '=', $oBot_Module->module_id)
					->where('bot_modules.entity_id', '=', $oBot_Module->entity_id)
					->where('bot_modules.type', '=', $oBot_Module->type);

				$oBot_Module->delete();

				$countBotModules = $oBot_Modules->getCount(FALSE);

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$sJSUpdateBotCounts = "var oTabBots = $('#" . $windowId . "').closest('.tab-pane'), tabBotsId = oTabBots.attr('id'), tabBotsBadge = oTabBots.parent('.tab-content').prev().find('a[href=#' + tabBotsId + '] span.badge'); tabBotsBadge.length && tabBotsBadge.text('" . ($countBotModules ? $countBotModules : '') . "');";
			}

			$this->_Admin_Form_Controller->addMessage(
				"<script>$('#{$windowId} .bot-modules div#{$id}').parents('.dd').remove(); $.loadBotModuleNestable();" . $sJSUpdateBotCounts . "</script>"
			);
		}

		return TRUE;
	}
}