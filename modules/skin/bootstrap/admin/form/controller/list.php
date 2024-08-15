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
class Skin_Bootstrap_Admin_Form_Controller_List extends Admin_Form_Controller_View
{
	/**
	 * Is filter necessary
	 * @return self
	 */
	protected function _isFilterNecessary()
	{
		// Is filter necessary
		$aAdmin_Form_Fields = $this->_Admin_Form_Controller->getAdminFormFields();
		foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
		{
			// Перекрытие параметров для данного поля
			$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;

			$aDatasets = $this->_Admin_Form_Controller->getDatasets();

			foreach ($aDatasets as $oTmpAdmin_Form_Dataset)
			{
				$oAdmin_Form_Field_Changed = $this->_Admin_Form_Controller->changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field_Changed);
			}

			if ($oAdmin_Form_Field_Changed->allow_filter || $oAdmin_Form_Field_Changed->view == 1)
			{
				$this->showFilter = TRUE;
				break;
			}
		}

		return $this;
	}

	/**
	 * Top menu bar
	 */
	protected function _topMenuBar()
	{
		?><div class="table-toolbar">
			<?php
			Core_Event::notify('Admin_Form_Controller.onBeforeShowMenu', $this->_Admin_Form_Controller, array($this));
			?>
			<?php $this->_Admin_Form_Controller->showFormMenus()?>
			<div class="table-toolbar-right pull-right">
				<?php $this->showPageSelector && $this->_pageSelector()?>
				<?php $this->showChangeViews && $this->_Admin_Form_Controller->showChangeViews()?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$sAdmin_View = $this->_Admin_Form_Controller->getWindowId() == 'id_content'
			? $this->_Admin_Form_Controller->Admin_View
			: Admin_View::getClassName('Admin_Internal_View');

		$oAdmin_View = Admin_View::create($sAdmin_View)
			->pageTitle($oAdmin_Form_Controller->pageTitle)
			->module($oAdmin_Form_Controller->module);

		$aAdminFormControllerChildren = array();

		foreach ($oAdmin_Form_Controller->getChildren() as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs
				|| $oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
			{
				$oAdmin_View->addChild($oAdmin_Form_Entity);
			}
			else
			{
				$aAdminFormControllerChildren[] = $oAdmin_Form_Entity;
			}
		}

		$this->_isFilterNecessary();

		// При показе формы могут быть добавлены сообщения в message, поэтому message показывается уже после отработки формы
		ob_start();

		$bShowForm = TRUE;

		$oAdmin_Form_Action_View = $oAdmin_Form->Admin_Form_Actions->getByName('viewForm');

		if ($oAdmin_Form_Action_View)
		{
			$oUser = Core_Auth::getCurrentUser();
			$bShowForm = $oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oUser, 'viewForm');
		}

		if ($bShowForm)
		{
			$this->_topMenuBar();
			foreach ($aAdminFormControllerChildren as $oAdmin_Form_Entity)
			{
				$oAdmin_Form_Entity->execute();
			}

			$this->_showContent();
			$this->_showFooter();
		}
		else
		{
			Core_Message::show(Core::_('Admin_Form.viewForm_disallow'), 'error');
		}

		$content = ob_get_clean();

		$oAdmin_View
			->content($content)
			->message($oAdmin_Form_Controller->getMessage())
			->show();

		$oAdmin_Form_Controller->applyEditable();

		$oAdmin_Form_Controller->showSettings();

		return $this;
	}

	/**
	 * Show items count selector
	 */
	protected function _pageSelector()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		// $sCurrentValue = $oAdmin_Form_Controller->limit;

		// $windowId = Core_Str::escapeJavascriptVariable($oAdmin_Form_Controller->getWindowId());
		// $additionalParams = Core_Str::escapeJavascriptVariable(
		// 	str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
		// );

		// TOP FILTER
		if ($this->_filterAvailable())
		{
			$path = Core_Str::escapeJavascriptVariable($oAdmin_Form_Controller->getPath());

			$oCore_Html_Entity_Span = Core_Html_Entity::factory('Span')
				->class('btn btn-sm btn-default margin-right-10')
				->id('showTopFilterButton')
				->onclick('$.toggleFilter(); $.changeFilterStatus({ path: \'' . $path . '\', show: +$(".topFilter").is(":visible") })')
				->title(Core::_('Admin_Form.filter'))
				->add(
					Core_Html_Entity::factory('I')->class('fa fa-filter no-margin')
				);

			$iFilters = count(Core_Array::get($oAdmin_Form_Controller->filterSettings, 'tabs', array()));

			if ($iFilters > 1)
			{
				$oCore_Html_Entity_Span->add(
					Core_Html_Entity::factory('Span')
						->class('badge badge-orange')
						->value($iFilters - 1)
				);
			}

			$oCore_Html_Entity_Span->execute();
		}

		// CSV Export
		Core_Html_Entity::factory('A')
			->class('btn btn-sm btn-default margin-right-10')
			->id('exportCsvButton')
			->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath()) . '&hostcms[export]=csv')
			->title(Core::_('Admin_Form.export_csv'))
			->target('_blank')
			->add(
				Core_Html_Entity::factory('I')->class('fa fa-upload no-margin')
			)
			->execute();

		// $oUser = Core_Auth::getCurrentUser();


		$aModelNames = array();
		$aDatasets = $oAdmin_Form_Controller->getDatasets();
		foreach ($aDatasets as $datasetKey => $oAdmin_Form_Dataset)
		{
			$oEntity = $oAdmin_Form_Dataset->getEntity();
			if ($oEntity instanceof Core_Entity)
			{
				$aModelNames[] = $oEntity->getModelName();
			}
		}

		// Settings
		Core_Html_Entity::factory('Span')
			->class('btn btn-sm btn-default margin-right-10')
			->id('adminFormSettings')
			->title(Core::_('Admin_Form.admin_form_field_settings'))
			->onclick('$.showAdminFormSettings(' . $oAdmin_Form->id . ', ' . CURRENT_SITE . ', \'' . implode(',', $aModelNames) . '\')')
			->add(
				Core_Html_Entity::factory('I')->class('fa-solid fa-cog no-margin')
			)
			->execute();

		$oAdmin_Form_Controller->pageSelector();
	}

	/**
	 * Show form footer
	 * @hostcms-event Admin_Form_Controller.onBeforeShowFooter
	 * @hostcms-event Admin_Form_Controller.onAfterShowFooter
	 */
	public function _showFooter()
	{
		$bShowNavigation = $this->showPageNavigation
			&& $this->_Admin_Form_Controller->getTotalCount() > $this->_Admin_Form_Controller->limit;

		Core_Event::notify('Admin_Form_Controller.onBeforeShowFooter', $this->_Admin_Form_Controller, array($this));

		?><div class="DTTTFooter">
			<div class="row">
				<div class="col-xs-12 <?php echo $bShowNavigation ? 'col-sm-6 col-md-7 col-lg-8' : ''?>">
					<?php $this->bottomActions()?>
				</div>
				<?php
				if ($bShowNavigation)
				{
					?><div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
						<?php $this->_Admin_Form_Controller->pageNavigation()?>
					</div><?php
				}
				?>
			</div>
		</div>
		<script>
			$(function (){
				// Sticky actions
				$('.DTTTFooter').addClass('sticky-actions');

				$(document).on("scroll", function () {
					// to bottom
					if ($(window).scrollTop() + $(window).height() == $(document).height()) {
						$('.DTTTFooter').removeClass('sticky-actions');
					}

					// to top
					if ($(window).scrollTop() + $(window).height() < $(document).height()) {
						$('.DTTTFooter').addClass('sticky-actions');
					}
				});
			});
		</script>
		<?php

		Core_Event::notify('Admin_Form_Controller.onAfterShowFooter', $this->_Admin_Form_Controller, array($this));

		return $this;
	}

	/**
	 * Check filter availability
	 * @return boolean
	 */
	protected function _filterAvailable()
	{
		if ($this->showFilter)
		{
			$aAdmin_Form_Fields = $this->_Admin_Form_Controller->getAdminFormFields();
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				// Перекрытие параметров для данного поля
				$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;

				$aDatasets = $this->_Admin_Form_Controller->getDatasets();
				foreach ($aDatasets as $datasetKey => $oTmpAdmin_Form_Dataset)
				{
					$oAdmin_Form_Field_Changed = $this->_Admin_Form_Controller->changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field_Changed);
				}

				if ($oAdmin_Form_Field_Changed->allow_filter && $oAdmin_Form_Field_Changed->view != 2 || $oAdmin_Form_Field_Changed->view == 1)
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Show top filter form
	 * @return self
	 */
	protected function _showTopFilter()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

		$aAdmin_Form_Fields = $this->_Admin_Form_Controller->getAdminFormFields();
		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		$aHide = array();
		$path = Core_Str::escapeJavascriptVariable($oAdmin_Form_Controller->getPath());

		$aTabs = Core_Array::get($oAdmin_Form_Controller->getFilterSettings(), 'tabs', array());
		?>
		<div class="tabbable topFilter" style="display: none" id="top-filter-<?php echo $oAdmin_Form->id?>">
			<ul class="nav nav-tabs tabs-flat" id="filterTabs">
				<?php
				!isset($aTabs['main']) && $aTabs['main'] = array();

				foreach ($aTabs as $tabName => $aTab)
				{
					$tabName = strval($tabName);
					$bMain = $tabName === 'main';

					$bCurrent = $oAdmin_Form_Controller->filterId === $tabName
						|| $oAdmin_Form_Controller->filterId === '' && $bMain;

					?><li id="filter-li-<?php echo htmlspecialchars($tabName)?>" <?php echo $bCurrent ? ' class="active tab-orange"' : ''?> data-filter-id="<?php echo htmlspecialchars($tabName)?>">
						<a data-toggle="tab" href="#filter-<?php echo htmlspecialchars($tabName)?>">
							<?php echo htmlspecialchars(
								$bMain
									? Core::_('Admin_Form.filter')
									: $aTab['caption']
							)?>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
			<div class="tab-content tabs-flat">
				<?php
				$filterPrefix = 'topFilter_';
				foreach ($aTabs as $tabName => $aTab)
				{
					$tabName = strval($tabName);
					$bMain = $tabName === 'main';

					$bCurrent = $oAdmin_Form_Controller->filterId === $tabName
						|| $oAdmin_Form_Controller->filterId === '' && $bMain;

					?><div id="filter-<?php echo htmlspecialchars($tabName)?>" class="tab-pane<?php echo $bCurrent ? ' in active' : ''?>">
						<div id="horizontal-form">
							<form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($oAdmin_Form_Controller->getPath())?>" data-filter-id="<?php echo htmlspecialchars($tabName)?>" method="POST">
								<?php
								// Top Filter
								foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
								{
									// Перекрытие параметров для данного поля
									$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;

									$aDatasets = $oAdmin_Form_Controller->getDatasets();
									foreach ($aDatasets as $datasetKey => $oTmpAdmin_Form_Dataset)
									{
										$oAdmin_Form_Field_Changed = $oAdmin_Form_Controller->changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field_Changed);
									}

									if ($oAdmin_Form_Field_Changed->allow_filter && $oAdmin_Form_Field_Changed->view != 2 || $oAdmin_Form_Field_Changed->view == 1)
									{
										$fieldName = $oAdmin_Form_Field instanceof Admin_Form_Field_Model
											? $oAdmin_Form_Field->getCaption($oAdmin_Language->id)
											: $oAdmin_Form_Field->caption;

										if (!is_null($fieldName))
										{
											$sFormGroupId = $tabName . '-field-' . $oAdmin_Form_Field_Changed->id;

											$bHide = isset($aTabs[$tabName]['fields'][$oAdmin_Form_Field_Changed->name]['show'])
												&& $aTabs[$tabName]['fields'][$oAdmin_Form_Field_Changed->name]['show'] == 0;

											$bHide && $aHide[] = '#' . $sFormGroupId;

											$sInputId = "id_{$filterPrefix}{$oAdmin_Form_Field_Changed->id}";

											?><div class="form-group" id="<?php echo htmlspecialchars($sFormGroupId)?>">
												<label for="<?php echo htmlspecialchars($sInputId)?>" class="col-sm-3 col-lg-2 control-label no-padding-right">
													<?php echo htmlspecialchars($fieldName)?>
												</label>
												<div class="col-sm-9 col-lg-10">
													<div class="row">
														<?php
															if ($oAdmin_Form_Field_Changed->filter_condition)
															{
																?>
																<div class="col-xs-2 col-md-1 no-padding-right">
																<?php
																$aOptions = array(
																	'=' => '=',
																	'>' => '>',
																	'<' => '<',
																	'>=' => '≥',
																	'<=' => '≤',
																);
																?>
																<select name="<?php echo $filterPrefix, $oAdmin_Form_Field_Changed->id, '_condition'?>" class="form-control input-sm">
																	<?php
																	$filterCondition = Core_Array::get($oAdmin_Form_Controller->request, "{$filterPrefix}{$oAdmin_Form_Field_Changed->id}_condition", '=');

																	foreach ($aOptions as $key => $value)
																	{

																		$selected = $filterCondition == $key ? 'selected="selected"' : '';

																		?><option <?php echo $selected?> value="<?php echo $key?>"><?php echo $value?></option><?php
																	}
																	?>
																</select>

																</div>
																<?php
															}
														?>
														<div class="<?php echo $oAdmin_Form_Field_Changed->filter_condition ? 'col-xs-10 col-md-11' : 'col-sm-12'?>">
															<?php
															$oAdmin_Form_Controller->showFilterField($oAdmin_Form_Field_Changed, $filterPrefix, $tabName);
															?>
														</div>
													</div>
												</div>
											</div><?php
										}
									}
								}

								if (Core::moduleIsActive('tag') && !is_null($oAdmin_Form_Controller->showTopFilterTags))
								{
									$aOptionTags = array();

									$aFilterValues = Core_Array::getPost($filterPrefix . 'filter_tags', array(), 'array');

									foreach ($aFilterValues as $tag_name)
									{
										$oTag = Core_Entity::factory('Tag')->getByName($tag_name);

										if (!is_null($oTag))
										{
											$aOptionTags[$oTag->name] = array(
												'value' => $oTag->name,
												'attr' => array('selected' => 'selected')
											);
										}
									}

									?><div class="form-group" id="filter_tags">
										<label for="id_<?php echo $filterPrefix?>_filter_tags" class="col-sm-3 col-lg-2 control-label no-padding-right">
											Метки
										</label>
										<div class="col-sm-9 col-lg-10">
											<div class="row">
												<div class="col-sm-12"><?php
													Admin_Form_Entity::factory('Select')
														->options($aOptionTags)
														->name($filterPrefix . 'filter_tags[]')
														->class('filter-tags')
														->style('width: 100%')
														->multiple('multiple')
														->divAttr(array('class' => ''))
														->execute();
												?></div>
												<script>
												$(function(){
													$("#<?php echo $windowId?> .filter-tags").select2({
														dropdownParent: $("#<?php echo $windowId?>"),
														language: "<?php echo Core_I18n::instance()->getLng()?>",
														minimumInputLength: 1,
														placeholder: "<?php echo Core::_('Shop_Item.type_tag')?>",
														tags: true,
														allowClear: true,
														multiple: true,
														ajax: {
															url: "/admin/tag/index.php?loadFilterTagsList&entity=<?php echo $oAdmin_Form_Controller->showTopFilterTags?>",
															dataType: "json",
															type: "GET",
															processResults: function (data) {
																var aResults = [];
																$.each(data, function (index, item) {
																	aResults.push({
																		"id": item.id,
																		"text": item.text
																	});
																});
																return {
																	results: aResults
																};
															}
														}
													});
												});</script>
											</div>
										</div>
									</div><?php
								}
								?>
								<div class="form-group text-align-right">
									<div class="col-sm-offset-2 col-sm-10">
										<button type="submit" class="btn btn-default" onclick="mainFormLocker.unlock(); <?php echo $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())?>"><?php echo Core::_('Admin_Form.button_to_filter')?></button>
										<div class="btn-group">
											<a class="btn btn-default dropdown-toggle" data-toggle="dropdown">
												<i class="fa fa-plus"></i>
											</a>
											<ul class="dropdown-menu dropdown-menu-right">
												<?php
												foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
												{
													if ($oAdmin_Form_Field->allow_filter && $oAdmin_Form_Field->view != 2 || $oAdmin_Form_Field->view == 1)
													{
														$fieldName = $oAdmin_Form_Field instanceof Admin_Form_Field_Model
															? $oAdmin_Form_Field->getCaption($oAdmin_Language->id)
															: $oAdmin_Form_Field->name;

														if (!is_null($fieldName))
														{
															$class = isset($aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['show'])
																&& $aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['show'] == 0
																? ''
																: ' fa-check';

															?><li>
																<a data-filter-field-id="<?php echo htmlspecialchars($tabName) . '-field-' . $oAdmin_Form_Field->id?>" onclick="$.changeFilterField({ path: '<?php echo $path?>', tab: '<?php echo htmlspecialchars($tabName)?>', field: '<?php echo Core_Str::escapeJavascriptVariable($oAdmin_Form_Field->name)?>', context: this })"><i class="dropdown-icon fa<?php echo $class?>"></i> <?php echo htmlspecialchars($fieldName)?></a>
															</li><?php
														}
													}
												}
												?>
											</ul>
										</div>

										<div class="btn-group">
											<a class="btn btn-default dropdown-toggle" data-toggle="dropdown">
												<i class="fa fa-gear"></i>
											</a>
											<ul class="dropdown-menu dropdown-menu-right">
												<li>
													<a href="javascript:void(0);" onclick="$.filterSaveAs('<?php echo Core::_('Admin_Form.filter_enter_title')?>', $(this), '<?php echo Core_Str::escapeJavascriptVariable(str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams))?>')"><?php echo Core::_('Admin_Form.saveAs')?></a>
													<?php if (!$bMain) {
													?>
													<a href="javascript:void(0);" onclick="$.filterSave($(this))"><?php echo Core::_('Admin_Form.save')?></a>
													<?php
													$sDelete = Core::_('Admin_Form.delete');
													?>
													<a href="javascript:void(0);" onclick="res = confirm('<?php echo htmlspecialchars(Core::_('Admin_Form.confirm_dialog', $sDelete))?>'); if (res) { $.filterDelete($(this)) } return res;"><?php echo $sDelete?></a>
													<?php
													}
													?>
												</li>
											</ul>
										</div>

										<a class="btn btn-default" title="<?php echo Core::_('Admin_Form.clear')?>" onclick="$.clearTopFilter('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>')"><i class="fa fa-times-circle no-margin"></i></a>
									</div>
								</div>
							</form>
						</div>
					</div>
					<?php
				}

				if (count($aHide))
				{
					?><script>$('<?php echo implode(',', $aHide)?>').hide();</script><?php
				}
				?>
			</div>
		</div>
		<?php

		return $this;
	}

	/**
	 * Show Form Content
	 *
	 * @hostcms-event Admin_Form_Controller.onBeforeGetAdminActionLoadHref
	 * @hostcms-event Admin_Form_Controller.onBeforeGetAdminActionLoadAjax
	 * @hostcms-event Admin_Form_Controller.onBeforeShowField
	 * @hostcms-event Admin_Form_Controller.onAfterShowField
	 */
	protected function _showContent()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

		$aAdmin_Form_Fields = $this->_Admin_Form_Controller->getAdminFormFields();
		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		//Core_Event::notify('Admin_Form_Controller.onBeforeShowContent', $this);
		$oUser = Core_Auth::getCurrentUser();

		if (is_null($oUser))
		{
			return FALSE;
		}

		if ($this->_filterAvailable())
		{
			$this->_showTopFilter();
		}

		$oSortingField = $oAdmin_Form_Controller->getSortingField();

		$aModelNames = array();
		$aDatasets = $oAdmin_Form_Controller->getDatasets();
		foreach ($aDatasets as $datasetKey => $oAdmin_Form_Dataset)
		{
			$oEntity = $oAdmin_Form_Dataset->getEntity();
			if ($oEntity instanceof Core_Entity)
			{
				$aModelNames[] = $oEntity->getModelName();
			}
		}

		// Available Fields for User
		$aAvailableFields = $oAdmin_Form->getAvailableFieldsForUser($oUser->id);

		// IF Not available fields show default fields
		if (!count($aAvailableFields))
		{
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				$oAdmin_Form_Field->show_by_default
					&& $aAvailableFields[$oAdmin_Form_Field->id] = $oAdmin_Form_Field->id;
			}
		}
		?>
		<div class="admin-table-wrap table-scrollable no-border">
			<table class="admin-table table table-hover table-striped" data-admin-form-id="<?php echo $oAdmin_Form->id?>" data-site-id="<?php echo CURRENT_SITE?>" data-models-names="<?php echo implode(',', $aModelNames)?>" id="admin-table-<?php echo $oAdmin_Form->id?>">
				<thead>
				<tr>
					<?php
					// Ячейку над групповыми чекбоксами показываем только при наличии действий
					if ($oAdmin_Form->show_operations && $oAdmin_Form_Controller->showOperations)
					{
						?><th class="action-checkbox">&nbsp;</th><?php
					}

					$allow_filter = FALSE;
					foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
					{
						if (isset($aAvailableFields[$oAdmin_Form_Field->id]))
						{
							// 0 - Столбец и фильтр, 2 - Столбец
							if ($oAdmin_Form_Field->view == 0 || $oAdmin_Form_Field->view == 2)
							{
								// There is at least one filter
								$oAdmin_Form_Field->allow_filter && $allow_filter = TRUE;

								// Перекрытие параметров для данного поля
								$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;

								$aDatasets = $oAdmin_Form_Controller->getDatasets();
								foreach ($aDatasets as $datasetKey => $oTmpAdmin_Form_Dataset)
								{
									$oAdmin_Form_Field_Changed = $oAdmin_Form_Controller->changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field_Changed);
								}

								$oAdmin_Form_Field_Settings = Core_Entity::factory('Admin_Form_Field_Setting');
								$oAdmin_Form_Field_Settings->queryBuilder()
									->where('admin_form_id', '=', $oAdmin_Form->id)
									->where('user_id', '=', $oUser->id);

								strpos($oAdmin_Form_Field_Changed->id, 'uf_') === 0
									? $oAdmin_Form_Field_Settings->queryBuilder()->where('field_id', '=', intval(filter_var($oAdmin_Form_Field_Changed->id, FILTER_SANITIZE_NUMBER_INT)))
									: $oAdmin_Form_Field_Settings->queryBuilder()->where('admin_form_field_id', '=', $oAdmin_Form_Field_Changed->id);

								$oAdmin_Form_Field_Setting = $oAdmin_Form_Field_Settings->getFirst(FALSE);

								$width = !is_null($oAdmin_Form_Field_Setting) && intval($oAdmin_Form_Field_Setting->width)
									? $oAdmin_Form_Field_Setting->width . 'px'
									: htmlspecialchars((string) $oAdmin_Form_Field_Changed->width);

								$class = htmlspecialchars((string) $oAdmin_Form_Field_Changed->class);

								$fieldName = $oAdmin_Form_Field instanceof Admin_Form_Field_Model
									? $oAdmin_Form_Field->getCaption($oAdmin_Language->id)
									: $oAdmin_Form_Field->caption;

								$fieldName = !is_null($fieldName) && $fieldName !== ''
									? htmlspecialchars($fieldName)
									: '—';

								$oAdmin_Form_Field_Changed->allow_sorting
									&& is_object($oSortingField)
									&& $oAdmin_Form_Field->id == $oSortingField->id
									&& $class .= ' highlight';

								$sSortingOnClick = '';

								if ($oAdmin_Form_Field_Changed->allow_sorting)
								{
									//$hrefDown = $oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 1);
									$onclickDown = $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 1);

									//$hrefUp = $oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 0);
									$onclickUp = $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 0);

									if ($oAdmin_Form_Field->id == $oAdmin_Form_Controller->sortingFieldId)
									{
										$class .= $oAdmin_Form_Controller->sortingDirection == 1
											? ' sorting_desc'
											: ' sorting_asc';

										$sSortingOnClick = $oAdmin_Form_Controller->sortingDirection == 1
											? $onclickUp
											: $onclickDown;
									}
									else
									{
										$class .= ' sorting';
										$sSortingOnClick = $onclickUp;
									}
								}
								?><th title="<?php echo $fieldName?>" class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?> onclick="if (event.target != event.currentTarget) return false; <?php echo $sSortingOnClick?>" data-admin-form-field-id="<?php echo $oAdmin_Form_Field_Changed->id?>"><?php
									if ($oAdmin_Form_Field_Changed->ico !='')
									{
										echo '<i class="' . htmlspecialchars((string) $oAdmin_Form_Field_Changed->ico) . '" title="' . $fieldName . '"></i>';
									}
									else
									{
										echo $fieldName;
									}
								?></th><?php
							}
						}
					}

					// Доступные действия для пользователя
					$aAllowed_Admin_Form_Actions = $oAdmin_Form_Controller->getAdminFormActions();

					if ($oAdmin_Form->show_operations && $oAdmin_Form_Controller->showOperations
						|| $allow_filter && $this->showFilter)
					{
						$iSingleActionCount = 0;

						foreach ($aAllowed_Admin_Form_Actions as $oAdmin_Form_Action)
						{
							$oAdmin_Form_Action->single && $iSingleActionCount++;
						}

						?><th class="filter-action-<?php echo $iSingleActionCount?> sticky-column">&nbsp;</th><?php
					}
					?>
				</tr><?php
			?><tr class="admin_table_filter"><?php
			// Чекбокс "Выбрать все" показываем только при наличии действий
			if ($oAdmin_Form->show_operations && $oAdmin_Form_Controller->showOperations)
			{
				?><td class="action-checkbox"><label><input type="checkbox" name="admin_forms_all_check" id="id_admin_forms_all_check" onclick="$('#<?php echo $windowId?>').highlightAllRows(this.checked)" class="form-control"/><span class="text"></span></label></td><?php
			}

			// Main Filter
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				// 0 - Столбец и фильтр, 2 - Столбец
				if (isset($aAvailableFields[$oAdmin_Form_Field->id]) && ($oAdmin_Form_Field->view == 0 || $oAdmin_Form_Field->view == 2))
				{
					// Перекрытие параметров для данного поля
					$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;

					$aDatasets = $oAdmin_Form_Controller->getDatasets();
					foreach ($aDatasets as $datasetKey => $oTmpAdmin_Form_Dataset)
					{
						$oAdmin_Form_Field_Changed = $oAdmin_Form_Controller->changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field_Changed);
					}

					$width = htmlspecialchars((string) $oAdmin_Form_Field_Changed->width);
					$class = htmlspecialchars((string) $oAdmin_Form_Field_Changed->class);

					// Подсвечивать
					$oAdmin_Form_Field_Changed->allow_sorting
						&& is_object($oSortingField)
						&& $oAdmin_Form_Field->id == $oSortingField->id
						&& $class .= ' highlight';

					?><td class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?>><?php

					if ($oAdmin_Form_Field_Changed->allow_filter)
					{
						$filterPrefix = 'admin_form_filter_';
						$oAdmin_Form_Controller->showFilterField($oAdmin_Form_Field_Changed, $filterPrefix);
					}
					else
					{
						// Фильтр не разрешен.
						?><div style="color: #CEC3A3; text-align: center">—</div><?php
					}
					?></td><?php
				}
			}

			// Фильтр показываем если есть события или хотя бы у одного есть фильтр
			if ($oAdmin_Form->show_operations && $oAdmin_Form_Controller->showOperations
				|| $allow_filter && $this->showFilter)
			{
				$onclick = $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath());

				?><td class="apply-button sticky-column"><?php
					?><div class="btn-group">
						<a class="btn btn-xs btn-palegreen" id="admin_forms_apply_button" title="<?php echo Core::_('Admin_Form.button_to_filter')?>" onclick="mainFormLocker.unlock(); <?php echo $onclick?>"><i class="fa-solid fa-magnifying-glass"></i></a>
						<a title="<?php echo Core::_('Admin_Form.clear')?>" class="btn btn-xs btn-magenta" onclick="$.clearFilter('<?php echo $windowId?>')"><i class="fa fa-times-circle"></i></a>
					</div><?php
				?></td><?php
			}
			?></tr>
			</thead><?php

			$oAdmin_Form_Controller->addAdditionalParam('secret_csrf', Core_Security::getCsrfToken());

			// Устанавливаем ограничения на источники
			$oAdmin_Form_Controller->setDatasetConditions();
			$oAdmin_Form_Controller->setDatasetLimits();

			$aDatasets = $oAdmin_Form_Controller->getDatasets();
			foreach ($aDatasets as $datasetKey => $oAdmin_Form_Dataset)
			{
				try {
					// Добавляем внешнюю замену по датасету
					$oAdmin_Form_Controller->addExternalReplace('{dataset_key}', $datasetKey);

					$quotedDatasetKey = htmlspecialchars($datasetKey);
					$escapedDatasetKey = Core_Str::escapeJavascriptVariable($oAdmin_Form_Controller->jQueryEscape($datasetKey));

					$aDataFromDataset = $oAdmin_Form_Dataset->load();
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
					$aDataFromDataset = array();
				}

				if (!empty($aDataFromDataset))
				{
					$key_field_name = $oAdmin_Form->key_field;

					foreach ($aDataFromDataset as $oEntity)
					{
						if (!isset($oEntity->$key_field_name))
						{
							throw new Core_Exception('Error! Key field missing in the Entity');
						}

						try
						{
							$entityKey = $oEntity->$key_field_name;

							// Экранируем ' в имени индексного поля, т.к. дальше это значение пойдет в JS
							$quotedEntityKey = htmlspecialchars($entityKey);
							$escapedEntityKey = Core_Str::escapeJavascriptVariable($oAdmin_Form_Controller->jQueryEscape(htmlspecialchars($entityKey)));

							/*$entityKey = str_replace(
								array("'", '%'),
								array("\'", '\\%'),
								$entityKey
							);*/
						}
						catch (Exception $e)
						{
							Core_Message::show('Caught exception: ' . $e->getMessage() . "\n", 'error');
							$entityKey = NULL;
						}

						?><tr id="row_<?php echo $quotedDatasetKey?>_<?php echo $quotedEntityKey?>">
						<?php
						// Чекбокс "Для элемента" показываем только при наличии действий
						if ($oAdmin_Form->show_operations && $oAdmin_Form_Controller->showOperations)
						{
							?><td class="action-checkbox">
								<label><input type="checkbox" id="check_<?php echo $quotedDatasetKey?>_<?php echo $quotedEntityKey?>" onclick="$('#<?php echo $windowId?>').setTopCheckbox(); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" /><span class="text"></span></label><?php
							?></td><?php
						}

						foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
						{
							// 0 - Столбец и фильтр, 2 - Столбец
							if (isset($aAvailableFields[$oAdmin_Form_Field->id])
								&& ($oAdmin_Form_Field->view == 0 || $oAdmin_Form_Field->view == 2)
							)
							{
								// Перекрытие параметров для данного поля
								$oAdmin_Form_Field_Changed = $oAdmin_Form_Controller->changeField($oAdmin_Form_Dataset, $oAdmin_Form_Field);

								// Параметры поля.
								// $width = htmlspecialchars(trim((string) $oAdmin_Form_Field_Changed->width));
								$class = htmlspecialchars((string) $oAdmin_Form_Field_Changed->class);

								$oAdmin_Form_Field->allow_sorting
									&& is_object($oSortingField)
									&& $oAdmin_Form_Field->id == $oSortingField->id
									&& $class .= ' highlight';

								/*?><td class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?>><?php*/
								?><td class="<?php echo trim($class)?>"><?php

								$fieldName = $oAdmin_Form_Controller->getFieldName($oAdmin_Form_Field_Changed->name);

								// Badges
								$badgesMethodName = $fieldName . 'Badge';
								$bBadge = $oAdmin_Form_Controller->isCallable($oEntity, $badgesMethodName);

								if ($bBadge)
								{
									?><div style="position: relative"><?php
								}

								// Backends
								$aTmpFieldName = explode('.', $oAdmin_Form_Field_Changed->name);
								$backendName = (isset($aTmpFieldName[1]) ? $aTmpFieldName[1] : $aTmpFieldName[0]) . 'Backend';

								try
								{
									Core_Event::notify('Admin_Form_Controller.onBeforeShowField', $oAdmin_Form_Controller, array($oEntity, $oAdmin_Form_Field));

									// Не вычисляемое поле и нет Backend-метода
									if ($oAdmin_Form_Field_Changed->type != 10 && !$oAdmin_Form_Controller->isCallable($oEntity, $backendName))
									{
										if (isset($oEntity->$fieldName))
										{
											// значение свойства
											$value = htmlspecialchars((string) $oEntity->$fieldName);
										}
										//elseif (strpos($fieldName, 'uf_') === 0 && isset($oEntity->$fieldName))
										elseif ($oAdmin_Form_Controller->isCallable($oEntity, $fieldName))
										{
											// Выполним функцию обратного вызова
											$value = htmlspecialchars((string) $oEntity->$fieldName($oAdmin_Form_Field_Changed, $oAdmin_Form_Controller));
										}
										else
										{
											$value = NULL;
										}

										$element_name = "apply_check_{$quotedDatasetKey}_{$quotedEntityKey}_fv_{$oAdmin_Form_Field_Changed->id}";

										$sCheckSelector = "check_{$escapedDatasetKey}_{$escapedEntityKey}";
										// Формат не экранируем, т.к. он может содержать теги
										$sFormat = $oAdmin_Form_Field_Changed->format;

										// Отображения элементов полей, в зависимости от их типа.
										switch ($oAdmin_Form_Field_Changed->type)
										{
											case 1: // Текст.
												if (!is_null($value))
												{
													?><span id="<?php echo $element_name?>"<?php echo $oAdmin_Form_Field_Changed->editable ? ' class="editable"' : ''?>><?php
													echo $oAdmin_Form_Controller->applyFormat(nl2br($value), $sFormat)?></span><?php
												}
											break;
											case 2: // Поле ввода.
												if (!is_null($value))
												{
													?><input type="text" name="<?php echo $element_name?>" id="<?php echo $element_name?>" value="<?php echo $value?>" onchange="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" onkeydown="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" class="form-control input-xs" /><?php
												}
											break;
											case 3: // Checkbox.
												?><label><input type="checkbox" name="<?php echo $element_name?>" id="<?php echo $element_name?>" <?php echo intval($value) ? 'checked="checked"' : ''?> onclick="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight();" value="1" /><span class="text"></span></label><?php
											break;
											case 4: // Ссылка.
												$link = htmlspecialchars($oAdmin_Form_Field_Changed->link);
												$onclick = htmlspecialchars($oAdmin_Form_Field_Changed->onclick);

												//$link_text = trim($value);
												$link_text = $oAdmin_Form_Controller->applyFormat($value, $sFormat);

												$link = $oAdmin_Form_Controller->doReplaces($aAdmin_Form_Fields, $oEntity, $link);
												$onclick = $oAdmin_Form_Controller->doReplaces($aAdmin_Form_Fields, $oEntity, $onclick, 'onclick');

												if (mb_strlen($link_text))
												{
													?><a href="<?php echo $link?>" <?php echo (!empty($onclick)) ? "onclick=\"{$onclick}\"" : ''?>><?php echo $link_text?></a><?php
												}
												else
												{
													?>&nbsp;<?php
												}
											break;
											case 5: // Дата-время.
												if (!is_null($value))
												{
													$value = $value == '0000-00-00 00:00:00' || $value == ''
														? ''
														: Core_Date::sql2datetime($value);

													if ($oAdmin_Form_Field_Changed->editable)
													{
														$sCurrentLng = Core_I18n::instance()->getLng();

														?><div class="row">
															<div class="date col-xs-12">
																<input id="<?php echo $element_name?>" name="<?php echo $element_name?>" size="auto" type="text" value="<?php echo htmlspecialchars($value)?>" class="form-control input-sm" />
															</div>
														</div>
														<script type="text/javascript">
														(function($) {
															var el = $('#<?php echo $windowId?> #<?php echo $element_name?>');
															el.datetimepicker({
																locale: '<?php echo $sCurrentLng?>',
																format: '<?php echo Core::$mainConfig['dateTimePickerFormat']?>',
																useCurrent: false
															})
															.on('dp.change', function(){
																$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>');
															})
															.on('click', function(){
																$(this).trigger('focus')
															});
															el.closest('td').css('overflow','visible');
														})(jQuery);
														</script><?php
													}
													else
													{
														?><span id="<?php echo $element_name?>"><?php echo $oAdmin_Form_Controller->applyFormat($value, $sFormat)?></span><?php
													}

													/*?><span id="<?php echo $element_name?>"<?php echo $oAdmin_Form_Field_Changed->editable ? ' class="editable"' : ''?>><?php echo $oAdmin_Form_Controller->applyFormat($value, $sFormat)?></span><?php*/
												}
											break;
											case 6: // Дата.
												if (!is_null($value))
												{
													$value = $value == '0000-00-00 00:00:00' || $value == ''
														? ''
														: Core_Date::sql2date($value);

													if ($oAdmin_Form_Field_Changed->editable)
													{
														$sCurrentLng = Core_I18n::instance()->getLng();

														?><div class="row">
															<div class="date col-xs-12">
																<input id="<?php echo $element_name?>" name="<?php echo $element_name?>" size="auto" type="text" value="<?php echo htmlspecialchars($value)?>" class="form-control input-sm" />
															</div>
														</div>
														<script type="text/javascript">
														(function($) {
															var el = $('#<?php echo $windowId?> #<?php echo $element_name?>');
															el.datetimepicker({
																locale: '<?php echo $sCurrentLng?>',
																format: '<?php echo Core::$mainConfig['datePickerFormat']?>',
																useCurrent: false
															})
															.on('dp.change', function(){
																$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>');
															})
															.on('click', function(){
																$(this).trigger('focus')
															});
															el.closest('td').css('overflow','visible');
														})(jQuery);
														</script><?php
													}
													else
													{
														?><span id="<?php echo $element_name?>"><?php echo $oAdmin_Form_Controller->applyFormat($value, $sFormat)?></span><?php
													}

													/*?><span id="<?php echo $element_name?>"<?php echo $oAdmin_Form_Field_Changed->editable ? ' class="editable"' : ''?>><?php
													echo $oAdmin_Form_Controller->applyFormat($value, $sFormat)?></span><?php*/
												}
											break;
											case 7: // Картинка-ссылка.
												$link = $oAdmin_Form_Field_Changed->link;
												$onclick = $oAdmin_Form_Field_Changed->onclick;

												$link = $oAdmin_Form_Controller->doReplaces($aAdmin_Form_Fields, $oEntity, $link);
												$onclick = $oAdmin_Form_Controller->doReplaces($aAdmin_Form_Fields, $oEntity, $onclick, 'onclick');

												// ALT-ы к картинкам
												// TITLE-ы к картинкам
												$alt_array = $title_array = $value_array = $ico_array = array();

												/*
												Разделяем варианты значений на строки, т.к. они приходят к нам в виде:
												0 = /images/off.gif
												1 = /images/on.gif
												*/
												$aImageExplode = explode("\n", $oAdmin_Form_Field_Changed->image);

												foreach ($aImageExplode as $str_value)
												{
													// Каждую строку разделяем по равно
													$str_explode = explode('=', $str_value);

													if (count($str_explode) > 1)
													{
														$mIndex = trim($str_explode[0]);
														// сохраняем в массив варинаты значений и ссылки для них
														$value_array[$mIndex] = trim($str_explode[1]);

														// Если указано альтернативное значение для картинки - добавим его в alt и title
														if (isset($str_explode[2])
															&& trim((string) $value) == $mIndex)
														{
															$sTmp = trim($str_explode[2]);

															$lngAltName = 'Admin_Form.' . $sTmp;
															if (Core_I18n::instance()->check($lngAltName))
															{
																$sTmp = Core::_($lngAltName);
															}

															$alt_array[$mIndex] = $title_array[$mIndex] = $sTmp;
														}

														// ICO
														isset($str_explode[3])
															&& $ico_array[$mIndex] = $str_explode[3];
													}
												}

												// Получаем заголовок столбца на случай, если для IMG не было указано alt-а или title
												$fieldCaption = $oAdmin_Form_Field instanceof Admin_Form_Field_Model
													? $oAdmin_Form_Field->getCaption($oAdmin_Language->id)
													: $oAdmin_Form_Field->caption;

												$fieldCaption = $fieldCaption != ''
													? $fieldCaption
													: '—';

												if (empty($alt_array[$value]))
												{
													$alt_array[$value] = $fieldCaption;
												}

												if (empty($title_array[$value]))
												{
													$title_array[$value] = $fieldCaption;
												}

												if (isset($value_array[$value]))
												{
													$src = $value_array[$value];
												}
												elseif (isset($value_array['']))
												{
													$src = $value_array[''];
												}
												else
												{
													$src = NULL;
												}

												if (isset($ico_array[$value]))
												{
													$ico = $ico_array[$value];
												}
												elseif (isset($ico_array['']))
												{
													$ico = $ico_array[''];
												}
												else
												{
													$ico = NULL;
												}

												// Отображаем картинку ссылкой
												if (!empty($link) && !is_null($src))
												{
													?><a href="<?php echo $link?>" onclick="$('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight();<?php echo $onclick?>"><?php
												}

												// Отображаем картинку без ссылки
												if (!is_null($ico))
												{
													?><i class="<?php echo htmlspecialchars($ico)?>" title="<?php echo htmlspecialchars(Core_Array::get($title_array, $value))?>"></i><?php
												}
												elseif (!is_null($src))
												{
													?><img src="<?php echo htmlspecialchars($src)?>" alt="<?php echo htmlspecialchars(Core_Array::get($alt_array, $value))?>" title="<?php echo htmlspecialchars(Core_Array::get($title_array, $value))?>" /><?php
												}
												/*elseif (!empty($link) && !isset($value_array[$value]))
												{
													// Картинки для такого значения не найдено, но есть ссылка
													?><a href="<?php echo $link?>" onclick="$('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight();<?php echo $onclick?> ">—</a><?php
												}*/
												else
												{
													// Картинки для такого значения не найдено
													?>—<?php
												}

												if (!empty($link) && !is_null($src))
												{
													?></a><?php
												}

											break;
											case 8: // Выпадающий список
												$oSelect = Admin_Form_Entity::factory('Select')
													->name($element_name)
													->id($element_name)
													->divAttr(array())
													->value($value)
													->onchange("$.setCheckbox('{$windowId}', '{$sCheckSelector}')");

												if (is_array($oAdmin_Form_Field_Changed->list))
												{
													$aValue = $oAdmin_Form_Field_Changed->list;
												}
												else
												{
													$aValue = array();

													$aListExplode = explode("\n", $oAdmin_Form_Field_Changed->list);
													foreach ($aListExplode as $str_value)
													{
														// Каждую строку разделяем по равно
														$str_explode = explode('=', $str_value);

														if (count($str_explode) > 1 /*&& $str_explode[1] != '…'*/)
														{
															// сохраняем в массив варинаты значений и ссылки для них
															$aValue[trim($str_explode[0])] = trim($str_explode[1]);
														}
													}
												}

												$oSelect
													->options($aValue)
													->execute();
											break;
											case 9: // Текст "AS IS"
												if (mb_strlen($value) != 0)
												{
													echo '<span>' . html_entity_decode($value, ENT_COMPAT, 'UTF-8') . '</span>';
												}
												else
												{
													?>&nbsp;<?php
												}
											break;
											case 11: // Текстовое поле
												if (!is_null($value))
												{
													?><textarea name="<?php echo $element_name?>" id="<?php echo $element_name?>" onchange="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" onkeydown="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" class="form-control"><?php echo $value?></textarea><?php
												}
											break;
											default: // Тип не определен.
												?>&nbsp;<?php
											break;
										}
									}
									// Вычисляемое поле с помощью функции обратного вызова
									else
									{
										if ($oAdmin_Form_Controller->isCallable($oEntity, $backendName))
										{
											echo $oEntity->$backendName($oAdmin_Form_Field_Changed, $oAdmin_Form_Controller);
										}
										elseif ($oAdmin_Form_Controller->isCallable($oEntity, $fieldName))
										{
											echo $oEntity->$fieldName($oAdmin_Form_Field_Changed, $oAdmin_Form_Controller);
										}
										elseif (property_exists($oEntity, $fieldName))
										{
											echo '<span>' . htmlspecialchars((string) $oEntity->$fieldName) . '</span>';
										}
									}
									Core_Event::notify('Admin_Form_Controller.onAfterShowField', $oAdmin_Form_Controller, array($oEntity, $oAdmin_Form_Field));
								}
								catch (Exception $e)
								{
									Core_Message::show('Caught exception: ' . $e->getMessage() . "\n", 'error');
								}

								// Badges
								if ($bBadge)
								{
									// Выполним функцию обратного вызова
									$oEntity->$badgesMethodName($oAdmin_Form_Field, $oAdmin_Form_Controller);
									?></div><?php
								}
								?></td><?php
							}
						}

						// Действия для строки в правом столбце
						if ($oAdmin_Form->show_operations && $oAdmin_Form_Controller->showOperations
							/*|| $allow_filter && $this->showFilter*/)
						{
							$sContents = '';

							$oCore_Html_Entity_Ul = Core_Html_Entity::factory('Ul')
								->class('dropdown-menu pull-right');

							?><td class="sticky-column"><?php

							$sActionsFullView = $sActionsShortView = '';

							$iActionsCount = 0;

							// Подмена массива действий через событие
							Core_Event::notify('Admin_Form_Controller.onBeforeShowActions', $oAdmin_Form_Controller, array($datasetKey, $oEntity, $aAllowed_Admin_Form_Actions));
							$aActions = Core_Event::getLastReturn();

							!is_array($aActions)
								&& $aActions = $aAllowed_Admin_Form_Actions;

							foreach ($aActions as $oAdmin_Form_Action)
							{
								// Перекрытие параметров для данного поля
								$oAdmin_Form_Action_Changed = $oAdmin_Form_Controller->changeAction($oAdmin_Form_Dataset, $oAdmin_Form_Action);

								// Отображаем действие, только если разрешено.
								if (!$oAdmin_Form_Action_Changed->single)
								{
									continue;
								}

								// Проверяем, привязано ли действие к определенному dataset'у.
								if ($oAdmin_Form_Action_Changed->dataset != -1
									&& $oAdmin_Form_Action_Changed->dataset != $datasetKey)
								{
									continue;
								}

								// Если у модели есть метод checkBackendAccess(), то проверяем права на это действие, совершаемое текущим пользователем
								if (method_exists($oEntity, 'checkBackendAccess') && !$oEntity->checkBackendAccess($oAdmin_Form_Action_Changed->name, $oUser))
								{
									continue;
								}

								// Проверка через user_id на право выполнения действия над объектом
								if (($oEntity instanceof Core_Entity) && !$oUser->checkObjectAccess($oEntity))
								{
									continue;
								}

								$iActionsCount++;

								$name = htmlspecialchars($oAdmin_Form_Action->getCaption($oAdmin_Language->id));

								Core_Event::notify('Admin_Form_Controller.onBeforeGetAdminActionLoadHref', $oAdmin_Form_Controller, array($oAdmin_Form_Action_Changed, $escapedDatasetKey, $escapedEntityKey));

								$mReturn = Core_Event::getLastReturn();
								$href = is_null($mReturn)
									// array('path', 'action', 'operation', 'datasetKey', 'datasetValue', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window')
									? $oAdmin_Form_Controller->getAdminActionLoadHref(array(
										'path' => $oAdmin_Form_Controller->getPath(),
										'action' => $oAdmin_Form_Action_Changed->name,
										'datasetKey' => $escapedDatasetKey,
										'datasetValue' => $escapedEntityKey,
										'window' => 'id_content'
									))
									: $mReturn;

								Core_Event::notify('Admin_Form_Controller.onBeforeGetAdminActionLoadAjax', $oAdmin_Form_Controller, array($oAdmin_Form_Action_Changed, $escapedDatasetKey, $escapedEntityKey));

								$mReturn = Core_Event::getLastReturn();
								$onclick = is_null($mReturn)
									? ($oAdmin_Form_Action_Changed->modal
										? $oAdmin_Form_Controller->getAdminActionModalLoad(array(
											'path' => $oAdmin_Form_Controller->getPath(), 'action' => $oAdmin_Form_Action_Changed->name,
											'operation' => 'modal',
											'datasetKey' => $datasetKey, 'datasetValue' => $entityKey,
											'width' => '90%'
										))
										: $oAdmin_Form_Controller->getAdminActionLoadAjax(array(
											'path' => $oAdmin_Form_Controller->getPath(), 'action' => $oAdmin_Form_Action_Changed->name,
											'datasetKey' => $datasetKey, 'datasetValue' => $entityKey
										))
									)
									: $mReturn;

								// Change onclick to true
								$oAdmin_Form_Action_Changed->new_window && $onclick = 'return true;';

								// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
								if ($oAdmin_Form_Action_Changed->confirm)
								{
									$onclick = "res = confirm('" .
										htmlspecialchars(Core::_('Admin_Form.confirm_dialog', $name)) .
										"'); if (!res) { $('#{$windowId} #row_{$escapedDatasetKey}_{$escapedEntityKey}').toggleHighlight(); } else {{$onclick}} return res;";
								}

								is_null($oAdmin_Form_Action_Changed->color) && $oAdmin_Form_Action_Changed->color = 'info';
								is_null($oAdmin_Form_Action_Changed->icon) && $oAdmin_Form_Action_Changed->icon = 'fa fa-bar';

								$aAttrs = isset($oAdmin_Form_Action_Changed->attrs)
									? $oAdmin_Form_Action_Changed->attrs
									: array();

								$aAttrs += array(
									'title' => $name,
									'href' => $href,
									'onclick' => "mainFormLocker.unlock(); $onclick"
								);

								$oAdmin_Form_Action_Changed->new_window
									&& $aAttrs['target'] = '_blank';

								$sActionsFullView .= '<a ' . $this->getAttrString($aAttrs) . ' class="btn btn-xs btn-' . htmlspecialchars($oAdmin_Form_Action_Changed->color) .' "><i class="' . htmlspecialchars($oAdmin_Form_Action_Changed->icon) . '"></i></a>';

								$sActionsShortView .= '<li><a ' . $this->getAttrString($aAttrs) . '><i class="' . htmlspecialchars($oAdmin_Form_Action_Changed->icon) . ' fa-fw btn-sm btn-' . htmlspecialchars($oAdmin_Form_Action_Changed->color) . '"></i>' . $name . '</a></li>';

								/*$sActionsFullView .= '<a title="' . $name . '" href="' . $href . '" onclick="mainFormLocker.unlock(); ' . $onclick .'" class="btn btn-xs btn-' . htmlspecialchars($oAdmin_Form_Action_Changed->color) .' "><i class="' . htmlspecialchars($oAdmin_Form_Action_Changed->icon) . '"></i></a>';

								$sActionsShortView .= '<li><a title="' . $name . '" href="' . htmlspecialchars($href) . '" onclick="mainFormLocker.unlock(); ' . $onclick .'"><i class="' . htmlspecialchars($oAdmin_Form_Action_Changed->icon) . ' fa-fw btn-sm btn-' . htmlspecialchars($oAdmin_Form_Action_Changed->color) . '"></i>' . $name . '</a></li>';*/
							}

							if ($iActionsCount)
							{
								?><div class="btn-group <?php echo $iActionsCount > 1 ? 'visible-md visible-lg' : ''?>"><?php echo $sActionsFullView?></div><?php

								if ($iActionsCount > 1)
								{
								?><div class="visible-xs visible-sm"><div class="btn-group">
									<button class="btn btn-palegreen btn-xs dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i></button>
									<ul class="dropdown-menu actions-dropdown-menu dropdown-menu-right" role="menu"><?php
									echo $sActionsShortView;
									?></ul>
								</div></div><?php
								}
							}
							?></td><?php
						}
						?></tr><?php
					}
				}
			}
			?>
			</table>
		</div>
		<?php

		if (Core_Array::get($oAdmin_Form_Controller->filterSettings, 'show'))
		{
			?><script>$.toggleFilter();</script><?php
		}

		//Core_Event::notify('Admin_Form_Controller.onAfterShowContent', $oAdmin_Form_Controller);

		return $this;
	}

	protected function getAttrString(array $attr)
	{
		$return = '';
		foreach ($attr as $key => $value)
		{
			$return .= ' ' . $key . '="' . $value . '"';
		}

		return $return;
	}

	/**
	 * Show action panel in administration center
	 * @return self
	 */
	public function bottomActions()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

		// Строка с действиями
		//if ($this->_showBottomActions)
		//{
			// $windowId = $oAdmin_Form_Controller->getWindowId();

			// Текущий пользователь
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}

			// Доступные действия для пользователя
			$aAllowed_Admin_Form_Actions = $oAdmin_Form_Controller->getAdminFormActions();

			// Групповые операции
			if ($oAdmin_Form->show_group_operations && !empty($aAllowed_Admin_Form_Actions))
			{
				?><div class="dataTables_actions"><?php
				$sActionsFullView = $sActionsShortView = '';

				$iGroupCount = 0;

				$aAdminFormActionsByDir = array();

				foreach ($aAllowed_Admin_Form_Actions as $oAdmin_Form_Action)
				{
					if ($oAdmin_Form_Action->group)
					{
						$iGroupCount++;

						if (!$oAdmin_Form_Action->admin_form_action_dir_id)
						{
							$text = htmlspecialchars($oAdmin_Form_Action->getCaption($oAdmin_Language->id));

							$href = $oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name);
							$onclick = $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name);

							// Нужно подтверждение для действия
							if ($oAdmin_Form_Action->confirm)
							{
								// $href .= '';

								$onclick = "res = confirm('" . Core::_('Admin_Form.confirm_dialog', htmlspecialchars($text)) . "'); if (res) { {$onclick} } else {return false}";

								// $link_class = 'admin_form_action_alert_link';
							}
							// else
							// {
							// 	$link_class = 'admin_form_action_link';
							// }

							// ниже по тексту alt-ы и title-ы не выводятся, т.к. они дублируются текстовыми
							// надписями и при отключении картинок текст дублируется
							/* alt="<?php echo htmlspecialchars($text)?>"*/

							$sActionsFullView .= '<li><a title="' . htmlspecialchars($text) . '" href="' . $href . '" onclick="mainFormLocker.unlock(); ' . $onclick .'"><i class="' . htmlspecialchars($oAdmin_Form_Action->icon) . ' fa-fw btn-sm btn-' . htmlspecialchars($oAdmin_Form_Action->color) . '"></i>' . htmlspecialchars($text) . '</a></li>';

							$sActionsShortView .= '<a href="' . htmlspecialchars($href) . '" onclick="mainFormLocker.unlock(); ' . $onclick . '" class="btn-labeled btn btn-'. htmlspecialchars($oAdmin_Form_Action->color) . '"><i class="btn-label ' . htmlspecialchars($oAdmin_Form_Action->icon) . '"></i>' . htmlspecialchars($text) . '</a>';
						}
						else
						{
							$aAdminFormActionsByDir[$oAdmin_Form_Action->admin_form_action_dir_id][] = $oAdmin_Form_Action;
						}
					}
				}

				// Сгруппированные действия
				$aAdmin_Form_Action_Dirs = $oAdmin_Form->Admin_Form_Action_Dirs->findAll();
				foreach ($aAdmin_Form_Action_Dirs as $oAdmin_Form_Action_Dir)
				{
					if (isset($aAdminFormActionsByDir[$oAdmin_Form_Action_Dir->id]))
					{
						$icon = $oAdmin_Form_Action_Dir->icon != ''
							? htmlspecialchars($oAdmin_Form_Action_Dir->icon)
							: 'fa fa-bars';

						$additionalClass = $oAdmin_Form_Action_Dir->getWordName() == ''
							? ' no-margin-right no-padding-right'
							: '';

						$color = $oAdmin_Form_Action_Dir->color != ''
							? htmlspecialchars($oAdmin_Form_Action_Dir->color)
							: 'palegreen';

						$sActionsShortView .= '<div class="btn-group dropup">
							<a class="btn btn-' . $color . ' dropdown-toggle" data-toggle="dropdown">
								<i class="' . $icon . ' icon-separator ' . $additionalClass . '"></i>' .
								htmlspecialchars($oAdmin_Form_Action_Dir->getWordName()) .
							'</a>
							<ul class="dropdown-menu">';

						foreach ($aAdminFormActionsByDir[$oAdmin_Form_Action_Dir->id] as $key => $oAdmin_Form_Action)
						{
							$text = htmlspecialchars($oAdmin_Form_Action->getCaption($oAdmin_Language->id));

							$href = $oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name);
							$onclick = $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name);

							// Нужно подтверждение для действия
							if ($oAdmin_Form_Action->confirm)
							{
								$onclick = "res = confirm('" . Core::_('Admin_Form.confirm_dialog', htmlspecialchars($text)) . "'); if (res) { {$onclick} } else {return false}";
							}

							$divider = $key == 0
								? '<li class="divider"></li>'
								: '';

							$sActionsFullView .= $divider . '<li><a title="' . htmlspecialchars($text) . '" href="' . $href . '" onclick="mainFormLocker.unlock(); ' . $onclick .'"><i class="' . htmlspecialchars($oAdmin_Form_Action->icon) . ' fa-fw btn-sm btn-' . htmlspecialchars($oAdmin_Form_Action->color) . '"></i>' . htmlspecialchars($text) . '</a></li>';

							$sActionsShortView .= '<li><a title="' . htmlspecialchars($text) . '" href="' . $href . '" onclick="mainFormLocker.unlock(); ' . $onclick .'"><i class="' . htmlspecialchars($oAdmin_Form_Action->icon) . ' fa-fw btn-sm btn-' . htmlspecialchars($oAdmin_Form_Action->color) . '"></i>' . htmlspecialchars($text) . '</a></li>';
						}

						$sActionsShortView .= '</ul>
						</div>';
					}
				}


				if ($iGroupCount > 1)
				{
					?><div class="visible-sm visible-xs">
						<div class="btn-group dropup">
							<a class="btn btn-palegreen dropdown-toggle" data-toggle="dropdown">
								<i class="fa fa-bars icon-separator"></i>
								<?php echo Core::_('Admin_Form.actions')?>
							</a>
							<ul class="dropdown-menu">
								<?php echo $sActionsFullView?>
							</ul>
						</div>
					</div><?php
				}

				?><div <?php echo $iGroupCount > 1 ? 'class="hidden-sm hidden-xs"' : ''?>>
					<?php echo $sActionsShortView?>
				</div>
			</div>
			<?php
			}
		//}

		return $this;
	}

	/**
	 * Get user fields
	 * @param object $oAdmin_Form_Controller
	 * @return array
	 */
	protected function _getFields($oAdmin_Form_Controller)
	{
		$aFields = array();

		if (Core::moduleIsActive('field'))
		{
			$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();
			$oUser = Core_Auth::getCurrentUser();

			$aAvailableFields = $oAdmin_Form->getAvailableFieldsForUser($oUser->id);

			$aModelNames = array();
			$aDatasets = $oAdmin_Form_Controller->getDatasets();
			foreach ($aDatasets as $datasetKey => $oAdmin_Form_Dataset)
			{
				$oEntity = $oAdmin_Form_Dataset->getEntity();
				if ($oEntity instanceof Core_Entity)
				{
					$aModelNames[] = $oEntity->getModelName();
				}
			}

			$aAdmin_Form_Fields = Admin_Form_Controller::getFields(CURRENT_SITE, $aModelNames);
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				if (isset($aAvailableFields[$oAdmin_Form_Field->id]))
				{
					$field_id = intval(filter_var($oAdmin_Form_Field->id, FILTER_SANITIZE_NUMBER_INT));

					$oField = Core_Entity::factory('Field')->getById($field_id, FALSE);

					if (!is_null($oField))
					{
						$aFields[] = $oField;
					}
				}
			}
		}

		return $aFields;
	}

	/**
	 * Get value of Field_Value
	 * @param Field_Model $oField
	 * @param mixed $oField_Value
	 * @param mixed $object
	 * @return string
	 */
	protected function _getFieldValue($oField, $oField_Value, $object)
	{
		switch ($oField->type)
		{
			case 0: // Int
			case 1: // String
			case 4: // Textarea
			case 6: // Wysiwyg
			case 7: // Checkbox
			case 10: // Hidden field
			case 11: // Float
				$result = $oField_Value->value;
			break;
			/*case 2: // File
				$href = method_exists($object, 'getItemHref')
					? $object->getItemHref()
					: $object->getGroupHref();

				$result = $oField_Value->file == ''
					? ''
					: $oField_Value
						->setHref($href)
						->getLargeFileHref();
			break;*/
			case 3: // List
				$result = $this->_getListValue($oField_Value->value);
			break;
			case 5: // Informationsystem
				$result = $oField_Value->value
					? $oField_Value->Informationsystem_Item->name
					: '';
			break;
			case 8: // Date
				$result = Core_Date::sql2date($oField_Value->value);
			break;
			case 9: // Datetime
				$result = Core_Date::sql2datetime($oField_Value->value);
			break;
			case 12: // Shop
				$result = $oField_Value->value
					? $oField_Value->Shop_Item->name
					: '';
			break;
			default:
				$result = $oField_Value->value;
		}

		return $result;
	}

	protected $_cacheGetListValue = array();

	protected function _getListValue($list_item_id)
	{
		if ($list_item_id && Core::moduleIsActive('list'))
		{
			if (!isset($this->_cacheGetListValue[$list_item_id]))
			{
				$oList_Item = Core_Entity::factory('List_Item')->getByid($list_item_id, FALSE);

				$this->_cacheGetListValue[$list_item_id] = $oList_Item ? $oList_Item->value : '';
			}

			return $this->_cacheGetListValue[$list_item_id];
		}

		return '';
	}
}