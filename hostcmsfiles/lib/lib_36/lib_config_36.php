<?php
@ini_set('display_errors', 1);
error_reporting(E_ALL);
@set_time_limit(90000);

// Временная директория
$currentMonth = date('n');
$sTemporaryDirectory = TMP_DIR . '1c_exchange_files/';
$sMonthTemporaryDirectory = $sTemporaryDirectory . 'month-' . $currentMonth . '/';
$sCmsFolderTemporaryDirectory = CMS_FOLDER . $sMonthTemporaryDirectory;

// Магазин для выгрузки
$oShop = Core_Entity::factory('Shop')->find(Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

// Размер блока выгружаемых данных (1000000 = 1 мБ)
$iFileLimit = 1000000;

// Логировать обмен
$bDebug = TRUE;

// bugfix
usleep(10);

$BOM = "\xEF\xBB\xBF";

// Решение проблемы авторизации при PHP в режиме CGI
if (isset($_REQUEST['authorization'])
|| (isset($_SERVER['argv'][0])
		&& empty($_SERVER['PHP_AUTH_USER'])
		&& empty($_SERVER['PHP_AUTH_PW'])))
{
	$authorization_base64 = isset($_REQUEST['authorization'])
		? $_REQUEST['authorization']
		: mb_substr($_SERVER['argv'][0], 14);

	$authorization = base64_decode(mb_substr($authorization_base64, 6));
	$authorization_explode = explode(':', $authorization);

	if (count($authorization_explode) == 2)
	{
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = $authorization_explode;
	}

	unset($authorization);
}

if (!isset($_SERVER['PHP_AUTH_USER']))
{
	header('WWW-Authenticate: Basic realm="HostCMS"');
	header('HTTP/1.0 401 Unauthorized');
	exit;
}
elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
{
	$answr = Core_Auth::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

	Core_Auth::setCurrentSite();

	if (!Core_Auth::logged())
	{
		Core_Log::instance()->clear()
			->status(Core_Log::$ERROR)
			//->notify(FALSE)
			->write(Core::_('Core.error_log_authorization_error'));
	}

	$oUser = Core_Entity::factory('User')->getByLogin(
		$_SERVER['PHP_AUTH_USER']
	);

	if ($answr !== TRUE || !is_null($oUser) && $oUser->read_only)
	{
		$bDebug && Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('1С, ошибка авторизации');

		// авторизация не пройдена
		exit('Authentication failed!');
	}
}
else
{
	exit();
}

$sType = Core_Array::getGet('type');
$sMode = Core_Array::getGet('mode');

if (($sType == 'catalog' || $sType == 'sale') && $sMode == 'checkauth')
{
	clearstatcache();

	if ($sType == 'catalog')
	{
		// Удаление директорий обмена за предыдущие месяцы
		for ($i = 1; $i <= 12; $i++)
		{
			if ($currentMonth != $i)
			{
				$sTmpDir = CMS_FOLDER . $sTemporaryDirectory . 'month-' . $i;

				$bDebug && Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write('1С, удаление файлов предыдущего месяца ' . $i);

				// Удаляем файлы предыдущего месяца
				if (is_dir($sTmpDir)
					&& Core_File::deleteDir($sTmpDir) === FALSE)
				{
					echo "{$BOM}failure\nCan't delete temporary folder {$sTmpDir}";
					die();
				}
			}
		}

		// Удаление XML файлов
		if (is_dir($sCmsFolderTemporaryDirectory))
		{
			$bDebug && Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('1С, удаление файлов предыдущего обмена');

			try
			{
				clearstatcache();

				if ($dh = @opendir($sCmsFolderTemporaryDirectory))
				{
					while (($file = readdir($dh)) !== FALSE)
					{
						if ($file != '.' && $file != '..')
						{
							$pathName = $sCmsFolderTemporaryDirectory .  $file;

							if (Core_File::getExtension($pathName) == 'xml' && is_file($pathName))
							{
								$bDebug && Core_Log::instance()->clear()
									->status(Core_Log::$MESSAGE)
									->write('1С, удаление файла ' . $pathName);

								Core_File::delete($pathName);
							}
						}
					}

					closedir($dh);
					clearstatcache();
				}
			}
			catch(Exception $exc)
			{
				echo sprintf("{$BOM}failure\n%s", $exc/*->getMessage()*/);
			}
		}
	}

	Core_Session::start();
	echo sprintf("{$BOM}success\n%s\n%s", session_name(), session_id());
}
elseif (($sType == 'catalog' || $sType == 'sale') && $sMode == 'init')
{
	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, mode=init');

	echo sprintf("{$BOM}zip=no\nfile_limit=%s", $iFileLimit);
}
elseif ($sType == 'catalog' && $sMode == 'file' && ($sFileName = Core_Array::get($_SERVER, 'REQUEST_URI')) != '')
{
	parse_str($sFileName, $_myGet);
	$sFileName = $_myGet['filename'];

	$sFullFileName = $sCmsFolderTemporaryDirectory . $sFileName;
	Core_File::mkdir(dirname($sFullFileName), CHMOD, TRUE);

	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=catalog, mode=file, destination=' . $sFullFileName);

	if (file_put_contents($sFullFileName, file_get_contents("php://input"), FILE_APPEND) !== FALSE
		&& @chmod($sFullFileName, CHMOD_FILE))
	{
		echo "{$BOM}success";
	}
	else
	{
		echo "{$BOM}failure\nCan't save incoming data to file: {$sFullFileName}";
	}
}
elseif ($sType == 'catalog' && $sMode == 'import' && !is_null($sFileName = Core_Array::getGet('filename')))
{
	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=catalog, mode=import');

	try
	{
		$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sCmsFolderTemporaryDirectory . $sFileName);
		$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
		$oShop_Item_Import_Cml_Controller->itemDescription = 'text';
		$oShop_Item_Import_Cml_Controller->iShopGroupId = 0;
		$oShop_Item_Import_Cml_Controller->sPicturesPath = $sMonthTemporaryDirectory;
		$oShop_Item_Import_Cml_Controller->importAction = 1;
		$oShop_Item_Import_Cml_Controller->sShopDefaultPriceName = defined('SHOP_DEFAULT_CML_CURRENCY_NAME')
			? SHOP_DEFAULT_CML_CURRENCY_NAME
			: 'Розничная';
		//$oShop_Item_Import_Cml_Controller->updateFields = array('marking', 'name', 'shop_group_id', 'text', 'description', 'images', 'taxes', 'shop_producer_id');
		$oShop_Item_Import_Cml_Controller->debug = $bDebug;
		$aReturn = $oShop_Item_Import_Cml_Controller->import();
		echo "{$BOM}" . $aReturn['status'];
	}
	catch(Exception $exc)
	{
		echo sprintf("{$BOM}failure\n%s", $exc/*->getMessage()*/);
	}
}
elseif ($sType == 'sale' && $sMode == 'query')
{
	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=sale, mode=query');

	$oXml = new Core_SimpleXMLElement(sprintf(
		"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<КоммерческаяИнформация ВерсияСхемы=\"2.08\" ДатаФормирования=\"%s\"></КоммерческаяИнформация>",
		date("Y-m-d")));

	$aShopOrders = $oShop->Shop_Orders->getAllByUnloaded(0);

	foreach($aShopOrders as $oShopOrder)
	{
		$oShopOrder->addCml($oXml);
		$oShopOrder->unloaded = 1;
		$oShopOrder->save();
	}

	header('Content-type: text/xml; charset=UTF-8');
	echo $BOM, $oXml->asXML();
}
elseif ($sType == 'sale' && $sMode == 'success')
{
	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=sale, mode=success');

	/*$aShopOrders = $oShop->Shop_Orders->getAllByUnloaded(0);

	foreach($aShopOrders as $oShopOrder)
	{
		$oShopOrder->unloaded = 1;
		$oShopOrder->save();
	}*/

	echo "{$BOM}success\n";
}
elseif ($sType == 'sale' && $sMode == 'file' && ($sFileName = Core_Array::get($_SERVER, 'REQUEST_URI')) != '')
{
	parse_str($sFileName, $_myGet);
	$sFileName = $_myGet['filename'];

	$sFullFileName = $sCmsFolderTemporaryDirectory . $sFileName;
	Core_File::mkdir(dirname($sFullFileName), CHMOD, TRUE);

	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=sale, mode=file, destination=' . $sFullFileName);

	is_file($sFullFileName) && Core_File::delete($sFullFileName);

	if (file_put_contents($sFullFileName, file_get_contents("php://input"), FILE_APPEND) !== FALSE
		&& @chmod($sFullFileName, CHMOD_FILE))
	{
		$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sCmsFolderTemporaryDirectory . $sFileName);
		$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
		$oShop_Item_Import_Cml_Controller->itemDescription = 'text';
		$oShop_Item_Import_Cml_Controller->debug = $bDebug;
		$oShop_Item_Import_Cml_Controller->importOrders();
		echo "{$BOM}success";
	}
	else
	{
		echo "{$BOM}failure\nCan't save incoming data to file: {$sFullFileName}";
	}
}

die();