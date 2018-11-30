<?php
/**
* Online shop.
*
* @package HostCMS
* @version 6.x
* @author Hostmake LLC
* @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$aConfig = Core_Config::instance()->get('shop_config');

$oXsl = isset($aConfig['shop_item_card_xsl'])
	? Core_Entity::factory('Xsl')->getByName($aConfig['shop_item_card_xsl'])
	: NULL;

require_once(CMS_FOLDER . 'modules/vendor/Picqer/autoload.php');

if (!is_null(Core_Array::getPost('start')))
{
	$xsl_id = intval(Core_Array::getPost('xsl_id', 0));

	$aTmp = array();

	$aTmp['fio'] = Core_Array::getPost('fio', '');
	$aTmp['date'] = Core_Array::getPost('date', '');
	$aTmp['width'] = intval(Core_Array::getPost('width', 50));
	$aTmp['font'] = intval(Core_Array::getPost('font', 10));
	$aTmp['horizontal'] = intval(Core_Array::getPost('horizontal', 3));
	$aTmp['vertical'] = intval(Core_Array::getPost('vertical', 5));

	if ($xsl_id)
	{
		$oXsl = Core_Entity::factory('Xsl')->getById($xsl_id);

		if (!is_null($oXsl))
		{
			?><!DOCTYPE html"><?php
			Core::initConstants(Core_Entity::factory('Site', CURRENT_SITE));

			$aConfig['shop_item_card_xsl'] = strval($oXsl->name);
			Core_Config::instance()->set('shop_config', $aConfig);

			$oShop = Core_Entity::factory('Shop', intval(Core_Array::getPost('shop_id', 0)));

			$iParentGroupId = intval(Core_Array::getPost('parent_group', 0));

			if ($iParentGroupId == 0)
			{
				$oShop_Groups = $oShop->Shop_Groups;
				$oShop_Groups->queryBuilder()
					->where('shop_groups.parent_id', '=', 0);
			}
			else
			{
				$oShop_Groups = Core_Entity::factory('Shop_Group', $iParentGroupId)->Shop_Groups;
			}

			$aShopGroupsId = array_merge(array($iParentGroupId), $oShop_Groups->getGroupChildrenId());

			foreach ($aShopGroupsId as $iShopGroupId)
			{
				$oShop_Items = $oShop->Shop_Items;
				$oShop_Items->queryBuilder()
					->where('shop_items.shop_group_id', '=', $iShopGroupId)
					->where('shop_items.shortcut_id', '=', 0)
					->clearOrderBy()
					->orderBy('shop_items.sorting');

				$aShop_Items = $oShop_Items->findAll(FALSE);

				foreach ($aShop_Items as $oShop_Item)
				{
					$oShop_Item_Barcode = $oShop_Item->Shop_Item_Barcodes->getFirst();

					$generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();

					if (!is_null($oShop_Item_Barcode) /*&& $oShop_Item_Barcode->type*/)
					{
						$type = getBarcodeType($oShop_Item_Barcode, $generatorPNG);

						$barcode = !is_null($type)
							? base64_encode($generatorPNG->getBarcode($oShop_Item_Barcode->value, $type))
							: '';

						$value = $oShop_Item_Barcode->value;
					}
					else
					{
						$barcode = $value = '';
					}

					$oShop->addEntity(
						$oShop_Item->clearEntities()
							->addEntity($oShop_Item->Shop_Measure->clearEntities())
							->addEntity($oShop_Item->Shop_Currency->clearEntities())
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('barcode_image')
									->value($barcode)
							)
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('barcode')
									->value($value)
							)
					);
				}
			}

			foreach ($aTmp as $name => $value)
			{
				$oShop->addEntity(
					Core::factory('Core_Xml_Entity')
						->name($name)
						->value($value)
				);
			}

			$oShop->addEntity(
				$oShop->Shop_Company->clearEntities()
			);

			$sXml = $oShop->getXml();

			Core::setLng($oShop->Site->lng);

			$return = Xsl_Processor::instance()
				->xml($sXml)
				->xsl($oXsl)
				->formatOutput(FALSE)
				->process();

			echo $return;
		}
	}

	/*$oShop_Item_Card = new Shop_Item_Card();

	$oShop_Item_Card->fio = Core_Array::getPost('fio', '');
	$oShop_Item_Card->date = Core_Array::getPost('date', '');
	$oShop_Item_Card->width = intval(Core_Array::getPost('width', 50));
	$oShop_Item_Card->font = intval(Core_Array::getPost('font', 10));
	$oShop_Item_Card->horizontal = intval(Core_Array::getPost('horizontal', 3));
	$oShop_Item_Card->vertical = intval(Core_Array::getPost('vertical', 5));

	$oShop_Item_Card->horizontal <= 0 && $oShop_Item_Card->horizontal = 3;
	$oShop_Item_Card->vertical <= 0 && $oShop_Item_Card->vertical = 5;

	?><!DOCTYPE html>
	<html>
		<head>
			<title><?php echo Core::_('Shop_Item.item_cards_print')?></title>
			<meta http-equiv="Content-Language" content="ru">
			<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
			<link type="text/css" href="/modules/skin/bootstrap/css/font-awesome.min.css?<?php echo rand(1, 999999)?>" rel="stylesheet" />
			<?php
			$oShop_Item_Card->showCss();
			?>
		</head>
		<body>
			<?php
			$iParentGroupId = Core_Array::getPost('parent_group', 0);

			$oShop = Core_Entity::factory('Shop', Core_Array::getPost('shop_id', 0));

			if ($iParentGroupId == 0)
			{
				$oShop_Groups = $oShop->Shop_Groups;
				$oShop_Groups->queryBuilder()
					->where('parent_id', '=', 0);
			}
			else
			{
				$oShop_Groups = Core_Entity::factory('Shop_Group', $iParentGroupId)->Shop_Groups;
			}

			$aShopGroupsId = array_merge(array($iParentGroupId), $oShop_Groups->getGroupChildrenId());

			$i = 0;

			foreach ($aShopGroupsId as $iShopGroupId)
			{
				$oShop_Items = $oShop->Shop_Items;
				$oShop_Items->queryBuilder()
					->where('shop_items.shop_group_id', '=', $iShopGroupId)
					->where('shop_items.shortcut_id', '=', 0)
					->clearOrderBy()
					->orderBy('shop_items.sorting');

				$aShop_Items = $oShop_Items->findAll(FALSE);

				foreach ($aShop_Items as $oShop_Item)
				{
					$oShop_Item_Card->build($oShop_Item);

					$i++;

					// По горизонтали
					if ($i % $oShop_Item_Card->horizontal == 0)
					{
						?><div style="clear:both;"> </div><?php
					}

					// Всего на страницу
					if ($i % ($oShop_Item_Card->horizontal * $oShop_Item_Card->vertical) == 0)
					{
						?><div class="pagebreak"> </div><?php
					}
				}
			}
			?>
		</body>
	</html>
	<?php*/
}
else
{
	$oAdmin_Form_Controller = Admin_Form_Controller::create();
	$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

	// Контроллер формы
	$oAdmin_Form_Controller
		->module(Core_Module::factory($sModule))
		->setUp()
		->path('/admin/shop/item/card/index.php');

	// Получаем параметры
	$oShop = Core_Entity::factory('Shop', Core_Array::getRequest('shop_id', 0));
	$oShopDir = $oShop->Shop_Dir;
	$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getRequest('shop_group_id', 0));

	// Первая крошка на список магазинов
	$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php')
		)
	);

	// Крошки по директориям магазинов
	if ($oShopDir->id)
	{
		$oShopDirBreadcrumbs = $oShopDir;

		$aBreadcrumbs = array();

		do
		{
			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDirBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref(
					'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
			))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
					'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
			));
		} while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oBreadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
		}
	}

	// Крошка на список товаров и групп товаров магазина
	$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
			'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
			'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"
		))
	);

	// Крошки по группам товаров
	if ($oShopGroup->id)
	{
		$oShopGroupBreadcrumbs = $oShopGroup;

		$aBreadcrumbs = array();

		do
		{
			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref(
					'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"
			))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
					'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"
			));
		}while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oBreadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
		}
	}

	// Крошка на текущую форму
	$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Item.item_cards_print'))
		->href($oAdmin_Form_Controller->getAdminLoadHref(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"
		))
	);

	$oAdmin_View = Admin_View::create()
		->module(Core_Module::factory($sModule))
		->pageTitle(Core::_('Shop_Item.item_cards_print'))
		->addChild($oAdmin_Form_Entity_Breadcrumbs);

	if (!is_null($oXsl))
	{
		$xsl_id = $oXsl->id;
		$xsl_dir_id = $oXsl->xsl_dir_id;
	}
	else
	{
		$xsl_id = 0;
		$xsl_dir_id = 0;
	}

	$windowId = $oAdmin_Form_Controller->getWindowId();

	// Создаем экземпляры классов
	$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->target('_blank')
		->add(
			Admin_Form_Entity::factory('Tab')->name('main')
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Admin_Form_Entity::factory('Select')
						->name("parent_group")
						->options(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						->caption(Core::_('Shop_Item.item_cards_print_parent_group'))
						->value($oShopGroup->id)
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Admin_Form_Entity::factory('Input')
						->name('fio')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_fio'))
						->value($oShop->Shop_Company->accountant_legal_name)
					)
					->add(Admin_Form_Entity::factory('Date')
						->name('date')
						->caption(Core::_('Shop_Item.item_cards_print_date'))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->value(date('d.m.Y'))
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Admin_Form_Entity::factory('Input')
						->name('font')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_font'))
						->value(10)
					)
					->add(Admin_Form_Entity::factory('Input')
						->name('width')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_width'))
						->value(50)
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Admin_Form_Entity::factory('Input')
						->name('horizontal')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_horizontal'))
						->value(3)
					)
					->add(Admin_Form_Entity::factory('Input')
						->name('vertical')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_vertical'))
						->value(5)
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(
						Admin_Form_Entity::factory('Select')
							->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
							->id('xsl_dir_id')
							->name('xsl_dir_id')
							->options(
								array('...') + fillXslDir(0)
							)
							->value($xsl_dir_id)
							->onchange("$.ajaxRequest({path: '/admin/structure/index.php', context: 'xsl_id', callBack: [$.loadSelectOptionsCallback, function(){var xsl_id = \$('#{$windowId} #xsl_id [value=\'{$xsl_id}\']').get(0) ? {$xsl_id} : 0; \$('#{$windowId} #xsl_id').val(xsl_id)}], action: 'loadXslList', additionalParams: 'xsl_dir_id=' + this.value, windowId: '{$windowId}'}); return false")
					)
					->add(
						Admin_Form_Entity::factory('Select')
							->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
							->id('xsl_id')
							->name('xsl_id')
					)
					->add(
						Admin_Form_Entity::factory('Script')->value("$('#{$windowId} #xsl_dir_id').change();")
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Core::factory('Core_Html_Entity_Input')
						->type('hidden')
						->name('shop_id')
						->value($oShop->id)
					)
				)
	)->add(
		Admin_Form_Entity::factory('Button')
		->name('start')
		->type('submit')
		->class('applyButton btn btn-blue')
	);

	ob_start();
	$oAdmin_Form_Entity_Form->execute();
	$content = ob_get_clean();

	ob_start();
	$oAdmin_View
		->content($content)
		->show();

	Core_Skin::instance()
		->answer()
		->ajax(Core_Array::getRequest('_', FALSE))
		->content(ob_get_clean())
		->title(Core::_('Shop_Item.item_cards_print'))
		->execute();
}

