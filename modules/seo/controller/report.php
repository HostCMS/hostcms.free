<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SEO.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Controller_Report extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title(
			Core::_('Seo.report_title', Core_Entity::factory('Site', $this->_object->site_id)->name)
		);

		$oMainTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('admin_form.form_forms_tab_1'))
			->name('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$this->addTab($oMainTab);

		$oMainRow1->add(Admin_Form_Entity::factory('Datetime')
			->caption(Core::_('Seo.start_datetime'))
			->name('start_datetime')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->value(Core_Date::timestamp2sql(time() - 2678400)));

		$oMainRow2->add($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));
		$oMainRow3->add(Admin_Form_Entity::factory('Input')
			->caption(Core::_('Seo.count'))
			->name('count')
			->value(10)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->format(array(
					'lib' => array('value' => 'positiveInteger'),
					'minlen' => array('value' => 1)
				)
			));

		$oMainRow4->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.tcy'))
			->name('tcy')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		// Закладка обратных ссылок
		$oLinksTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_links'))
			->name('links');

		$oLinksTab
			->add($oLinksTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oLinksTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oLinksTabRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->addTabAfter($oLinksTab, $oMainTab);

		$oLinksTabRow1->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.yandex'))
			->name('yandex_links')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		$oLinksTabRow2->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.google'))
			->name('google_links')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		$oLinksTabRow3->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.bing'))
			->name('bing_links')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		// Закладка проиндексированных страниц
		$oIndexedTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_indexed'))
			->name('indexed');

		$oIndexedTab
			->add($oIndexedTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oIndexedTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oIndexedTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oIndexedTabRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->addTabAfter($oIndexedTab, $oLinksTab);

		$oIndexedTabRow1->add(Admin_Form_Entity::factory('Checkbox')
				->caption(Core::_('Seo.yandex'))
				->name('yandex_indexed')
				->value(1)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		$oIndexedTabRow2->add(Admin_Form_Entity::factory('Checkbox')
				->caption(Core::_('Seo.google'))
				->name('google_indexed')
				->value(1)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		$oIndexedTabRow3->add(Admin_Form_Entity::factory('Checkbox')
				->caption(Core::_('Seo.yahoo'))
				->name('yahoo_indexed')
				->value(1)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		$oIndexedTabRow4->add(Admin_Form_Entity::factory('Checkbox')
				->caption(Core::_('Seo.bing'))
				->name('bing_indexed')
				->value(1)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		// Закладка каталогов
		$oCatalogTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_catalog'))
			->name('catalog');

		$oCatalogTab
			->add($oCatalogTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCatalogTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCatalogTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCatalogTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$this->addTabAfter($oCatalogTab, $oIndexedTab);

		$oCatalogTabRow1->add($this->getField('yandex_catalog')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));
		$oCatalogTabRow2->add($this->getField('rambler_catalog')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));
		$oCatalogTabRow3->add($this->getField('dmoz_catalog')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));
		$oCatalogTabRow4->add($this->getField('mail_catalog')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));

		// Закладка счетчиков
		$oCounterTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_counter'))
			->name('counter');

		$oCounterTab
			->add($oCounterTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow5 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$this->addTabAfter($oCounterTab, $oCatalogTab);

		// Закладка счетчиков
		$oCounterTabRow1->add($this->getField('rambler_counter')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));
		$oCounterTabRow2->add($this->getField('spylog_counter')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));
		$oCounterTabRow3->add($this->getField('hotlog_counter')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));
		$oCounterTabRow4->add($this->getField('liveinternet_counter')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));
		$oCounterTabRow5->add($this->getField('mail_counter')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->value(1));

		// Позиции в поисковых системах
		$oPositionTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_search_system'))
			->name('position');

		$oPositionTab
			->add($oPositionTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPositionTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPositionTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPositionTabRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->addTabAfter($oPositionTab, $oCounterTab);

		$oPositionTabRow1->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.yandex'))
			->name('yandex_position')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));
		$oPositionTabRow2->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.google'))
			->name('google_position')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));
		$oPositionTabRow3->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.yahoo'))
			->name('yahoo_position')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));
		$oPositionTabRow4->add(Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Seo.bing'))
			->name('bing_position')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

		return $this;
	}

	/**
	 * Add form buttons
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _addButtons()
	{
		// Кнопки
		$oAdmin_Form_Entity_Buttons = Admin_Form_Entity::factory('Buttons');

		// Кнопка "Отправить"
		$oAdmin_Form_Entity_Button_Send = Admin_Form_Entity::factory('Button')
			->name('generate')
			->class('applyButton btn btn-blue')
			->value(Core::_('Seo.generate'))
			->onclick($this->_Admin_Form_Controller->getAdminSendForm(NULL, 'generate'));

		$oAdmin_Form_Entity_Buttons->add($oAdmin_Form_Entity_Button_Send);

		return $oAdmin_Form_Entity_Buttons;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		switch ($operation)
		{
			case NULL: // Показ формы
				$this->_Admin_Form_Controller->title(
					$this->title
				);

				return $this->_showEditForm();

			case 'generate':

				ob_start();

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$start_datetime = Core_Date::datetime2sql(Core_Array::get($this->_formValues, 'start_datetime', Core_Date::timestamp2sql(time() - 2678400)));
				$end_datetime = Core_Date::datetime2sql(Core_Array::get($this->_formValues, 'datetime', $this->_object->datetime));

				$oSite = Core_Entity::factory('Site', $this->_object->site_id);

				$aSeo = $oSite->Seos->getByDatetime($start_datetime, $end_datetime);

				$count = intval(Core_Array::get($this->_formValues, 'count', 10));

				$param = array('arrow' => TRUE);


				$oCore_Html_Entity_Table_For_Clone = Core::factory('Core_Html_Entity_Table')
					->class('admin-table table table-bordered table-hover table-striped');
				?>
				<script type="text/javascript">
				// Настройки графиков
				var gridbordercolor = "#eee", optionsForGraph = {
						series: {
							lines: {show: true}
						},
						legend: {
							noColumns: 4,
							backgroundOpacity: 0.65
						},
						xaxis: {
							mode: "time",
							timeformat: "%d.%m.%Y",
							color: gridbordercolor,
							timezone: "browser"
						},
						yaxis: {
							min: 0,
							color: gridbordercolor
						},
						grid: {
							hoverable: true,
							clickable: false,
							borderWidth: 0,
							aboveData: false
						},
						tooltip: true,
						tooltipOpts: {
							defaultTheme: false,
							dateFormat: "%d.%m.%Y",
							content: "<b>%s</b> : <span>%x</span> : <span>%y</span>"
						},
						selection: {
							mode: "x"
						}
					}
				</script>
				<?php
				$site_id = intval(Core_Array::getGet('site_id', CURRENT_SITE));

				//Если в отчете должен быть тИЦ
				if (intval(Core_Array::get($this->_formValues, 'tcy')))
				{
					$aTcyTitlesPerDay = array();
					$aTcyValuesPerDay = array();

					foreach ($aSeo as $oSeo)
					{
						$aTcyTitlesPerDay[] = strtotime($oSeo->datetime) * 1000;
						$aTcyValuesPerDay[] = $oSeo->tcy;
					}
					?>
					<div class="widget">
						<div class="widget-header bordered-bottom bordered-themeprimary">
							<i class="widget-icon fa fa-bar-chart-o themeprimary"></i>
							<span class="widget-caption themeprimary"><?php echo Core::_('Seo.tcy') ?></span>
							<div class="widget-buttons">
								<a href="#" data-toggle="maximize">
									<i class="fa fa-expand pink"></i>
								</a>
								<a href="#" data-toggle="collapse">
									<i class="fa fa-minus blue"></i>
								</a>
								<a href="#" data-toggle="dispose">
									<i class="fa fa-times darkorange"></i>
								</a>
							</div>
						</div>
						<div class="widget-body">
							<div id="tcy">
								<div class="row">
									<div class="col-sm-12">
										<div id="tcy-chart" class="chart chart-lg"></div>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-sm-12">
										<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Seo.reset')?></button>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<div class="table-scrollable">
										<?php
										$oCore_Html_Entity_Table = clone $oCore_Html_Entity_Table_For_Clone;

										// Не показываем некоторые столбцы, если общее количество таковых больше максимально разрешенного
										$report = $this->_buildMassReport($aSeo, "tcy", $count);

										// Дата
										$oCore_Html_Entity_Table
											->add(
												Core::factory('Core_Html_Entity_Thead')
													->add($this->_showTableTitleReport($report))
											);

										// тИЦ
										$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.tcy'), "tcy", $param));

										$oCore_Html_Entity_Table->execute();
										?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<script type="text/javascript">
						$(function(){
							var tcyTitlesPerDay = [<?php echo implode(',',$aTcyTitlesPerDay)?>],
								tcyValuesPerDay = [<?php echo implode(',', $aTcyValuesPerDay)?>],
								tcyValueTitlesPerDay = [];

								for(var i = 0; i < tcyValuesPerDay.length; i++) {
									tcyValueTitlesPerDay.push([new Date(tcyTitlesPerDay[i]), tcyValuesPerDay[i]]);
								}

							var themeprimary = getThemeColorFromCss('themeprimary'), dataTcyPerDay = [{
								color: themeprimary,
								label: "<?php echo Core::_('Seo.tcy') ?>",
								data: tcyValueTitlesPerDay
							}],

							placeholderTcyPerDay = $("#tcy-chart");

							placeholderTcyPerDay.bind("plotselected", function (event, ranges) {
									plotTcyPerDay = $.plot(placeholderTcyPerDay, dataTcyPerDay, $.extend(true, {}, optionsForGraph, {
										xaxis: {
											min: ranges.xaxis.from,
											max: ranges.xaxis.to
										}
									}));
							});

							$('<?php echo '#' . $windowId ?> #tcy #setOriginalZoom').on('click', function(){
								plotTcyPerDay = $.plot(placeholderTcyPerDay, dataTcyPerDay, optionsForGraph);
							});

							var plotTcyPerDay = $.plot(placeholderTcyPerDay, dataTcyPerDay, optionsForGraph);
						})
					</script>

					<?php
				}

				$google_links = intval(Core_Array::get($this->_formValues, 'google_links', 0));
				$yandex_links = intval(Core_Array::get($this->_formValues, 'yandex_links', 0));
				//$yahoo_links = intval(Core_Array::get($this->_formValues, 'yahoo_links', 0));
				$bing_links = intval(Core_Array::get($this->_formValues, 'bing_links', 0));

				if ($google_links || $yandex_links /*|| $yahoo_links*/ || $bing_links)
				{
					$aLinksTitlesPerDay = array();
					$aGoogleValuesPerDay = array();
					$aYandexValuesPerDay = array();
					$aBingValuesPerDay = array();

					foreach ($aSeo as $oSeo)
					{
						$aLinksTitlesPerDay[] = strtotime($oSeo->datetime) * 1000;

						$google_links && $aGoogleValuesPerDay[] = $oSeo->google_links;
						$yandex_links && $aYandexValuesPerDay[] = $oSeo->yandex_links;
						$bing_links && $aBingValuesPerDay[] = $oSeo->bing_links;
					}
					?>
					<div class="widget">
						<div class="widget-header bordered-bottom bordered-themeprimary">
							<i class="widget-icon fa fa-bar-chart-o themeprimary"></i>
							<span class="widget-caption themeprimary"><?php echo Core::_('Seo.report_links') ?></span>
							<div class="widget-buttons">
								<a href="#" data-toggle="maximize">
									<i class="fa fa-expand pink"></i>
								</a>
								<a href="#" data-toggle="collapse">
									<i class="fa fa-minus blue"></i>
								</a>
								<a href="#" data-toggle="dispose">
									<i class="fa fa-times darkorange"></i>
								</a>
							</div>
						</div>
						<div class="widget-body">
							<div id="links">
								<div class="row">
									<div class="col-sm-12">
										<div id="links-chart" class="chart chart-lg"></div>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-sm-12">
										<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Seo.reset')?></button>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<div class="table-scrollable">
										<?php
										$oCore_Html_Entity_Table = clone $oCore_Html_Entity_Table_For_Clone;

										// Не показываем некоторые столбцы, если общее количество таковых больше максимально разрешенного
										$report = $this->_buildMassReport($aSeo, "links", $count);

										// Дата
										$oCore_Html_Entity_Table
											//->add($this->_showTableTitleReport($report));
											->add(
													Core::factory('Core_Html_Entity_Thead')
														->add($this->_showTableTitleReport($report))
												);

										if ($google_links)
										{
											$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.google'), "google_links", $param));
										}

										if ($yandex_links)
										{
											$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.yandex'), "yandex_links", $param));
										}

										if ($bing_links)
										{
											$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.bing'), "bing_links", $param));
										}

										$oCore_Html_Entity_Table->execute();
										?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<script type="text/javascript">
						$(function(){
							var linksTitlesPerDay = [<?php echo implode(',',$aLinksTitlesPerDay)?>],
								themeprimary = getThemeColorFromCss('themeprimary'),
								dataLinksPerDay = [];

							<?php
							if ($google_links || $yandex_links || $bing_links)
							{
							?>
								for(var i = 0; i < linksTitlesPerDay.length; i++)
								{
									linksTitlesPerDay[i] = new Date(linksTitlesPerDay[i]);
								}
							<?php
							}

							if ($google_links)
							{
							?>
								googleValuesPerDay = [<?php echo implode(',', $aGoogleValuesPerDay)?>],
								googleValueTitlesPerDay = [];

								for(var i = 0; i < linksTitlesPerDay.length; i++) {
									googleValueTitlesPerDay.push([linksTitlesPerDay[i], googleValuesPerDay[i]]);
								}

								dataLinksPerDay.push({
									color: themeprimary,
									label: "<?php echo Core::_('Seo.google') ?>",
									data: googleValueTitlesPerDay
								})
							<?php
							}

							if ($yandex_links)
							{
							?>
								yandexValuesPerDay = [<?php echo implode(',', $aYandexValuesPerDay)?>],
								yandexValueTitlesPerDay = [];

								for(var i = 0; i < linksTitlesPerDay.length; i++) {
									yandexValueTitlesPerDay.push([linksTitlesPerDay[i], yandexValuesPerDay[i]]);
								}

								dataLinksPerDay.push({
									color: themesecondary,
									label: "<?php echo Core::_('Seo.yandex') ?>",
									data: yandexValueTitlesPerDay
								})
							<?php
							}

							if ($bing_links)
							{
							?>
								bingValuesPerDay = [<?php echo implode(',', $aBingValuesPerDay)?>],
								bingValueTitlesPerDay = [];

								for(var i = 0; i < linksTitlesPerDay.length; i++) {
									bingValueTitlesPerDay.push([linksTitlesPerDay[i], bingValuesPerDay[i]]);
								}

								dataLinksPerDay.push({
									color: themethirdcolor,
									label: "<?php echo Core::_('Seo.bing') ?>",
									data: bingValueTitlesPerDay
								})
							<?php
							}
							?>

							var placeholderLinksPerDay = $("#links-chart");

							placeholderLinksPerDay.bind("plotselected", function (event, ranges) {
									plotLinksPerDay = $.plot(placeholderLinksPerDay, dataLinksPerDay, $.extend(true, {}, optionsForGraph, {
										xaxis: {
											min: ranges.xaxis.from,
											max: ranges.xaxis.to
										}
									}));
							});

							$('<?php echo '#' . $windowId ?> #links #setOriginalZoom').on('click', function(){
								plotLinksPerDay = $.plot(placeholderLinksPerDay, dataLinksPerDay, optionsForGraph);
							});

							var plotLinksPerDay = $.plot(placeholderLinksPerDay, dataLinksPerDay, optionsForGraph);
						})
					</script>

					<?php
				}

				$yandex_indexed = intval(Core_Array::get($this->_formValues, 'yandex_indexed', 0));
				$yahoo_indexed = intval(Core_Array::get($this->_formValues, 'yahoo_indexed', 0));
				$bing_indexed = intval(Core_Array::get($this->_formValues, 'bing_indexed', 0));
				$google_indexed = intval(Core_Array::get($this->_formValues, 'google_indexed', 0));

				if ($yandex_indexed || $yahoo_indexed || $bing_indexed /*|| $rambler_indexed*/ || $google_indexed)
				{
					$aIndexedTitlesPerDay = array();
					$aGoogleValuesPerDay = array();
					$aYandexValuesPerDay = array();
					$aBingValuesPerDay = array();
					$aYahooValuesPerDay = array();

					foreach ($aSeo as $oSeo)
					{
						$aIndexedTitlesPerDay[] = strtotime($oSeo->datetime) * 1000;

						$google_indexed && $aGoogleValuesPerDay[] = $oSeo->google_indexed;
						$yandex_indexed && $aYandexValuesPerDay[] = $oSeo->yandex_indexed;
						$bing_indexed && $aBingValuesPerDay[] = $oSeo->bing_indexed;
						$yahoo_indexed && $aYahooValuesPerDay[] = $oSeo->yahoo_indexed;
					}
					?>
					<div class="widget">
						<div class="widget-header bordered-bottom bordered-themeprimary">
							<i class="widget-icon fa fa-bar-chart-o themeprimary"></i>
							<span class="widget-caption themeprimary"><?php echo Core::_('Seo.report_indexed') ?></span>
							<div class="widget-buttons">
								<a href="#" data-toggle="maximize">
									<i class="fa fa-expand pink"></i>
								</a>
								<a href="#" data-toggle="collapse">
									<i class="fa fa-minus blue"></i>
								</a>
								<a href="#" data-toggle="dispose">
									<i class="fa fa-times darkorange"></i>
								</a>
							</div>
						</div>
						<div class="widget-body">
							<div id="indexed">
								<div class="row">
									<div class="col-sm-12">
										<div id="indexed-chart" class="chart chart-lg"></div>
									</div>
								</div>
								<div class="row">
									<div class="form-group col-sm-12">
											<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Seo.reset')?></button>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<div class="table-scrollable">
										<?php
										// Не показываем некоторые столбцы, если общее количество таковых больше максимально разрешенного
										$report = $this->_buildMassReport($aSeo, "indexed", $count);

										$oCore_Html_Entity_Table = clone $oCore_Html_Entity_Table_For_Clone;

										// Дата
										$oCore_Html_Entity_Table
											->add(
													Core::factory('Core_Html_Entity_Thead')
														->add($this->_showTableTitleReport($report))
												);

										// Yandex
										if ($yandex_indexed)
										{
											$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.yandex'), "yandex_indexed", $param));
										}

										// Google
										if ($google_indexed)
										{
											$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.google'), "google_indexed", $param));
										}

										// Yahoo
										if ($yahoo_indexed)
										{
											$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.yahoo'), "yahoo_indexed", $param));
										}

										// Bing.com
										if ($bing_indexed)
										{
											$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.bing'), "bing_indexed", $param));
										}

										$oCore_Html_Entity_Table->execute();
										?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<script type="text/javascript">
						$(function(){
							var indexedTitlesPerDay = [<?php echo implode(',', $aIndexedTitlesPerDay)?>],
								themeprimary = getThemeColorFromCss('themeprimary'),
								dataIndexedPerDay = [];

							<?php
							if ($google_indexed || $yandex_indexed || $bing_indexed || $yahoo_indexed)
							{
							?>
								for(var i = 0; i < indexedTitlesPerDay.length; i++)
								{
									indexedTitlesPerDay[i] = new Date(indexedTitlesPerDay[i]);
								}
							<?php
							}

							if ($google_indexed)
							{
							?>
								googleValuesPerDay = [<?php echo implode(',', $aGoogleValuesPerDay)?>],
								googleValueTitlesPerDay = [];

								for(var i = 0; i < indexedTitlesPerDay.length; i++) {
									googleValueTitlesPerDay.push([indexedTitlesPerDay[i], googleValuesPerDay[i]]);
								}

								dataIndexedPerDay.push({
									color: themeprimary,
									label: "<?php echo Core::_('Seo.google') ?>",
									data: googleValueTitlesPerDay
								})
							<?php
							}

							if ($yandex_indexed)
							{
							?>
								yandexValuesPerDay = [<?php echo implode(',', $aYandexValuesPerDay)?>],
								yandexValueTitlesPerDay = [];

								for(var i = 0; i < indexedTitlesPerDay.length; i++) {
									yandexValueTitlesPerDay.push([indexedTitlesPerDay[i], yandexValuesPerDay[i]]);
								}

								dataIndexedPerDay.push({
									color: themesecondary,
									label: "<?php echo Core::_('Seo.yandex') ?>",
									data: yandexValueTitlesPerDay
								})
							<?php
							}

							if ($bing_indexed)
							{
							?>
								bingValuesPerDay = [<?php echo implode(',', $aBingValuesPerDay)?>],
								bingValueTitlesPerDay = [];

								for(var i = 0; i < indexedTitlesPerDay.length; i++) {
									bingValueTitlesPerDay.push([indexedTitlesPerDay[i], bingValuesPerDay[i]]);
								}

								dataIndexedPerDay.push({
									color: themethirdcolor,
									label: "<?php echo Core::_('Seo.bing') ?>",
									data: bingValueTitlesPerDay
								})
							<?php
							}

							if ($yahoo_indexed)
							{
							?>
								yahooValuesPerDay = [<?php echo implode(',', $aYahooValuesPerDay)?>],
								yahooValueTitlesPerDay = [];

								for(var i = 0; i < indexedTitlesPerDay.length; i++) {
									yahooValueTitlesPerDay.push([indexedTitlesPerDay[i], yahooValuesPerDay[i]]);
								}

								dataIndexedPerDay.push({
									color: themefourthcolor,
									label: "<?php echo Core::_('Seo.yahoo') ?>",
									data: yahooValueTitlesPerDay
								})
							<?php
							}
							?>

							var placeholderIndexedPerDay = $("#indexed-chart");

							placeholderIndexedPerDay.bind("plotselected", function (event, ranges) {
									plotIndexedPerDay = $.plot(placeholderIndexedPerDay, dataIndexedPerDay, $.extend(true, {}, optionsForGraph, {
										xaxis: {
											min: ranges.xaxis.from,
											max: ranges.xaxis.to
										}
									}));
							});

							$('<?php echo '#' . $windowId ?> #indexed #setOriginalZoom').on('click', function(){
								plotIndexedPerDay = $.plot(placeholderIndexedPerDay, dataIndexedPerDay, optionsForGraph);
							});

							var plotIndexedPerDay = $.plot(placeholderIndexedPerDay, dataIndexedPerDay, optionsForGraph);
						})
					</script>

					<?php
				}

				$yandex_catalog = intval(Core_Array::get($this->_formValues, 'yandex_catalog', 0));
				$rambler_catalog = intval(Core_Array::get($this->_formValues, 'rambler_catalog', 0));
				$dmoz_catalog = intval(Core_Array::get($this->_formValues, 'dmoz_catalog', 0));
				$mail_catalog = intval(Core_Array::get($this->_formValues, 'mail_catalog', 0));

				$param['status'] = TRUE;

				//Если в отчете должны быть данные о каталогах
				if ($yandex_catalog || $rambler_catalog || $dmoz_catalog || $mail_catalog)
				{
					$oCore_Html_Entity_Table = clone $oCore_Html_Entity_Table_For_Clone;

					// Не показываем некоторые столбцы, если общее количество таковых больше максимально разрешенного
					$report = $this->_buildMassReport($aSeo, "catalog", $count);

					// Дата
					$oCore_Html_Entity_Table
						->add(
							Core::factory('Core_Html_Entity_Thead')
								->add($this->_showTableTitleReport($report))
						);

					// Яндекс каталог
					if ($yandex_catalog)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.yandex'), "yandex_catalog", $param));
					}

					// Рамблер каталог
					if ($rambler_catalog)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.rambler'), "rambler_catalog", $param));
					}

					// Dmoz каталог
					if ($dmoz_catalog)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.dmoz_catalog'), "dmoz_catalog", $param));
					}

					// Mail каталог
					if ($mail_catalog)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.mail_catalog'), "mail_catalog", $param));
					}
					?>
					<div class="widget">
						<div class="widget-header bordered-bottom bordered-themeprimary">
							<i class="widget-icon fa fa-bar-chart-o themeprimary"></i>
							<span class="widget-caption themeprimary"><?php echo Core::_('Seo.report_catalog') ?></span>
							<div class="widget-buttons">
								<a href="#" data-toggle="maximize">
									<i class="fa fa-expand pink"></i>
								</a>
								<a href="#" data-toggle="collapse">
									<i class="fa fa-minus blue"></i>
								</a>
								<a href="#" data-toggle="dispose">
									<i class="fa fa-times darkorange"></i>
								</a>
							</div>
						</div>
						<div class="widget-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="table-scrollable">
									<?php
									$oCore_Html_Entity_Table->execute();
									?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				}

				$spylog_counter = intval(Core_Array::get($this->_formValues, 'spylog_counter', 0));
				$rambler_counter = intval(Core_Array::get($this->_formValues, 'rambler_counter', 0));
				$hotlog_counter = intval(Core_Array::get($this->_formValues, 'hotlog_counter', 0));
				$liveinternet_counter = intval(Core_Array::get($this->_formValues, 'liveinternet_counter', 0));
				$mail_counter = intval(Core_Array::get($this->_formValues, 'mail_counter', 0));

				// Если в отчете должны быть данные о наличии счетчиков
				if ($spylog_counter || $rambler_counter || $hotlog_counter || $liveinternet_counter || $mail_counter)
				{
					$oCore_Html_Entity_Table = clone $oCore_Html_Entity_Table_For_Clone;

					// Не показываем некоторые столбцы, если общее количество таковых больше максимально разрешенного
					$report = $this->_buildMassReport($aSeo, "counter", $count);

					// Дата
					$oCore_Html_Entity_Table
						->add(
							Core::factory('Core_Html_Entity_Thead')
								->add($this->_showTableTitleReport($report))
						);

					// Счетчик SpyLog
					if ($spylog_counter)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.spylog_counter'), "spylog_counter", $param));
					}

					//счетчик Rambler's top 100
					if ($rambler_counter)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.rambler_counter'), "rambler_counter", $param));
					}

					//счетчик HotLog
					if ($hotlog_counter)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.hotlog_counter'), "hotlog_counter", $param));
					}

					//счетчик LiveInternet
					if ($liveinternet_counter)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.liveinternet_counter'), "liveinternet_counter", $param));
					}

					//счетчик Mail.ru
					if ($mail_counter)
					{
						$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.mail_counter'), "mail_counter", $param));
					}

					//$oCore_Html_Entity_Table->execute();
					?>
					<div class="widget">
						<div class="widget-header bordered-bottom bordered-themeprimary">
							<i class="widget-icon fa fa-bar-chart-o themeprimary"></i>
							<span class="widget-caption themeprimary"><?php echo Core::_('Seo.report_counter') ?></span>
							<div class="widget-buttons">
								<a href="#" data-toggle="maximize">
									<i class="fa fa-expand pink"></i>
								</a>
								<a href="#" data-toggle="collapse">
									<i class="fa fa-minus blue"></i>
								</a>
								<a href="#" data-toggle="dispose">
									<i class="fa fa-times darkorange"></i>
								</a>
							</div>
						</div>
						<div class="widget-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="table-scrollable">
									<?php
									$oCore_Html_Entity_Table->execute();
									?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				}

				$param['status'] = FALSE;

				$aSeo_Queries = $oSite->Seo_Queries->findAll();

				$yandex_position = intval(Core_Array::get($this->_formValues, 'yandex_position', 0));
				$google_position = intval(Core_Array::get($this->_formValues, 'google_position', 0));
				$yahoo_position = intval(Core_Array::get($this->_formValues, 'yahoo_position', 0));
				$bing_position = intval(Core_Array::get($this->_formValues, 'bing_position', 0));

				//Если в отчете должны быть данные о поисковых запросах
				if (count($aSeo_Queries) && ($yandex_position ||$google_position || $yahoo_position || $bing_position ))
				{
					Core::factory('Core_Html_Entity_H1')
						->value(Core::_('Seo.report_position'))
						->execute();

					foreach ($aSeo_Queries as $oSeo_Query)
					{
						$aSeo_Query_Positions = Core_Entity::factory('Seo_Query', $oSeo_Query->id)
							->Seo_Query_Positions
							->getByDatetime($start_datetime, $end_datetime);

						if (count($aSeo_Query_Positions))
						{
							$aQueryPositionTitlesPerDay = array();
							$aGoogleValuesPerDay = array();
							$aYandexValuesPerDay = array();
							$aBingValuesPerDay = array();
							$aYahooValuesPerDay = array();
							$aRamblerValuesPerDay = array();

							foreach ($aSeo_Query_Positions as $oSeo_Query_Position)
							{
								$aQueryPositionTitlesPerDay[] = strtotime($oSeo_Query_Position->datetime) * 1000;

								$google_position && $aGoogleValuesPerDay[] = -$oSeo_Query_Position->google;
								$yandex_position && $aYandexValuesPerDay[] = -$oSeo_Query_Position->yandex;
								$bing_position && $aBingValuesPerDay[] = -$oSeo_Query_Position->bing;
								$yahoo_position && $aYahooValuesPerDay[] = -$oSeo_Query_Position->yahoo;
							}
							?>
							<div class="widget">
								<div class="widget-header bordered-bottom bordered-themeprimary">
									<i class="widget-icon fa fa-bar-chart-o themeprimary"></i>
									<span class="widget-caption themeprimary"><?php echo Core::_('Seo.report_position_text', $oSeo_Query->query) ?></span>
									<div class="widget-buttons">
										<a href="#" data-toggle="maximize">
											<i class="fa fa-expand pink"></i>
										</a>
										<a href="#" data-toggle="collapse">
											<i class="fa fa-minus blue"></i>
										</a>
										<a href="#" data-toggle="dispose">
											<i class="fa fa-times darkorange"></i>
										</a>
									</div>
								</div>
								<div class="widget-body">
									<div id="queryPosition<?php echo $oSeo_Query->id;?>">
										<div class="row">
											<div class="col-sm-12">
												<div id="queryPosition<?php echo $oSeo_Query->id;?>-chart" class="chart chart-lg"></div>
											</div>
										</div>
										<div class="row">
											<div class="form-group col-sm-12">
												<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Seo.reset')?></button>
											</div>
										</div>
										<div class="row">
											<div class="col-lg-12">
												<div class="table-scrollable">
												<?php
													$param['inverse'] = true;
													$param['type'] = "position";

													$oCore_Html_Entity_Table = clone $oCore_Html_Entity_Table_For_Clone;

													$aSeo_Query_Positions = $oSeo_Query
														->Seo_Query_Positions
														->getByDatetime($start_datetime, $end_datetime);

													// Не показываем некоторые столбцы, если общее количество таковых больше максимально разрешенного
													$report = $this->_buildMassReport($aSeo_Query_Positions, "position", $count);

													// Дата
													$oCore_Html_Entity_Table
														->add(
															Core::factory('Core_Html_Entity_Thead')
																->add($this->_showTableTitleReport($report))
														);

													if ($yandex_position)
													{
														$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.yandex'), "yandex", $param));
													}

													if ($google_position)
													{
														$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.google'), "google", $param));
													}

													if ($yahoo_position)
													{
														$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.yahoo'), "yahoo", $param));
													}

													if ($bing_position)
													{
														$oCore_Html_Entity_Table->add($this->_showTableRow($report, Core::_('Seo.bing'), "bing", $param));
													}

													$oCore_Html_Entity_Table->execute();
												?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<script type="text/javascript">
								$(function(){
									var queryPositionTitlesPerDay = [<?php echo implode(',', $aQueryPositionTitlesPerDay)?>],
										themeprimary = getThemeColorFromCss('themeprimary'),
										dataQueryPositionPerDay = [];

									<?php
									if ($google_position || $yandex_position || $bing_position || $yahoo_position)
									{
									?>
										for(var i = 0; i < queryPositionTitlesPerDay.length; i++)
										{
											queryPositionTitlesPerDay[i] = new Date(queryPositionTitlesPerDay[i]);
										}
									<?php
									}

									if ($google_position)
									{
									?>
										googleValuesPerDay = [<?php echo implode(',', $aGoogleValuesPerDay)?>],
										googleValueTitlesPerDay = [];

										for(var i = 0; i < queryPositionTitlesPerDay.length; i++) {
											googleValueTitlesPerDay.push([queryPositionTitlesPerDay[i], googleValuesPerDay[i]]);
										}

										dataQueryPositionPerDay.push({
											color: themeprimary,
											label: "<?php echo Core::_('Seo.google') ?>",
											data: googleValueTitlesPerDay
										})
									<?php
									}

									if ($yandex_position)
									{
									?>
										yandexValuesPerDay = [<?php echo implode(',', $aYandexValuesPerDay)?>],
										yandexValueTitlesPerDay = [];

										for(var i = 0; i < queryPositionTitlesPerDay.length; i++) {
											yandexValueTitlesPerDay.push([queryPositionTitlesPerDay[i], yandexValuesPerDay[i]]);
										}

										dataQueryPositionPerDay.push({
											color: themesecondary,
											label: "<?php echo Core::_('Seo.yandex') ?>",
											data: yandexValueTitlesPerDay
										})
									<?php
									}

									if ($bing_position)
									{
									?>
										bingValuesPerDay = [<?php echo implode(',', $aBingValuesPerDay)?>],
										bingValueTitlesPerDay = [];

										for(var i = 0; i < queryPositionTitlesPerDay.length; i++) {
											bingValueTitlesPerDay.push([queryPositionTitlesPerDay[i], bingValuesPerDay[i]]);
										}

										dataQueryPositionPerDay.push({
											color: themethirdcolor,
											label: "<?php echo Core::_('Seo.bing') ?>",
											data: bingValueTitlesPerDay
										})
									<?php
									}

									if ($yahoo_position)
									{
									?>
										yahooValuesPerDay = [<?php echo implode(',', $aYahooValuesPerDay)?>],
										yahooValueTitlesPerDay = [];

										for(var i = 0; i < queryPositionTitlesPerDay.length; i++) {
											yahooValueTitlesPerDay.push([queryPositionTitlesPerDay[i], yahooValuesPerDay[i]]);
										}

										dataQueryPositionPerDay.push({
											color: themefourthcolor,
											label: "<?php echo Core::_('Seo.yahoo') ?>",
											data: yahooValueTitlesPerDay
										})
									<?php
									}
									?>

									var placeholderQueryPositionPerDay = $("#queryPosition<?php echo $oSeo_Query->id;?>-chart"),
										optionsForQueryPositionGraph = $.extend(true, {}, optionsForGraph, {
											yaxis: {
												min: null,
												tickFormatter: function(val, axis){ return -val}
											}
										});

									placeholderQueryPositionPerDay.bind("plotselected", function (event, ranges) {
											plotQueryPositionPerDay = $.plot(placeholderQueryPositionPerDay, dataQueryPositionPerDay, $.extend(true, {}, optionsForQueryPositionGraph, {
												xaxis: {
													min: ranges.xaxis.from,
													max: ranges.xaxis.to
												}
											}));
									});

									$('<?php echo '#' . $windowId ?> #queryPosition<?php echo $oSeo_Query->id;?> #setOriginalZoom').on('click', function(){
										plotQueryPositionPerDay = $.plot(placeholderQueryPositionPerDay, dataQueryPositionPerDay, optionsForQueryPositionGraph);
									});

									var plotQueryPositionPerDay = $.plot(placeholderQueryPositionPerDay, dataQueryPositionPerDay, optionsForQueryPositionGraph);
								})
							</script>

							<?php
						}
					}
				}

				$this->addEntity(
					Admin_Form_Entity::factory('Code')
						->html(ob_get_clean())
				);

				// Контроллер показа формы редактирования с учетом скина
				$oAdmin_Form_Action_Controller_Type_Edit_Show = Admin_Form_Action_Controller_Type_Edit_Show::create();

				$oAdmin_Form_Action_Controller_Type_Edit_Show
					->title($this->title)
					->children($this->_children)
					->Admin_Form_Controller($this->_Admin_Form_Controller)
					->formId($this->_formId)
					->buttons(NULL);

				$this->addContent(
					$oAdmin_Form_Action_Controller_Type_Edit_Show->showEditForm()
				);

				return TRUE;

			default:
				return FALSE; // Показываем форму
		}
	}

	/**
	* Графическое отображение статуса наличия счетчиков и страницы в каталогах в отчете
	*
	* @param bool $value Наличие сайта в каталоге, либо счетчика на странице - true, false - иначе
	* <code>
	* <?php
	* $Seo = new Seo();
	*
	* $value = true;
	*
	* $Seo->DrawStatusReport($value);
	* ?>
	* </code>
	*/
	protected function _drawStatusReport($value)
	{
		ob_start();

		if ($value)
		{
			echo '<i class="fa fa-check success"></i>';
		}

		return ob_get_clean();
	}

	/**
	* Отображение шапки таблицы в отчете
	*
	* @param array $report Массив данных
	* - $report[]['seo_characteristic_date_time'] str Дата
	* - $report[]['seo_position_search_query_date_time'] str Дата
	* @param array $param Массив дополнительных параметров
	*/
	protected function _showTableTitleReport($report, $param = array())
	{
		$count = count($report);

		if (isset($param['count']))
		{
			$count = intval($param['count']);
		}
		else
		{
			$count = count($report);
		}

		$oCore_Html_Entity_Tr = Core::factory('Core_Html_Entity_Tr')
			->add(Core::factory('Core_Html_Entity_Th')->width('150px'));

		for($i = 0; $i < $count; $i++)
		{
			if (isset($report[$i]))
			{
				//Дата
				$oCore_Html_Entity_Tr->add(Core::factory('Core_Html_Entity_Th')
					->value(Core_Date::sql2date($report[$i]['datetime']))
				);
			}
		}

		return $oCore_Html_Entity_Tr;
	}

	/**
	 * Отображение строк таблицы в Отчете
	 *
	 * @param array $report Массив данных
	 * - $report[]['seo_characteristic_yc'] int тИЦ
	 * - $report[]['seo_characteristic_pr'] int PR
	 * - $report[]['seo_characteristic_links_google'] int Ссылающиеся страницы по данным Google
	 * - $report[]['seo_characteristic_links_yandex'] int Ссылающиеся страницы по данным Yandex
	 * - $report[]['seo_characteristic_links_yahoo'] int Ссылающиеся страницы по данным Yahoo
	 * - $report[]['seo_characteristic_links_msn'] int Ссылающиеся страницы по данным Bing.com
	 * - $report[]['seo_characteristic_indexed_yandex'] int Индексированные страницы сервисом Yandex
	 * - $report[]['seo_characteristic_indexed_yahoo'] int Индексированные страницы сервисом Yahoo
	 * - $report[]['seo_characteristic_indexed_msn'] int Индексированные страницы сервисом Bing.com
	 * - $report[]['seo_characteristic_indexed_rambler'] int Индексированные страницы сервисом Rambler
	 * - $report[]['seo_characteristic_indexed_google'] int Индексированные страницы сервисом Google
	 * - $report[]['seo_characteristic_catalog_yandex'] bool Наличие страницы в каталоге Yandex
	 * - $report[]['seo_characteristic_catalog_rambler'] bool Наличие страницы в каталоге Rambler
	 * - $report[]['seo_characteristic_catalog_mail'] bool Наличие страницы в каталоге Mail
	 * - $report[]['seo_characteristic_catalog_dmoz'] bool Наличие страницы в каталоге Dmoz
	 * - $report[]['seo_characteristic_counter_rambler'] bool Наличие счетчика Rambler
	 * - $report[]['seo_characteristic_counter_spylog'] bool Наличие счетчика SpyLog
	 * - $report[]['seo_characteristic_counter_hotlog'] bool Наличие счетчика HotLog
	 * - $report[]['seo_characteristic_counter_mail'] bool Наличие счетчика Mail
	 * - $report[]['seo_characteristic_counter_liveinternet'] bool Наличие счетчика LiveInternet
	 * @param str $field_name Название строки
	 * @param str $field_value Название поля БД
	 * @param array $param Массив дополнительных параметров
	 * - $param['arrow'] bool Отображение стрелочек динамики изменения значений
	 * - $param['status'] bool Графическое отображение статуса наличия счетчиков и страницы в каталогах
	 * - $param['inverse'] bool Инвертирование отображения динамики изменения значений
	 * - $param['count'] int Количество строк в массиве данных
	 */
	protected function _showTableRow($report, $field_name, $field_value, $param = array())
	{
		// Не задана инверсия значений
		if (!isset($param['inverse']))
		{
			$param['inverse'] = false;
		}

		// Количество строк в массиве данных
		if (isset($param['count']))
		{
			$count = intval($param['count']);
		}
		else
		{
			$count = count($report);
		}

		// Не отображать стрелочки динамики изменения значений
		if (!isset($param['arrow']))
		{
			$param['arrow'] = false;
		}

		// Не отображать графически статус наличия счетчиков и страницы в каталогах
		if (!isset($param['status']))
		{
			$param['status'] = false;
		}

		// Не задан тип набора значений
		if (!isset($param['type']))
		{
			$param['type'] = false;
		}

		$oCore_Html_Entity_Tr = Core::factory('Core_Html_Entity_Tr')
			//->class('row')
			->add(Core::factory('Core_Html_Entity_Td')->width('150px')
				->value($field_name));

		$prev = false;

		// Значения
		for($i = 0; $i < $count; $i++)
		{
			if ($param['status'])
			{
				$oCore_Html_Entity_Tr->add(Core::factory('Core_Html_Entity_Td')
					->align('center')
					->value($this->_drawStatusReport($report[$i][$field_value])));
			}
			elseif (isset($report[$i]))
			{
				$sValue = ($param['type'] == 'position' && $report[$i][$field_value] == 0)
					? '&mdash;'
					: $report[$i][$field_value];

				// Рисуем стрелочки
				if ($param['arrow'])
				{
					$sValue .= $param['inverse']
						? $this->_showArrowQuery($prev, $report[$i][$field_value])
						: $this->_showArrow($prev, $report[$i][$field_value]);
				}

				$oCore_Html_Entity_Tr->add(Core::factory('Core_Html_Entity_Td')
					->align('center')
					->value($sValue));
			}

			if (isset($report[$i]))
			{
				$prev = $report[$i][$field_value];
			}
		}

		return $oCore_Html_Entity_Tr;
	}

	/**
	* Игнорирование столбцов таблицы
	*
	* @param array $report Массив данных
	* @param str $value_type Тип поля значений
	* @param int $column_count column count
	* @return array
	*/
	protected function _buildMassReport($report, $value_type, $column_count)
	{
		$field_value = array();

		// Тип поля значений
		switch ($value_type)
		{
			default:
					break;
			case 'tcy':
				$field_value[] = "tcy";
				break;
			case 'links':
				$field_value[] = "google_links";
				$field_value[] = "yandex_links";
				//$field_value[] = "yahoo_links";
				$field_value[] = "bing_links";
				break;
			case 'indexed':
				$field_value[] = "yandex_indexed";
				//$field_value[] = "rambler_indexed";
				$field_value[] = "google_indexed";
				$field_value[] = "yahoo_indexed";
				$field_value[] = "bing_indexed";
				break;
			case 'catalog':
				$field_value[] = "yandex_catalog";
				$field_value[] = "rambler_catalog";
				$field_value[] = "dmoz_catalog";
				$field_value[] = "mail_catalog";
				break;
			case 'counter':
				$field_value[] = "spylog_counter";
				$field_value[] = "rambler_counter";
				$field_value[] = "hotlog_counter";
				$field_value[] = "liveinternet_counter";
				$field_value[] = "mail_counter";
				break;
			case 'position':
				$field_value[] = "yandex";
				//$field_value[] = "rambler";
				$field_value[] = "google";
				$field_value[] = "yahoo";
				$field_value[] = "bing";
				break;
		}

		// Количество элементов массива
		$count = count($report);
		$column_count = min($count, intval($column_count));

		// Формируем массив значений
		$array = array();

		for ($i = 0; $i < $count; $i++)
		{
			for ($j = 0; $j < count($field_value); $j++)
			{
				$array[$i][$field_value[$j]] = $report[$i]->$field_value[$j];
			}
		}

		// Итоговый массив элементов
		$array_value_final = array();

		if (count($array) == 0)
		{
			return $array_value_final;
		}

		// Формируем массив столбцов, входящих в диапазон отображаемых
		$array_value = array();

		// Массив с индексами элементов массива, которые учитываются (Нужен для корректного добавления дат в массив)
		$index = array();

		// Итоговый массив индексов учитываемых элементов (Нужен для корректного добавления дат в массив)
		$index_final = array();

		// Индекс массива $array_value
		$j = 0;

		// Первое значение
		$array_value[$j] = $array[0];
		$index[$j] = 0;

		// число невошедших элементов
		$count_deleted = 0;

		// -2 - Первый элемент (уже добавлен в массив) + Последний элемент (будет добавлен позже)
		for ($i = 1; $i < count($array) - 1; $i++)
		{
			// В первую очередь отбрасываем одинаковые столбцы
			if ($count - $count_deleted > $column_count)
			{
				// Если элемент массива $report неравен предыдущему добавленному элементу массива $array_value
				if ($array[$i] !== $array_value[$j])
				{
					$array_value[++$j] = $array[$i];
					$index[$j] = $i;
				}
				else
				{
					$count_deleted++;
				}
			}
			else
			{
				// Добавляем очередной элемент в массив
				$array_value[++$j] = $array[$i];
				$index[$j] = $i;
			}
		}

		// Если число элементов все еще не меньше разрешенного
		if (count($array_value) >= $column_count && $column_count > 1)
		{
			// Определяем, каждый какой элемент будем учитывать
			$quotient = count($array_value) / ($column_count - 1); // -1 Оставляем "место" для последнего элемента

			$j = 0;
			$array_value_final[$j] = $array_value[0];
			$index_final[$j] = 0;

			// Индекс первого учитываемого элемента
			$ind = $quotient;

			// Формируем массив из учитываемых элементов
			while (floor($ind) < count($array_value))
			{
				if (count($array_value_final) < $column_count - 1) // -1 Оставляем "место" для последнего элемента
				{
					$array_value_final[++$j] = $array_value[floor($ind)];
					$index_final[$j] = $index[floor($ind)];
					$ind += $quotient;
				}
				else
				{
					break;
				}
			}
		}
		else
		{
			$array_value_final = $array_value;
			$index_final = $index;
		}

		// Последний элемент
		$array_value_final[++$j] = end($array);
		$index_final[$j] = count($array) - 1;

		// Добавляем в массив даты
		foreach ($array_value_final as $key => $val)
		{
			$array_value_final[$key]['datetime'] = $report[$index_final[$key]]->datetime;
		}

		return $array_value_final;
	}

	/**
	* Отображение стрелочек динамики изменения значений
	*
	* @param int $prev_value предыдущее значение
	* @param int $current_value текущее значение
	* <code>
	* <?php
	* $Seo = new Seo();
	*
	* $prev_value = 1;
	* $current_value = 10;
	*
	* $Seo->ShowArrow($prev_value, $current_value);
	* ?>
	* </code>
	*/
	protected function _showArrow($prev_value, $current_value)
	{
		if ($prev_value != false && $current_value != false)
		{
			ob_start();

			if ($change_value = $current_value - $prev_value)
			{
				echo ' <i class="fa fa-' . ($change_value > 0 ? 'sort-asc success' : 'sort-desc danger') . '"></i>';
			}

			return ob_get_clean();
		}
	}

	/**
	* Отображение стрелочек динамики изменения значений поисковых запросов
	*
	* @param int $prev_value предыдущее значение
	* @param int $current_value текущее значение
	* <code>
	* <?php
	* $Seo = new Seo();
	*
	* $prev_value = 1;
	* $current_value = 10;
	*
	* $Seo->ShowArrowQuery($prev_value, $current_value);
	* ?>
	* </code>
	*/
	protected function _showArrowQuery($prev_value, $current_value)
	{
		if ($prev_value !== false && $current_value !== false && $current_value != 0)
		{
			ob_start();

			if ($change_value = $prev_value - $current_value)
			{
				echo ' <i class="fa fa-' . (($change_value > 0 || -$change_value == $current_value) ? 'sort-asc success' : 'sort-desc danger') . '"></i>';
			}

			return ob_get_clean();
		}
	}
}