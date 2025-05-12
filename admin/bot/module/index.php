<?php
/**
 * Bot.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'bot');

$iAdmin_Form_Id = 308;
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Путь к контроллеру формы ЦА
$sAdminFormAction = '/{admin}/bot/module/index.php';

$module_id = intval(Core_Array::getGet('module_id'));
$entity_id = intval(Core_Array::getGet('entity_id'));
$type = intval(Core_Array::getGet('type'));

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->addView('bot', 'Bot_Controller_View')
	->view('bot')
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	);

if (Core_Array::getPost('show_settings'))
{
	$aJSON = array(
		'status' => 'error'
	);

	$bot_module_id = intval(Core_Array::getPost('bot_module_id'));

	$oBot_Module = Core_Entity::factory('Bot_Module')->getById($bot_module_id);

	if (!is_null($oBot_Module))
	{
		$aDuration =  Core_Date::getDuration($oBot_Module->minutes);

		ob_start();
		?>
		<div class="modal fade" id="settingsModal<?php echo $oBot_Module->id?>" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?php echo Core::_('Bot_Module.settings_modal_title', $oBot_Module->Bot->name)?></h4>
					</div>
					<div class="modal-body">
						<form class="bot-modules-form" role="form">
							<div class="row">
								<div class="form-group col-xs-12">
									<div class="bot-module-type">
										<?php
										$aOptions = array(
											0 => array(
												'value' => Core::_('Bot_Module.delay_type0'),
												'color' => '#53a93f'
											),
											1 => array(
												'value' => Core::_('Bot_Module.delay_type1'),
												'color' => '#5db2ff'
											),
											2 => array(
												'value' => Core::_('Bot_Module.delay_type2'),
												'color' => '#6f85bf'
											),
											3 => array(
												'value' => Core::_('Bot_Module.delay_type3'),
												'color' => '#d73d32'
											)
										);

										Admin_Form_Entity::factory('Dropdownlist')
											->id('delayType' . $oBot_Module->id)
											->options($aOptions)
											->name('delay_type')
											->value($oBot_Module->delay_type)
											->controller($oAdmin_Form_Controller)
											->divAttr(array('class' => 'delay-type'))
											->data('change-context', 'true')
											->execute();

										$settingsModalId = 'settingsModal' . $oBot_Module->id;

										Admin_Form_Entity::factory('Script')
											->value("$('#{$settingsModalId} :hidden').on('change', function(e) { mainFormLocker.lock(e) });")
											->controller($oAdmin_Form_Controller)
											->execute();

											$bHideMinutes = $oBot_Module->delay_type == 0
												? 'hidden'
												: '';
										?>
										<div class="minutes-block <?php echo $bHideMinutes?>">
											<!--<div class="col-xs-12 no-padding-left no-padding-right">-->
												<input type="text" name="minutes" class="form-control flat" size="2" value="<?php echo Core_Array::get($aDuration, 'value', '')?>"/>
												<?php
												$aMinuteTypes = array(
													0 => array(
														'value' => Core::_('Bot_Module.minutes'),
														'color' => '#42ca11',
														'icon' => NULL
													),
													1 => array(
														'value' => Core::_('Bot_Module.hours'),
														'color' => '#11b9ca',
														'icon' => NULL
													),
													2 => array(
														'value' => Core::_('Bot_Module.days'),
														'color' => '#ca9311',
														'icon' => NULL
													)
												);

												Admin_Form_Entity::factory('Dropdownlist')
													->options($aMinuteTypes)
													->name('minutes_type')
													->value(Core_Array::get($aDuration, 'type', 0))
													->controller($oAdmin_Form_Controller)
													->divAttr(array('class' => 'minutes-type'))
													->data('change-context', 'true')
													->execute();
												?>
											<!--</div>-->
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<?php
								$oClass = new $oBot_Module->Bot->class();
								$aFields = $oClass->getFields();

								$aData = json_decode($oBot_Module->json, TRUE);

								foreach ($aFields as $fieldName => $aField)
								{
									$aField += array(
										'divAttr' => 'form-group col-xs-12'
									);

									if (isset($aField['type']))
									{
										$aFormat = isset($aField['obligatory']) && $aField['obligatory']
											? array('minlen' => array('value' => 1))
											: array();

										// ->data('required', 1)

										switch ($aField['type'])
										{
											case 'input':
												$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input');
												$oAdmin_Form_Entity
													->name($fieldName)
													->value(
														isset($aData[$fieldName]) ? $aData[$fieldName] : ''
													)
													->format($aFormat);
											break;
											case 'textarea':
												$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea');
												$oAdmin_Form_Entity
													->name($fieldName)
													->rows(10)
													->value(
														isset($aData[$fieldName]) ? $aData[$fieldName] : ''
													);
											break;
											case 'wysiwyg':
												$id = $fieldName . rand(0, 99999);

												$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea');
												$oAdmin_Form_Entity
													//->id($id)
													->name($fieldName)
													->rows(10)
													->value(
														isset($aData[$fieldName]) ? $aData[$fieldName] : ''
													)
													->wysiwyg(Core::moduleIsActive('wysiwyg'))
													->wysiwygOptions(array(
														'menubar' => 'false',
														'statusbar' => 'false',
														'plugins' => '[\'advlist autolink lists link image charmap print preview anchor\', \'searchreplace visualblocks code fullscreen\', \'insertdatetime media table paste code wordcount\']',
														'toolbar1' => '"insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat"'
													));
											break;
											case 'checkbox':
												$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox');
												$oAdmin_Form_Entity
													->name($fieldName)
													->value(1)
													->checked(isset($aData[$fieldName]) ? $aData[$fieldName] : FALSE);
											break;
											case 'user':
												$oSite = Core_Entity::factory('Site', CURRENT_SITE);
												$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

												$id = $fieldName . rand(0, 99999);

												$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select');
												$oAdmin_Form_Entity
													->id($id)
													->name($fieldName)
													->options($aSelectResponsibleUsers);

												$user_id = isset($aData[$fieldName]) ? $aData[$fieldName] : 0;

												Admin_Form_Entity::factory('Script')
													->value("$('#" . $id . "').selectUser({
															language: '" . Core_i18n::instance()->getLng() . "',
															placeholder: '" . Core::_('User.select_user') . "'
														})
														.val('" . $user_id . "')
														.trigger('change.select2')
														.on('select2:unselect', function (){
															$(this)
																.next('.select2-container')
																.find('.select2-selection--single')
																.removeClass('user-container');
														});"
													)
													->controller($oAdmin_Form_Controller)
													->execute();
											break;
											case 'users':
												$oSite = Core_Entity::factory('Site', CURRENT_SITE);
												$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

												$id = $fieldName . rand(0, 99999);

												$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select');
												$oAdmin_Form_Entity
													->id($id)
													->name($fieldName . '[]')
													->multiple('multiple')
													->options($aSelectResponsibleUsers)
													->style('width: 100%')
													->value(
														isset($aData[$fieldName]) ? $aData[$fieldName] : array()
													);

												Admin_Form_Entity::factory('Script')
													->value('$(function(){
														$("#' . $id . '").select2({
																placeholder: "",
																templateResult: $.templateResultItemResponsibleEmployees,
																escapeMarkup: function(m) { return m; },
																templateSelection: $.templateSelectionItemResponsibleEmployees,
																language: "' . Core_i18n::instance()->getLng() . '",
																width: "100%"
															})
															.on("select2:opening select2:closing", function(e){

																var $searchfield = $(this).parent().find(".select2-search__field");

																if (!$searchfield.data("setKeydownHeader"))
																{
																	$searchfield.data("setKeydownHeader", true);

																	$searchfield.on("keydown", function(e) {

																		var $this = $(this);

																		if ($this.val() == "" && e.key == "Backspace")
																		{
																			$this
																				.parents("ul.select2-selection__rendered")
																				.find("li.select2-selection__choice")
																				.filter(":last")
																				.find(".select2-selection__choice__remove")
																				.trigger("click");

																			e.stopImmediatePropagation();
																			e.preventDefault();
																		}
																	});
																}
															});
														});
													')
													->controller($oAdmin_Form_Controller)
													->execute();
											break;
											case 'dropdown':
												$aOptions = isset($aField['options']) && count($aField['options'])
													? $aField['options']
													: array();

												$oAdmin_Form_Entity = Admin_Form_Entity::factory('Dropdownlist')
													->options($aOptions)
													->name($fieldName)
													->value(
														isset($aData[$fieldName]) ? $aData[$fieldName] : ''
													);
											break;
										}

										$oAdmin_Form_Entity
											->caption(isset($aField['caption']) ? $aField['caption'] : '')
											->divAttr(array('class' => $aField['divAttr']))
											->controller($oAdmin_Form_Controller)
											->execute();
									}
								}
								?>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" onclick="mainFormLocker.unlock(); $.saveBotModuleSettings(<?php echo $oBot_Module->id?>, <?php echo $oBot_Module->module_id?>, <?php echo $oBot_Module->entity_id?>, <?php echo $oBot_Module->type?>)"><?php echo Core::_('Bot_Module.save_settings')?></button>
					</div>
				</div>
			</div>
			<script>
				$(function() {
					$('ul[name = delay_type] li a').on('click', function(){

						var value = parseInt($(this).parents('li').attr('id'));

						if (value)
						{
							$('.minutes-block').removeClass('hidden');
						}
						else
						{
							$('.minutes-block').addClass('hidden');

							// Clear
							$('input[name = minutes]').val(0);
							$('ul[name = minutes_type] li').eq(0).click();
							$('input[name = minutes_type]').val(0);
						}
					});


					$('#settingsModal<?php echo $oBot_Module->id?>').on('hide.bs.modal', function(e){

						$('.open [data-toggle="dropdown"]').dropdown('toggle');
					});

					//$('#settingsModal<?php echo $oBot_Module->id?> :input').on('click', function() { mainFormLocker.unlock() });
				});
			</script>
		</div>
		<?php
		$aJSON = array(
			'status' => 'success',
			'html' => ob_get_clean()
		);
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('save_settings'))
{
	$aJSON = array(
		'status' => 'error'
	);

	$data = Core_Array::getPost('data');
	$aFields = array();
	parse_str($data, $aFields);

	// var_dump($aFields);

	/*array(3) {
	  ["delay_type"]=>
	  string(1) "0"
	  ["minutes"]=>
	  string(1) "2"
	  ["minutes_type"]=>
	  string(1) "0"
	}*/

	$bot_module_id = intval(Core_Array::getPost('bot_module_id'));

	$oBot_Module = Core_Entity::factory('Bot_Module')->getById($bot_module_id);

	if (!is_null($oBot_Module))
	{
		$oBot_Module->delay_type = intval($aFields['delay_type']);
		$oBot_Module->minutes = Core_Date::convertDuration($aFields['minutes'], $aFields['minutes_type']);

		$oClass = new $oBot_Module->Bot->class();
		$aClassFields = $oClass->getFields();

		$aTmp = array();

		foreach ($aClassFields as $fieldName => $aField)
		{
			isset($aFields[$fieldName])
				&& $aTmp[$fieldName] = $aFields[$fieldName];
		}

		$oBot_Module->json = json_encode($aTmp);

		$oBot_Module->save();

		$aJSON = array(
			'status' => 'success'
		);
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('save_sorting'))
{
	$aJSON = array();

	$aIds = Core_Array::getPost('aIds', array());

	foreach ($aIds as $key => $id)
	{
		$oBot_Module = Core_Entity::factory('Bot_Module')->getById($id);

		if (!is_null($oBot_Module))
		{
			$oBot_Module->sorting = $key;
			$oBot_Module->save();
		}
	}

	Core::showJson($aJSON);
}

// Добавление модуля
$oAdminFormActionAdd = $oAdmin_Form->Admin_Form_Actions->getByName('addBot');

if ($oAdminFormActionAdd && $oAdmin_Form_Controller->getAction() == 'addBot')
{
	$oBot_Module_Controller_Add = Admin_Form_Action_Controller::factory(
		'Bot_Module_Controller_Add', $oAdminFormActionAdd
	);

	$oAdmin_Form_Controller->addAction($oBot_Module_Controller_Add);
}

// Удаление модуля
$oAdminFormActionDelete = $oAdmin_Form->Admin_Form_Actions->getByName('deleteModule');

if ($oAdminFormActionDelete && $oAdmin_Form_Controller->getAction() == 'deleteModule')
{
	$oBot_Module_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Bot_Module_Controller_Delete', $oAdminFormActionDelete
	);

	$oAdmin_Form_Controller->addAction($oBot_Module_Controller_Delete);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Bot_Module')
);

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('module_id', '=', $module_id)))
	->addCondition(array('where' => array('entity_id', '=', $entity_id)))
	->addCondition(array('where' => array('type', '=', $type)));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();