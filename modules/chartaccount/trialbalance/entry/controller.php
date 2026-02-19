<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Chartaccount_Trialbalance_Entry_Controller extends Chartaccount_Trialbalance_Controller
{
	/**
	 * Show content
	 * @param object $oTab
	 * @param array $aOptions
	 * @return object
	 */
	static public function showContent($oTab, array $aOptions = array())
	{
		$code = Core_Array::getGet('code', '', 'strval');
		$sc = Core_Array::getGet('sc', array(), 'array');

		$aOptionSubcounts = $aSubcountsValue = array();

		$aFilterSubcounts = Core_Array::getPost('subcounts', array(), 'array');
		foreach ($aFilterSubcounts as $aFilterSubcount)
		{
			if ($aFilterSubcount['value'])
			{
				$aOptionSubcounts[$aFilterSubcount['type']] = $aFilterSubcount['value'];
				$aSubcountsValue[$aFilterSubcount['sc']] = $aFilterSubcount['value'];
			}
		}

		// echo "<pre>";
		// var_dump($aOptionSubcounts);
		// echo "</pre>";

		$windowId = self::$_Admin_Form_Controller->getWindowId();

		$additionalParams = '';

		foreach ($sc as $scId => $scValue)
		{
			$additionalParams .= "&sc[{$scId}]={$scValue}";
		}

		self::setPath('/{admin}/chartaccount/trialbalance/entry/index.php?code=' . $code . $additionalParams);
		self::_init($aOptions);

		$oTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row report-header report-header-trialbalance'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainRow1->add(
			Admin_Form_Entity::factory('Code')
				->html(self::_getHeader())
		);

		$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode($code);

		if (!is_null($oChartaccount))
		{
			ob_start();
			?>
			<div class="col-xs-12">
				<div class="table-scrollable">
					<table class="table table-striped table-hover trialbalance-table">
						<thead>
							<tr>
								<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance_Entry.date')?></th>
								<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance_Entry.document')?></th>
								<th scope="col" colspan="3" style="width: 30%"><?php echo Core::_('Chartaccount_Trialbalance_Entry.debit')?></th>
								<th scope="col" colspan="3" style="width: 30%"><?php echo Core::_('Chartaccount_Trialbalance_Entry.credit')?></th>
								<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance_Entry.saldo')?></th>
							</tr>
							<tr>
								<th scope="col"></th>
								<th scope="col"></th>
								<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance_Entry.dt')?></th>
								<th scope="col" style="width: 5%"><?php echo Core::_('Chartaccount_Trialbalance_Entry.account')?></th>
								<th scope="col" style="width: 10%"></th>
								<th scope="col"><?php echo Core::_('Chartaccount_Trialbalance_Entry.ct')?></th>
								<th scope="col" style="width: 5%"><?php echo Core::_('Chartaccount_Trialbalance_Entry.account')?></th>
								<th scope="col" style="width: 10%"></th>
								<th scope="col"></th>
							</tr>
							<tr>
								<th scope="col" colspan="9" style="text-align: left">
									<?php
									$aSubcounts = array();

									for ($i = 0; $i < 3; $i++)
									{
										$subcountName = 'sc' . $i;

										if ($oChartaccount->$subcountName)
										{
											$aSubcounts[$oChartaccount->$subcountName] = Core_Array::get($aSubcountsValue, $subcountName, 0, 'int');
											// $aSubcounts[$oChartaccount->$subcountName] = '';
										}
									}

									$oSubcounts = Admin_Form_Entity::factory('Div')->class('chartaccount-trialbalance-entry-subcounts')->add(Admin_Form_Entity::factory('Span'));

									Chartaccount_Controller::showSubcounts($aSubcounts, $oChartaccount->id, $oSubcounts, self::$_Admin_Form_Controller, array('company_id' => self::$company_id));

									$oSubcounts->add(
										Admin_Form_Entity::factory('Span')
											->class('btn btn-sm btn-default margin-right-10')
											->add(Core_Html_Entity::factory('I')->class('fa fa-filter no-margin'))
											->onclick("$.filterChartaccountTrialbalanceEntries(this, '" . $windowId . "', '" . $code . "')")
									);

									$oSubcounts->execute();
									?>
								</th>
							</tr>
							<tr>
								<th scope="col" colspan="2" style="text-align: left"><?php echo Core::_('Chartaccount_Trialbalance_Entry.start_balance')?></th>
								<?php
								list($periodBeforeEndDate) = explode(' ', Core_Date::timestamp2sql(strtotime('-1 day', Core_Date::date2timestamp(self::$startDatetime))));

								// $aTmp = array('code' => $oChartaccount->code, 'name' => $oChartaccount->name);

								// Сальдо Д, К на начало периода
								$aOptions = array('company_id' => self::$company_id, 'dchartaccount_id' => $oChartaccount->id, 'date_to' => $periodBeforeEndDate);
								$aOptions['debit_sc'] = $sc;
								$dAmountBeforePeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);

								$aOptions = array('company_id' => self::$company_id, 'cchartaccount_id' => $oChartaccount->id, 'date_to' => $periodBeforeEndDate);
								$aOptions['credit_sc'] = $sc;
								$cAmountBeforePeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);
								?>
								<th scope="col" colspan="3" style="text-align: right">
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
								?></th>
								<th scope="col" colspan="3" style="text-align: right">
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
								?>
								</th>
								<th scope="col"></th>
							</tr>
						</thead>
						<tbody>
							<?php
								$aChartaccount_Entries = $dateArray = array();

								// Дебет
								$aOptions = array('company_id' => self::$company_id, 'dchartaccount_id' => $oChartaccount->id, 'date_from' => self::$startDatetime, 'date_to' => self::$endDatetime);
								$aOptions['debit_sc'] = $sc;

								// test
								count($aOptionSubcounts) && $aOptions['subcount'] = $aOptionSubcounts;

								$aDChartaccount_Entries = Chartaccount_Entry_Controller::getEntries($aOptions);

								foreach ($aDChartaccount_Entries as $oChartaccount_Entry)
								{
									$aChartaccount_Entries[] = $oChartaccount_Entry->dataType('debit');
									$dateArray[] = $oChartaccount_Entry->datetime;
								}
								unset($aDChartaccount_Entries);

								// Кредит
								$aOptions = array('company_id' => self::$company_id, 'cchartaccount_id' => $oChartaccount->id, 'date_from' => self::$startDatetime, 'date_to' => self::$endDatetime);
								$aOptions['credit_sc'] = $sc;

								// test
								count($aOptionSubcounts) && $aOptions['subcount'] = $aOptionSubcounts;

								$aCChartaccount_Entries = Chartaccount_Entry_Controller::getEntries($aOptions);

								foreach ($aCChartaccount_Entries as $oChartaccount_Entry)
								{
									$aChartaccount_Entries[] = $oChartaccount_Entry->dataType('credit');
									$dateArray[] = $oChartaccount_Entry->datetime;
								}
								unset($aCChartaccount_Entries);

								array_multisort($dateArray, SORT_STRING, $aChartaccount_Entries);

								$dAmountPeriod = $cAmountPeriod = 0;

								foreach ($aChartaccount_Entries as $oChartaccount_Entry)
								{
									$oObject = Chartaccount_Controller::getDocument($oChartaccount_Entry->document_id);

									$name = !is_null($oObject)
										? (method_exists($oObject, 'getDocumentFullName')
											? $oObject->getDocumentFullName(self::$_Admin_Form_Controller)
											: Core::_('Shop_Document_Relation.type' . $oObject->getEntityType()) . ' №' . htmlspecialchars($oObject->number)
										)
										: 'undefined';

									Chartaccount_Controller::setAdminFormController(self::$_Admin_Form_Controller);
									$aSubcounts = Chartaccount_Controller::getSubcounts($oChartaccount_Entry);

									// echo "<pre>";
									// var_dump($aSubcounts);
									// echo "</pre>";

									?><tr>
										<td><?php echo Core_Date::sql2date($oChartaccount_Entry->datetime)?></td>
										<td><?php
											echo $name;

											if ($oChartaccount_Entry->description != '')
											{
												?><span class="small gray margin-left-5"><?php echo htmlspecialchars($oChartaccount_Entry->description)?></span><?php
											}
										?></td>
										<td><?php
											if (isset($aSubcounts['debit']))
											{
												foreach ($aSubcounts['debit'] as $aDSubcount)
												{
													if (is_array($aDSubcount))
													{
														$color = 'style="color: ' . (isset($aDSubcount['color'])
															?  $aDSubcount['color']
															: '#0092d6') . '"';

														?><div class="small" <?php echo $color?>><?php
														if (isset($aDSubcount['onclick']) && $aDSubcount['onclick'] != '')
														{
															?><a style="color: inherit" href="<?php echo $aDSubcount['href']?>" onclick="<?php echo $aDSubcount['onclick']?>"><?php echo htmlspecialchars((string) $aDSubcount['name']);?></a><?php
														}
														else
														{
															echo htmlspecialchars((string) $aDSubcount['name']);
														}
														?></div><?php
													}
												}
											}
										?></td>
										<td class="semi-bold"><?php echo htmlspecialchars($oChartaccount_Entry->Chartaccount_Debit->code)?></td>
										<td style="text-align: right"><?php
										if ($oChartaccount_Entry->dataType == 'debit')
										{
											echo self::printAmount($oChartaccount_Entry->amount);
											$dAmountPeriod += $oChartaccount_Entry->amount;
										}
										?></td>
										<td><?php
											if (isset($aSubcounts['credit']))
											{
												foreach ($aSubcounts['credit'] as $aCSubcount)
												{
													if (is_array($aCSubcount))
													{
														$color = 'style="color: ' . (isset($aCSubcount['color'])
															?  $aCSubcount['color']
															: '#0092d6') . '"';

														?><div class="small" <?php echo $color?>><?php
														if (isset($aCSubcount['onclick']) && $aCSubcount['onclick'] != '')
														{
															?><a style="color: inherit" href="<?php echo $aCSubcount['href']?>" onclick="<?php echo $aCSubcount['onclick']?>"><?php echo htmlspecialchars((string) $aCSubcount['name']);?></a><?php
														}
														else
														{
															echo htmlspecialchars((string) $aCSubcount['name']);
														}
														?></div><?php
													}
												}
											}
										?></td>
										<td class="semi-bold"><?php echo htmlspecialchars($oChartaccount_Entry->Chartaccount_Credit->code)?></td>
										<td style="text-align: right"><?php
										if ($oChartaccount_Entry->dataType == 'credit')
										{
											echo self::printAmount($oChartaccount_Entry->amount);
											$cAmountPeriod += $oChartaccount_Entry->amount;
										}
										?></td>
										<td><?php
										$saldo = 0;
										if ($oChartaccount->type == 0)
										{
											//$beforeDebit = $dAmountBeforePeriod - $cAmountBeforePeriod;
											$letter = Core::_('Chartaccount_Trialbalance_Entry.letter_d');
											$saldo = $beforeDebit + $dAmountPeriod - $cAmountPeriod;

										}
										elseif ($oChartaccount->type == 1)
										{
											//$beforeCredit = $cAmountBeforePeriod - $dAmountBeforePeriod;
											$letter = Core::_('Chartaccount_Trialbalance_Entry.letter_c');
											$saldo = $beforeCredit + $cAmountPeriod - $dAmountPeriod;
										}
										elseif ($oChartaccount->type == 2)
										{
											$saldo = $beforeDebit - $beforeCredit + $dAmountPeriod - $cAmountPeriod;
											if ($saldo > 0)
											{
												$letter = Core::_('Chartaccount_Trialbalance_Entry.letter_d');
											}
											else
											{
												$letter = Core::_('Chartaccount_Trialbalance_Entry.letter_c');
												$saldo = -$saldo;
											}
										}

										?><span class="pull-left"><?php echo $letter;?></span>
										<span class="pull-right"><?php echo self::printAmount($saldo);?></span></td>
									</tr><?php
								}
							?>
							<tr class="total">
								<td scope="col" colspan="2" style="text-align: left"><?php echo Core::_('Chartaccount_Trialbalance_Entry.period_transactions')?></td>
								<td scope="col" colspan="3" style="text-align: right"><?php echo self::printAmount($dAmountPeriod);?></td>
								<td scope="col" colspan="3" style="text-align: right"><?php echo self::printAmount($cAmountPeriod);?></td>
								<td scope="col"></td>
							</tr>
							<tr class="total">
								<td scope="col" colspan="2" style="text-align: left"><?php echo Core::_('Chartaccount_Trialbalance_Entry.end_balance')?></td>
								<td scope="col" colspan="3" style="text-align: right">
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
								?>
								</td>
								<td scope="col" colspan="3" style="text-align: right">
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
								?>
								</td>
								<td scope="col"></td>
							</tr>
						</tbody>
					</table>
			</div>
			<?php
			$oMainRow2->add(
				Admin_Form_Entity::factory('Code')
					->html(ob_get_clean())
			);
		}

		return $oTab;
	}
}