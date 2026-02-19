<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Operation_Item_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Chartaccount_Operation_Item_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'chartaccount_operation_items.id' => 'ASC',
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'chartaccount_operation' => array(),
		'chartaccount_debit' => array('model' => 'Chartaccount', 'foreign_key' => 'dchartaccount_id'),
		'chartaccount_credit' => array('model' => 'Chartaccount', 'foreign_key' => 'cchartaccount_id')
	);

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function debitBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->dchartaccount_id)
		{
			$oChartaccount_Debit = $this->Chartaccount_Debit;

			Chartaccount_Controller::setAdminFormController($oAdmin_Form_Controller);
			$aSubcounts = Chartaccount_Controller::getSubcounts($this);

			ob_start();

			/*?><div class="semi-bold"><?php echo htmlspecialchars((string) $oChartaccount_Debit->code)?></div><?php*/
			?><div><?php echo Chartaccount_Controller::getCode($oChartaccount_Debit)?></div><?php

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

			return ob_get_clean();
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function creditBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->cchartaccount_id)
		{
			$oChartaccount_Credit = $this->Chartaccount_Credit;

			Chartaccount_Controller::setAdminFormController($oAdmin_Form_Controller);
			$aSubcounts = Chartaccount_Controller::getSubcounts($this);

			ob_start();

			/*?><div class="semi-bold"><?php echo htmlspecialchars((string) $oChartaccount_Credit->code)?></div><?php*/
			?><div><?php echo Chartaccount_Controller::getCode($oChartaccount_Credit)?></div><?php

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

			return ob_get_clean();
		}
	}
}