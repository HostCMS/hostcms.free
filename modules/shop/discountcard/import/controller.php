<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Import_Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Import_Controller extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title(Core::_('Shop_Discountcard.add_list'));

		$oMainTab = $this->getTab('main')->clear();
		$oAdditionalTab = $this->getTab('additional')->clear();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainRow1->add(
			Admin_Form_Entity::factory('Code')
				->html('
					<div class="col-xs-12">
						<div class="alert alert-success fade in">
							' . Core::_('Shop_Discountcard.format') . '
						</div>
					</div>
				')
		);

		$oMainRow2->add(
			Admin_Form_Entity::factory('Textarea')
				->rows(5)
				->caption(Core::_('Shop_Discountcard.add_list'))
				->name('shop_discountcard_list')
				->divAttr(array('class' => 'form-group col-xs-12'))
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Discountcard_Import_Controller.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$shop_id = intval(Core_Array::getGet('shop_id', 0));
		$oShop = Core_Entity::factory('Shop')->find($shop_id);

		if (!is_null($oShop->id))
		{
			// Массив данных о дисконтных картах, каждый с новой строки
			// Формат строки:
			// number;login;phone;shop_discountcard_level_id;amount
			$aRows = explode("\n", Core_Array::getPost('shop_discountcard_list'));

			foreach ($aRows as $sRow)
			{
				$aRow = explode(';', $sRow);

				$sNumber = trim($aRow[0]);

				$oTmp_Shop_Discountcard = $oShop->Shop_Discountcards->getByNumber($sNumber);

				// Discountcard not found
				if (is_null($oTmp_Shop_Discountcard))
				{
					$siteuser_id = 0;

					if (Core::moduleIsActive('siteuser'))
					{
						// Если есть логин
						if (isset($aRow[1]) && strlen($aRow[1]))
						{
							$sLogin = trim($aRow[1]);

							$oSiteuser = $oShop->Site->Siteusers->getByLogin($sLogin);

							if (!is_null($oSiteuser))
							{
								$siteuser_id = $oSiteuser->id;
							}
						}

						// Если логин пустой, ищем по номеру телефона
						if (!$siteuser_id && isset($aRow[2]) && strlen($aRow[2]))
						{
							$sPhone = trim($aRow[2]);

							$oDirectory_Phone = Core_Entity::factory('Directory_Phone')->getByValue($sPhone);

							if (!is_null($oDirectory_Phone))
							{
								$aSiteuser_Companies = $oDirectory_Phone->Siteuser_Companies->findAll(FALSE);

								if (count($aSiteuser_Companies))
								{
									foreach ($aSiteuser_Companies as $oSiteuser_Company)
									{
										$oSiteuser = $oSiteuser_Company->Siteuser;

										if ($oSiteuser->site_id == CURRENT_SITE)
										{
											$siteuser_id = $oSiteuser->id;

											break;
										}
									}
								}
								else
								{
									$aSiteuser_People = $oDirectory_Phone->Siteuser_People->findAll(FALSE);

									if (count($aSiteuser_People))
									{
										foreach ($aSiteuser_People as $oSiteuser_Person)
										{
											$oSiteuser = $oSiteuser_Person->Siteuser;

											if ($oSiteuser->site_id == CURRENT_SITE)
											{
												$siteuser_id = $oSiteuser->id;

												break;
											}
										}
									}
								}
							}
						}
					}


					$oShop_Discountcard = Core_Entity::factory('Shop_Discountcard');
					$oShop_Discountcard->number = $sNumber;
					$oShop_Discountcard->siteuser_id = $siteuser_id;
					$oShop_Discountcard->shop_discountcard_level_id = intval(Core_Array::get($aRow, 3, 0));
					$oShop_Discountcard->amount = Core_Array::get($aRow, 4, 0);

					$oShop->add($oShop_Discountcard);
				}
				else
				{
					// Показ ошибок
					Core_Message::show(Core::_('Shop_Discountcard.import_error', $sNumber), 'error');
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}