/**
 * Create visual tree of the directories
 * @param int $iXslDirParentId parent directory ID
 * @param boolean $bExclude exclude group ID
 * @param int $iLevel current nesting level
 * @return array
 */
function fillXslDir($iXslDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
{
	$iXslDirParentId = intval($iXslDirParentId);
	$iLevel = intval($iLevel);

	$oXsl_Dir = Core_Entity::factory('Xsl_Dir', $iXslDirParentId);

	$aReturn = array();

	// Дочерние разделы
	$childrenDirs = $oXsl_Dir->Xsl_Dirs->findAll();

	if (count($childrenDirs))
	{
		foreach ($childrenDirs as $childrenDir)
		{
			if ($bExclude != $childrenDir->id)
			{
				$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name;
				$aReturn += fillXslDir($childrenDir->id, $bExclude, $iLevel+1);
			}
		}
	}

	return $aReturn;
}

function getBarcodeType(Shop_Item_Barcode_Model $oShop_Item_Barcode, $generatorPNG)
{
	$return = NULL;

	switch ($oShop_Item_Barcode->type)
	{
		case 1:
			$return = $generatorPNG::TYPE_EAN_8;
		break;
		case 2:
		case 3:
			$return = $generatorPNG::TYPE_EAN_13;
		break;
		case 4:
			$return = $generatorPNG::TYPE_CODE_128;
		break;
		case 5:
			$return = $generatorPNG::TYPE_CODE_39;
		break;
	}

	return $return;
}