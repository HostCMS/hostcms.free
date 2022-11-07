<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot_Controller_View.
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Bot_Controller_View extends Admin_Form_Controller_View
{
	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_View = Admin_View::create($this->_Admin_Form_Controller->Admin_View)
			->pageTitle($oAdmin_Form_Controller->pageTitle)
			->module($oAdmin_Form_Controller->module);

		$oUser = Core_Auth::getCurrentUser();

		// При показе формы могут быть добавлены сообщения в message, поэтому message показывается уже после отработки формы
		ob_start();

		$this->_showContent();

		$content = ob_get_clean();

		$oAdmin_View
			->content($content)
			->message($oAdmin_Form_Controller->getMessage())
			->show();

		return $this;
	}

	/**
	 * Show form content in administration center
	 * @return self
	 */
	protected function _showContent()
	{
		// $iEntityId = intval(Core_Array::getGet('entity_id'));

		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

		$aAdmin_Form_Fields = $oAdmin_Form->Admin_Form_Fields->findAll();

		$oSortingField = $oAdmin_Form_Controller->getSortingField();

		$oUser = Core_Auth::getCurrentUser();

		if (is_null($oUser))
		{
			return FALSE;
		}

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller
			->limit(999)
			->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		// Bot modules
		$aEntities = $aDatasets[0]->load();
		?>
		<script>
		$(function() {
			$.extend({
				loadBotModuleNestable: function()
				{
					var aScripts = [
						'jquery.nestable.min.js'
					];

					$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/nestable/').done(function() {
						$('#<?php echo $windowId?> .bot-modules .dd').nestable({
							maxDepth: 1,
							emptyClass: ''
						});

						$('#<?php echo $windowId?> .bot-modules .dd-handle a')
							.on('mousedown', function (e) {
								e.stopPropagation();
							})
							.on('touchend', function () {
								$(this).click();
							});

						$bChange = true;

						$('#<?php echo $windowId?> .bot-modules .dd').on('change', function() {
							$aIds = [];

							$.each($('#<?php echo $windowId?> .bot-modules li.dd-item'), function(i, object){
								$aIds.push($(object).data('id'));
							});

							if ($bChange)
							{
								$.ajax({
									url: '/admin/bot/module/index.php',
									data: { 'save_sorting': 1, 'aIds': $aIds },
									dataType: 'json',
									type: 'POST',
									success: function(result){}
								});

								$bChange = false;
							}
						});
					});
				},
				showBotModuleSettings: function(bot_module_id)
				{
					$.ajax({
						url: '/admin/bot/module/index.php',
						data: { 'show_settings': 1, 'bot_module_id': bot_module_id },
						dataType: 'json',
						type: 'POST',
						success: function(result){
							if (result.status == 'success')
							{
								$('#<?php echo $windowId?> li[data-id = ' + bot_module_id + ']').append(result.html);

								//$('#<?php echo $windowId?> #settingsModal' + bot_module_id).modal('show');

								//$('#<?php echo $windowId?> #settingsModal' + bot_module_id).on('hide.bs.modal', function (event) {

								$('#<?php echo $windowId?> #settingsModal' + bot_module_id)
									.modal('show')
									.on('hide.bs.modal', function (event) {

										var triggerReturn = $('body').triggerHandler('beforeHideModal');

										if (triggerReturn == 'break')
										{
											event.preventDefault();
										}
										else
										{
											$('#<?php echo $windowId?> #settingsModal' + bot_module_id)
												.removeTinyMCE()
												.remove();
										}

									});
							}
						}
					});
				},
				saveBotModuleSettings: function(bot_module_id, module_id, entity_id, type)
				{
					$.ajax({
						url: '/admin/bot/module/index.php',
						data: { 'save_settings': 1, 'data': $('#<?php echo $windowId?> .bot-modules-form').serialize(), 'bot_module_id': bot_module_id },
						dataType: 'json',
						type: 'POST',
						success: function(result){
							$('#<?php echo $windowId?> #settingsModal' + bot_module_id).modal('hide');

							// Reload list
							$.adminLoad({ path: '/admin/bot/module/index.php', additionalParams: 'entity_id=' + entity_id + '&module_id=' + module_id + '&type=' + type + '&hideMenu=1&_module=0', windowId: 'bots-container', loadingScreen: false });
						}
					});
				}
			});

			$.loadBotModuleNestable();
		});
		</script>
		<div class="row">
			<div class="form-group col-xs-12">
				<div class="dropdown">
					<a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">
						<?php echo Core::_('Admin_Form.add')?> <span class="caret"></span>
					</a>
					<?php echo $this->_showBotsTree()?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div class="well well-sm margin-bottom-10 bot-modules">
					<p class="semi-bold"><i class="widget-icon fa fa-list icon-separator palegreen"></i><?php echo Core::_('Bot.bot_modules')?></p>

					<?php
					foreach ($aEntities as $oBot_Module)
					{
						$oBot = $oBot_Module->Bot;

						$onclick = $this->_Admin_Form_Controller->getAdminActionLoadAjax('/admin/bot/module/index.php', 'deleteModule', NULL, 0, $oBot_Module->id);

						$sParents = $oBot->bot_dir_id
							? $oBot->Bot_Dir->dirPathWithSeparator() . ' → '
							: '';

						// Создаем класс бота
						$oClass = new $oBot->class();
						?>
							<div class="dd">
								<ol class="dd-list">
									<li class="dd-item bordered-default" style="border-color: <?php echo htmlspecialchars($oClass->getColor())?> !important;" data-id="<?php echo $oBot_Module->id?>" data-sorting="<?php echo $oBot_Module->sorting?>">
										<input type="checkbox" id="check_0_<?php echo $oBot_Module->id?>" class="hidden">
										<div class="dd-handle">
											<div id="<?php echo $oBot_Module->id?>" class="form-horizontal">
												<div class="form-group no-margin-bottom">
													<label for="bot_module<?php echo $oBot_Module->id?>" class="col-xs-12 col-sm-8 control-label text-align-left"><?php echo $sParents?><b><?php echo htmlspecialchars($oBot->name)?></b></label>
													<div class="col-xs-12 col-sm-3"><span class="<?php echo $oBot_Module->getDeadlineClass()?>"><?php echo $oBot_Module->getDeadline()?></span></div>

													<div class="bot-actions">
														<a class="show-settings" onclick="$.showBotModuleSettings(<?php echo $oBot_Module->id?>)"><i class="fa fa-cog azure"></i></a>
														<a class="delete-associated-item margin-left-5" onclick="<?php echo $onclick?>"><i class="fa fa-times-circle darkorange"></i></a>
													</div>
												</div>
											</div>
										</div>
									</li>
								</ol>
							</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php
		return $this;
	}

	/**
	 * Array of Bots tree
	 * @var array
	 */
	protected $_aBots = array();

	/**
	 * Array of Bot_Dirs tree
	 * @var array
	 */
	protected $_aBot_Dirs = array();

	/**
	 * Show bots tree
	 * @param int $iBotDirParentId parent id
	 * @param int $iLevel level
	 * @return string
	 */
	protected function _showBotsTree($iBotDirParentId = 0, $iLevel = 0)
	{
		$return = '';

		if ($iBotDirParentId == 0)
		{
			$aBot_Dirs = Core_Entity::factory('Bot_Dir')->findAll();
			foreach ($aBot_Dirs as $oBot_Dir)
			{
				$this->_aBot_Dirs[$oBot_Dir->parent_id][] = $oBot_Dir;
			}

			$aBot = Core_Entity::factory('Bot')->findAll();
			foreach ($aBot as $oBot)
			{
				$this->_aBots[$oBot->bot_dir_id][] = $oBot;
			}
		}

		if (isset($this->_aBots[$iBotDirParentId]) || isset($this->_aBot_Dirs[$iBotDirParentId]))
		{
			$return .= $iLevel == 0
				? '<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">'
				: '<ul class="dropdown-menu">';

			if (isset($this->_aBot_Dirs[$iBotDirParentId]))
			{
				foreach ($this->_aBot_Dirs[$iBotDirParentId] as $oBot_Dir)
				{
					$return .= '<li class="' . (isset($this->_aBots[$oBot_Dir->id]) || isset($this->_aBot_Dirs[$oBot_Dir->id]) ? 'dropdown-submenu' : '') . '"><a href="#"><i class="fa-regular fa-folder-open"></i>' . htmlspecialchars($oBot_Dir->name) . '</a>';
					$return .= $this->_showBotsTree($oBot_Dir->id, $iLevel + 1);
					$return .= '</li>';
				}
			}

			if (isset($this->_aBots[$iBotDirParentId]))
			{
				foreach ($this->_aBots[$iBotDirParentId] as $oBot)
				{
					$additionalParams = $this->_Admin_Form_Controller->additionalParams . '&bot_id=' . $oBot->id;

					$return .= '<li><a href="#" onclick="' . $this->_Admin_Form_Controller->getAdminActionLoadAjax('/admin/bot/module/index.php', 'addBot', NULL, 0, 0, $additionalParams) . '"><i class="fa fa-android"></i>' . htmlspecialchars($oBot->name) . '</a></li>';
				}
			}

			$return .= '</ul>';
		}

		if ($iBotDirParentId == 0)
		{
			$this->_aBot_Dirs = $this->_aBots = array();
		}

		return $return;
	}
}