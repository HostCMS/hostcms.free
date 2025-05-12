<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Chartaccount_Trialbalance_Controller
{
	static public $period = 1;

	static public $bAllYear = 0;

	static public $year = NULL;

	static public $iCurrentQuarter = 0;

	static public $month = NULL;

	static public $iStart = '';

	static public $iEnd = '';

	static public $startDatetime = '';

	static public $endDatetime = '';

	static public $company_id = 0;

	static public $oCompanyDefault = NULL;

	static protected $_path = '/{admin}/chartaccount/trialbalance/index.php';

	/**
	 * Set path
	 * @param string $path
	 * @return self
	 */
	static public function setPath($path)
	{
		self::$_path = $path;
	}

	/**
	 * Set path
	 * @param string $path
	 * @return self
	 */
	static public function getPath()
	{
		return Admin_Form_Controller::correctBackendPath(self::$_path);
	}

	static protected $_Admin_Form_Controller = NULL;

	/**
	 * Set admin form controller
	 * @param object $oAdmin_Form_Controller
	 * @return self
	 */
	static public function setAdminFormController($oAdmin_Form_Controller)
	{
		self::$_Admin_Form_Controller = $oAdmin_Form_Controller;
	}

	/**
	 * Init
	 * @param array $aOptions
	 * @return self
	 */
	static protected function _init($aOptions)
	{
		self::$period = isset($aOptions['period'])
			? $aOptions['period']
			: 1; // квартал

		// С начала года
		self::$bAllYear = isset($aOptions['all_year']) && intval($aOptions['all_year'])
			? $aOptions['all_year']
			: 0;

		self::$year = isset($aOptions['year']) && intval($aOptions['year'])
			? $aOptions['year']
			: date('Y');

		self::$iCurrentQuarter = isset($aOptions['quarter'])
			? intval($aOptions['quarter'])
			: intval(ceil(date('n', time()) / 3));

		self::$month = isset($aOptions['month']) && intval($aOptions['month'])
			? $aOptions['month']
			: date('m');

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		self::$oCompanyDefault = $oSite->Companies->getFirst(FALSE);

		self::$company_id = isset($aOptions['company_id']) && intval($aOptions['company_id'])
			? $aOptions['company_id']
			: self::$oCompanyDefault->id;

		switch (self::$period)
		{
			case 0: // месяц
				self::$iStart = strtotime(self::$year . '-' . self::$month . '-01');
				self::$iEnd = Core_Date::date2timestamp(date('Y-m-t', self::$iStart));
			break;
			case 1: // квартал
				self::$iStart = strtotime(self::$year . '-' . (self::$iCurrentQuarter * 3 - 2) . '-01');
				self::$iEnd = Core_Date::date2timestamp(date('Y-m-t', strtotime(self::$year . '-' . (self::$iCurrentQuarter * 3) . '-01')));
			break;
			case 2: // год
				self::$iStart = strtotime(self::$year . '-01-01');
				self::$iEnd = strtotime(self::$year . '-12-31');
			break;
		}

		self::$startDatetime = isset($aOptions['range_apply']) && $aOptions['range_apply'] == 1 && isset($aOptions['range_start_date'])
			? $aOptions['range_start_date']
			: date('Y-m-d', self::$iStart);

		self::$endDatetime = isset($aOptions['range_apply']) && $aOptions['range_apply'] == 1 && isset($aOptions['range_end_date'])
			? $aOptions['range_end_date']
			: date('Y-m-d', self::$iEnd);

		self::$bAllYear
			&& self::$startDatetime = date('Y-01-01', self::$iStart);
	}

	/**
	 * Get header
	 * @return string
	 */
	static protected function _getHeader()
	{
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aCompanies = $oSite->Companies->findAll();

		$windowId = self::$_Admin_Form_Controller->getWindowId();

		ob_start();
		?>
		<div class="col-xs-12 col-sm-6 col-lg-3 report-company">
			<select name="company_id" class="input-xs" onchange="$.sendRequest({context: $('.mainForm')});">
				<?php
					foreach ($aCompanies as $oCompany)
					{
						$selected = self::$company_id == $oCompany->id
							? ' selected="selected"'
							: '';

						?><option <?php echo $selected?> value="<?php echo $oCompany->id?>"><?php echo htmlspecialchars($oCompany->name)?></option><?php
					}
				?>
			</select>
		</div>
		<div class="col-xs-12 col-sm-6 col-lg-3 report-timeInterval">
			<span class="text margin-right-10"><?php echo Core::_('Chartaccount_Trialbalance.data_for')?> </span><span id="daterange" class="label label-primary"><?php echo date('d.m.Y', Core_Date::sql2timestamp(self::$startDatetime))?> — <?php echo date('d.m.Y', Core_Date::sql2timestamp(self::$endDatetime))?></span>
			<input type="hidden" name="range_apply" value="0" />
			<input type="hidden" name="range_start_date" value="<?php echo self::$startDatetime?>" />
			<input type="hidden" name="range_end_date" value="<?php echo self::$endDatetime?>" />
		</div>
		<div class="col-xs-12 col-sm-5 col-md-4 col-lg-2 report-period">
			<div class="group-by">
				<span><?php echo Core::_('Chartaccount_Trialbalance.period')?> </span>
				<div class="group-by-period">
					<?php
						for ($i = 0; $i < 3; $i++)
						{
							switch ($i)
							{
								case 0:
									$label = Core::_('Chartaccount_Trialbalance.month');
								break;
								case 1:
								default:
									$label = Core::_('Chartaccount_Trialbalance.quarter');
								break;
								case 2:
									$label = Core::_('Chartaccount_Trialbalance.year');
								break;
							}

							$labelClass = self::$period == $i
								? 'label label-primary'
								: 'text';

							?><span data-value="<?php echo $i?>" class="<?php echo $labelClass?>"><?php echo $label?></span><?php
						}
					?>
				</div>
				<input type="hidden" name="period" value="<?php echo self::$period?>" />
			</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-lg-1 report-select report-selectMonth <?php echo self::$period == 0 ? '' : 'hidden'?>">
			<select name="month" class="input-xs" onchange="$.sendRequest({context: $('.mainForm')});">
				<?php
					for ($i = 1; $i <= 12; $i++)
					{
						$selected = self::$month == $i
							? ' selected="selected"'
							: '';

						?><option <?php echo $selected?> value="<?php echo $i?>"><?php echo Core::_('Core.capitalMonth' . $i)?></option><?php
					}
				?>
			</select>
		</div>
		<div class="col-xs-12 col-sm-5 col-lg-1 report-select report-selectQuarter <?php echo self::$period == 1 ? '' : 'hidden'?>">
			<select name="quarter" class="input-xs" onchange="$.sendRequest({context: $('.mainForm')});">
				<?php
					for ($i = 1; $i <= 4; $i++)
					{
						$selected = self::$iCurrentQuarter == $i
							? ' selected="selected"'
							: '';

						?><option <?php echo $selected?> value="<?php echo $i?>"><?php echo $i?> <?php echo Core::_('Chartaccount_Trialbalance.quarter')?></option><?php
					}
				?>
			</select>
		</div>
		<div class="col-xs-12 col-sm-5 col-lg-1 report-select report-selectYear">
			<select name="year" class="input-xs" onchange="$.sendRequest({context: $('.mainForm')});">
				<?php
					for ($i = 10; $i > -1; $i--)
					{
						$iYear = date('Y') - $i;

						$selected = self::$year == $iYear
							? ' selected="selected"'
							: '';

						?><option <?php echo $selected?> value="<?php echo $iYear?>"><?php echo $iYear?></option><?php
					}
				?>
			</select>
		</div>
		<div class="col-xs-12 col-sm-6 col-lg-2 report-compareAllYear">
			<div class="all-year">
				<?php echo Core::_('Chartaccount_Trialbalance.all_year')?>
				<label>
					<input class="checkbox-slider toggle colored-success" name="all_year" onchange="$(this).val(+this.checked); $.sendRequest({path: '<?php echo self::getPath()?>', context: $('.mainForm')});" value="<?php echo self::$bAllYear?>" <?php echo self::$bAllYear ? 'checked="checked"' : ''?> type="checkbox" />
					<span class="text"></span>
				</label>
			</div>
		</div>
		<script>
			$(function() {
				$.extend({
					sendRequest: function(settings)
					{
						settings = $.extend({
							data: {}
						}, settings);

						var dataRequest = {
							ajaxLoadTabContent: 1,
							company_id: $('select[name="company_id"]').val(),
							period: $('input[name="period"]').val(),
							range_start_date: $('input[name="range_start_date"]').val(),
							range_end_date: $('input[name="range_end_date"]').val(),
							range_apply: $('input[name="range_apply"]').val(),
							month: $('select[name="month"]').val(),
							quarter: $('select[name="quarter"]').val(),
							year: $('select[name="year"]').val(),
							all_year: $('input[name="all_year"]').val(),
							subcounts: $.getChartaccountSubcouns('<?php echo $windowId?>'),
							external_data: settings.data
						};

						$.loadingScreen('show');

						$.ajax({
							// url: '<?php echo self::getPath()?>',
							url: settings.path,
							data: dataRequest,
							dataType: 'json',
							context: settings.context,
							type: 'POST',
							success: function(data) {
								this.html(data.content);

								$.loadingScreen('hide');
							}
						});
					}
				});

				var selectorGroupBy = $('.report-header .group-by-period span');

				$(selectorGroupBy).on('click', function(){
					selectorGroupBy.each(function(i) {
						$(this)
							.attr('class', 'text')
					});

					$(this).toggleClass('text label label-primary');

					$('input[name="period"]').val($(this).data('value'));
					$('.report-select').addClass('hidden');

					var selectName = 'selectMonth';

					switch ($(this).data('value'))
					{
						case 0:
							selectName = 'selectMonth';
						break;
						case 1:
							selectName = 'selectQuarter';
						break;
						case 2:
							selectName = 'selectYear';
						break;
					}

					$('.report-' + selectName).removeClass('hidden');

					$.sendRequest({context: $('.mainForm')});
				});

				$('#daterange').daterangepicker({
					locale: {
						applyLabel: '<?php echo Core::_("Report.applyLabel")?>',
						cancelLabel: '<?php echo Core::_("Report.cancelLabel")?>',
						format: 'DD/MM/YYYY'
					},
					startDate: '<?php echo date("d/m/Y", self::$iStart);?>',
					endDate: '<?php echo date("d/m/Y", self::$iEnd);?>',
				}).on('apply.daterangepicker', function (e, picker) {
					var startDate = picker.startDate.format('YYYY-MM-DD')
						endDate = picker.endDate.format('YYYY-MM-DD');

					$('input[name="range_start_date"]').val(startDate);
					$('input[name="range_end_date"]').val(endDate);
					$('input[name="range_apply"]').val(1);

					var startDateText = picker.startDate.format('DD.MM.YYYY'),
						endDateText = picker.endDate.format('DD.MM.YYYY');

					$('span#daterange').text(startDateText + ' — ' + endDateText);

					$.sendRequest({context: $('.mainForm')});
				});
			});
		</script>
		<?php

		return ob_get_clean();
	}

	/**
	 * Show content
	 * @param object $oTab
	 * @param array $aOptions
	 * @return object
	 */
	static public function showContent($oTab, array $aParams = array())
	{
		if (!Core::moduleIsActive('company'))
		{
			Core_Message::show(Core::_('Chartaccount_Trialbalance.company_off'), 'error');
			return;
		}

		self::_init($aParams);

		$oTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row report-header report-header-trialbalance'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainRow1->add(
			Admin_Form_Entity::factory('Code')
				->html(self::_getHeader())
		);

		ob_start();
		?>
		<div class="col-xs-12">
			<div class="table-scrollable">
				<table class="table table-striped table-bordered table-hover trialbalance-table">
					<thead>
						<tr>
							<th scope="col" colspan="2">
								<?php echo Core::_('Chartaccount_Trialbalance.account')?>
							</th>
							<th scope="col" colspan="2" style="width:20%">
								<?php echo Core::_('Chartaccount_Trialbalance.start_balance')?>
							</th>
							<th scope="col" colspan="2" style="width:20%">
								<?php echo Core::_('Chartaccount_Trialbalance.period_transactions')?>
							</th>
							<th scope="col" colspan="2" style="width:20%">
								<?php echo Core::_('Chartaccount_Trialbalance.end_balance')?>
							</th>
						</tr>
						<tr>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.number')?></th>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.name')?></th>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.debit')?></th>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.credit')?></th>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.debit')?></th>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.credit')?></th>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.debit')?></th>
							<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance.credit')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						list($periodBeforeEndDate) = explode(' ', Core_Date::timestamp2sql(strtotime('-1 day', Core_Date::date2timestamp(self::$startDatetime))));

						$totalAfterDebit = $totalAfterCredit = $totalPeriodDebit = $totalPeriodCredit = $totalBeforeDebit = $totalBeforeCredit = 0;

						$oCompany = !count($aParams)
							? self::$oCompanyDefault
							: Core_Entity::factory('Company')->getById(self::$company_id);

						if (!is_null($oCompany))
						{
							$aChartaccounts = Core_Entity::factory('Chartaccount')->findAll(FALSE);
							foreach ($aChartaccounts as $oChartaccount)
							{
								$aSubcounts = array();
								$aSubcounts[] = array('space' => FALSE, 'code' => $oChartaccount->code, 'name' => $oChartaccount->name);

								if ($oChartaccount->code == '50.1')
								{
									// все кассы выбираем
									$aCompany_Cashboxes = $oCompany->Company_Cashboxes->findAll(FALSE);
									foreach ($aCompany_Cashboxes as $oCompany_Cashbox)
									{
										$aSubcounts[] = array('space' => TRUE, 'code' => $oChartaccount->code, 'name' =>  $oCompany_Cashbox->name, 'sc' => array(29 => $oCompany_Cashbox->id));
									}
								}

								if ($oChartaccount->code == '51')
								{
									// все счета выбираем
									$aCompany_Accounts = $oCompany->Company_Accounts->findAll(FALSE);
									foreach ($aCompany_Accounts as $oCompany_Account)
									{
										$aSubcounts[] = array('space' => TRUE, 'code' => $oChartaccount->code, 'name' => $oCompany_Account->name, 'sc' => array(12 => $oCompany_Account->id));
									}
								}

								foreach ($aSubcounts as $aTmp)
								{
									// Сальдо Д,К на начало периода
									$aOptions = array('company_id' => self::$company_id, 'dchartaccount_id' => $oChartaccount->id, 'date_to' => $periodBeforeEndDate);
									isset($aTmp['sc']) && $aOptions['debit_sc'] = $aTmp['sc'];
									$dAmountBeforePeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);

									$aOptions = array('company_id' => self::$company_id, 'cchartaccount_id' => $oChartaccount->id, 'date_to' => $periodBeforeEndDate);
									isset($aTmp['sc']) && $aOptions['credit_sc'] = $aTmp['sc'];
									$cAmountBeforePeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);

									// Обороты за период
									$aOptions = array('company_id' => self::$company_id, 'dchartaccount_id' => $oChartaccount->id, 'date_from' => self::$startDatetime, 'date_to' => self::$endDatetime);
									isset($aTmp['sc']) && $aOptions['debit_sc'] = $aTmp['sc'];
									$dAmountPeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);

									$aOptions = array('company_id' => self::$company_id, 'cchartaccount_id' => $oChartaccount->id, 'date_from' => self::$startDatetime, 'date_to' => self::$endDatetime);
									isset($aTmp['sc']) && $aOptions['credit_sc'] = $aTmp['sc'];
									$cAmountPeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);

									if ($dAmountBeforePeriod > 0 || $cAmountBeforePeriod > 0 || $dAmountPeriod > 0 || $cAmountPeriod > 0)
									{
										$bSpace = isset($aTmp['space']) && $aTmp['space']
											? '<span class="margin-left-10"></span>'
											: '';

										$additionalParams = '';

										if (!is_null(Core_Array::getPost('ajaxLoadTabContent')))
										{
											foreach ($_POST as $key => $value)
											{
												$additionalParams .= "&{$key}={$value}";
											}
										}

										if (isset($aTmp['sc']))
										{
											foreach ($aTmp['sc'] as $scId => $scValue)
											{
												$additionalParams .= "&sc[{$scId}]={$scValue}";
											}
										}

										?><tr>
											<td>
												<span data-folder="<?php echo intval($oChartaccount->folder)?>"><a href="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/chartaccount/trialbalance/entry/')?>?code=<?php echo htmlspecialchars($aTmp['code'] . $additionalParams)?>" onclick="<?php echo self::$_Admin_Form_Controller->getAdminLoadAjax(array('path' => '/{admin}/chartaccount/trialbalance/entry/index.php', 'additionalParams' => 'code=' . htmlspecialchars($aTmp['code'] . $additionalParams)))?>"><?php echo htmlspecialchars($aTmp['code'])?></a></span>
											</td>
											<td>
												<span data-folder="<?php echo intval($oChartaccount->folder)?>"><?php echo $bSpace, htmlspecialchars($aTmp['name'])?></span>
												<?php echo $aTmp['code'] != '' ? $oChartaccount->getTypeBadge() : '';?>
											</td>
											<td class="amount">
											<?php
												$beforeDebit = 0;
												if ($oChartaccount->type == 0)
												{
													$beforeDebit = $dAmountBeforePeriod - $cAmountBeforePeriod;
												}
												elseif ($oChartaccount->type == 2)
												{
													if ($dAmountBeforePeriod == $cAmountBeforePeriod)
													{
														$beforeDebit = 0;
													}
													elseif ($dAmountBeforePeriod > $cAmountBeforePeriod)
													{
														$beforeDebit = $dAmountBeforePeriod - $cAmountBeforePeriod;
													}
												}

												echo self::printAmount($beforeDebit);

												!isset($aTmp['sc']) && $totalBeforeDebit += $beforeDebit;
											?>
											</td>
											<td class="amount">
											<?php
												$beforeCredit = 0;
												if ($oChartaccount->type == 1)
												{
													$beforeCredit = $cAmountBeforePeriod - $dAmountBeforePeriod;
												}
												elseif ($oChartaccount->type == 2)
												{
													if ($dAmountBeforePeriod == $cAmountBeforePeriod)
													{
														$beforeCredit = 0;
													}
													elseif ($cAmountBeforePeriod > $dAmountBeforePeriod)
													{
														$beforeCredit = $cAmountBeforePeriod - $dAmountBeforePeriod;
													}
												}

												echo self::printAmount($beforeCredit);

												!isset($aTmp['sc']) && $totalBeforeCredit += $beforeCredit;
											?>
											</td>
											<td class="amount">
											<?php
												echo self::printAmount($dAmountPeriod);
												!isset($aTmp['sc']) && $totalPeriodDebit += $dAmountPeriod;
											?>
											</td>
											<td class="amount">
											<?php
												echo self::printAmount($cAmountPeriod);
												!isset($aTmp['sc']) && $totalPeriodCredit += $cAmountPeriod;
											?>
											</td>
											<td class="amount">
											<?php
												$afterDebit = 0;
												if ($oChartaccount->type == 0)
												{
													$afterDebit = ($dAmountBeforePeriod - $cAmountBeforePeriod) + ($dAmountPeriod - $cAmountPeriod);
												}
												elseif ($oChartaccount->type == 2)
												{
													$dAfterPeriod = $dAmountBeforePeriod + $dAmountPeriod;
													$cAfterPeriod = $cAmountBeforePeriod + $cAmountPeriod;

													if ($dAfterPeriod == $cAfterPeriod)
													{
														$afterDebit = 0;
													}
													elseif ($dAfterPeriod > $cAfterPeriod)
													{
														$afterDebit = $dAfterPeriod - $cAfterPeriod;
													}
												}

												echo self::printAmount($afterDebit);

												!isset($aTmp['sc']) && $totalAfterDebit += $afterDebit;
											?>
											</td>
											<td class="amount">
											<?php
												$afterCredit = 0;
												if ($oChartaccount->type == 1)
												{
													$afterCredit = ($cAmountBeforePeriod - $dAmountBeforePeriod) + ($cAmountPeriod - $dAmountPeriod);
												}
												elseif ($oChartaccount->type == 2)
												{
													if ($dAfterPeriod == $cAfterPeriod)
													{
														$afterCredit = 0;
													}
													elseif ($cAfterPeriod > $dAfterPeriod)
													{
														$afterCredit = $cAfterPeriod - $dAfterPeriod;
													}
												}

												echo self::printAmount($afterCredit);

												!isset($aTmp['sc']) && $totalAfterCredit += $afterCredit;
											?>
											</td>
										</tr><?php
									}
								}
							}
						}
						?>
						<tr class="total">
							<td></td>
							<td>Итого</td>
							<td class="amount"><?php echo self::printAmount($totalBeforeCredit)?></td>
							<td class="amount"><?php echo self::printAmount($totalBeforeCredit)?></td>
							<td class="amount"><?php echo self::printAmount($totalPeriodDebit)?></td>
							<td class="amount"><?php echo self::printAmount($totalPeriodCredit)?></td>
							<td class="amount"><?php echo self::printAmount($totalAfterDebit)?></td>
							<td class="amount"><?php echo self::printAmount($totalAfterCredit)?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		$oMainRow2->add(
			Admin_Form_Entity::factory('Code')
				->html(ob_get_clean())
		);

		return $oTab;
	}

	/**
	 * Prin amount
	 * @param mixed $amount
	 * @return string
	 */
	static public function printAmount($amount)
	{
		if ($amount == 0)
		{
			return '';
		}

		$class = $amount < 0
			? 'darkorange'
			: '';

		return '<span class="' . $class . '">' . Core_Str::hideZeros(number_format(Shop_Controller::instance()->round($amount), 2, '.', ' ')) . '</span>';
	}
}