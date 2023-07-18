<?php
@ini_set('display_errors', 1);
error_reporting(E_ALL);

// Временная директория
$currentMonth = date('n');
$sTemporaryDirectory = TMP_DIR . '1c_exchange_files/';
$sMonthTemporaryDirectory = $sTemporaryDirectory . 'month-' . $currentMonth . '/';
$sCmsFolderTemporaryDirectory = CMS_FOLDER . $sMonthTemporaryDirectory;

// Магазин для выгрузки
$oShop = Core_Entity::factory('Shop')->find(Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

// Размер блока выгружаемых данных (10000000 = 10 мБ). Для "Мой склад" устанавливать (1000000 = 1 мБ)
$iFileLimit = 10000000;

// Логировать обмен
$bDebug = TRUE;

// Время на шаг, секунд
$iTimeout = 25;

// bugfix
usleep(10);

$BOM = "\xEF\xBB\xBF";

// Решение проблемы авторизации при PHP в режиме CGI
if (isset($_REQUEST['authorization'])
|| (isset($_SERVER['argv'][0])
	&& empty($_SERVER['PHP_AUTH_USER'])
	&& empty($_SERVER['PHP_AUTH_PW']))
)
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
	Core_Auth::setRegenerateId(FALSE);
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

$sType = Core_Array::getGet('type', '', 'str');
$sMode = Core_Array::getGet('mode', '', 'str');

if (($sType == 'catalog' || $sType == 'sale') && $sMode == 'checkauth')
{
	clearstatcache();

	if ($sType == 'catalog')
	{
		$bDebug && Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('1С, удаление директорий предыдущих месяцев');

		// Удаление директорий обмена за предыдущие месяцы
		for ($i = 1; $i <= 12; $i++)
		{
			if ($currentMonth != $i)
			{
				$sTmpDir = CMS_FOLDER . $sTemporaryDirectory . 'month-' . $i;

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
									->write('1С, удаление XML-файла предыдущего обмена ' . $pathName);

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
	Core_Session::regenerateId(TRUE);
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

	clearstatcache();

	if (is_file($sFullFileName))
	{
		$filesize = filesize($sFullFileName);
		$blocks = $filesize / $iFileLimit;
		$blocksDelta = ceil($blocks) - $blocks;

		// Накопленная разница между размером файла и размерами блока больше, чем 10% или прошло 5 минут с даты последнего изменения
		if ($blocksDelta > 0.1 || (filemtime($sFullFileName) + 60 * 5) < time())
		{
			$bDebug && Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('1С, DELETE previous file, type=catalog, mode=file, destination=' . $sFullFileName
					. ', filesize: ' . $filesize . ', fileLimit: ' . $iFileLimit . ', blocksDelta: ' . sprintf("%.3f", $blocksDelta)
					. ', time: ' . filemtime($sFullFileName) . ', current: ' . time()
				);

			Core_File::delete($sFullFileName);
		}
	}

	$content = file_get_contents("php://input");

	if (file_put_contents($sFullFileName, $content, FILE_APPEND) !== FALSE
		&& @chmod($sFullFileName, CHMOD_FILE))
	{
		echo "{$BOM}success";
	}
	else
	{
		echo "{$BOM}failure\nCan't save incoming data to file: {$sFullFileName}";
	}

	clearstatcache();

	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=catalog, mode=file, destination=' . $sFullFileName . ', contentSize=' . strlen($content) . ', fileSize=' . filesize($sFullFileName));
}
elseif ($sType == 'catalog' && $sMode == 'import' && !is_null($sFileName = Core_Array::getGet('filename')))
{
	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=catalog, mode=import, file=' . $sFileName . ', session_id=' . session_id());

	try
	{
		$sFullPath = $sCmsFolderTemporaryDirectory . Core_File::filenameCorrection($sFileName);

		$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sFullPath);
		$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
		$oShop_Item_Import_Cml_Controller->itemDescription = 'text';
		$oShop_Item_Import_Cml_Controller->iShopGroupId = 0;
		$oShop_Item_Import_Cml_Controller->sPicturesPath = $sMonthTemporaryDirectory;
		$oShop_Item_Import_Cml_Controller->importAction = 1;
		$oShop_Item_Import_Cml_Controller->sShopDefaultPriceName = defined('DEFAULT_CML_PRICE_NAME')
			? DEFAULT_CML_PRICE_NAME
			: 'Розничная';
		//$oShop_Item_Import_Cml_Controller->updateFields = array('marking', 'name', 'shop_group_id', 'text', 'description', 'images', 'taxes', 'shop_producer_id');
		//$oShop_Item_Import_Cml_Controller->skipProperties = array('Свойство1');
		$oShop_Item_Import_Cml_Controller->debug = $bDebug;
		$oShop_Item_Import_Cml_Controller->timeout = $iTimeout;
		// Событийная индексация
		//$oShop_Item_Import_Cml_Controller->searchIndexation = TRUE;
		$aReturn = $oShop_Item_Import_Cml_Controller->import();

		$bDebug && Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('1С, status: ' . $aReturn['status'] . ', timeout: ' . $oShop_Item_Import_Cml_Controller->timeout . ', type=catalog, mode=import, file=' . $sFileName . ', size=' . Core_File::filesize($sFullPath));

		if ($aReturn['status'] == 'success')
		{
			$bDebug && Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('1С, Delete file: ' . $sFileName);

			// Перед удалением делаем копию файла для отладки
			//Core_File::copy($sFullPath, $sFullPath . time());

			Core_File::delete($sFullPath);
		}

		echo $BOM . $aReturn['status'];
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

	if ($oShop->shop_company_id && method_exists($oShop->Company, 'addCml'))
	{
		$oShop->Company->addCml($oXml);
	}

	// Get Max Order Id
	$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
	$oCore_QueryBuilder_Select
		->from('shop_orders')
		->where('shop_orders.shop_id', '=', $oShop->id)
		->where('shop_orders.unloaded', '=', 0)
		->where('shop_orders.deleted', '=', 0);

	$aRow = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

	$maxId = $aRow['max_id'];

	$iFrom = 0;
	$onStep = 500;
	$iCounter = 0;

	do {
		$oShop_Orders = $oShop->Shop_Orders;
		$oShop_Orders->queryBuilder()
			->where('shop_orders.id', 'BETWEEN', array($iFrom + 1, $iFrom + $onStep));

		$aShop_Orders = $oShop_Orders->getAllByUnloaded(0);

		foreach($aShop_Orders as $oShop_Order)
		{
			$oShop_Order->addCml($oXml);
			$oShop_Order->unloaded = 1;
			$oShop_Order->save();
		}

		$iCounter += count($aShop_Orders);

		$iFrom += $onStep;
	}
	while ($iFrom < $maxId);

	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=sale, mode=query. Completed, found ' . $iCounter . ' orders');

	header('Content-type: text/xml; charset=UTF-8');
	echo $BOM;
	echo $oXml->asXML();

	/*$dom = dom_import_simplexml($oXml)->ownerDocument;
	$dom->formatOutput = TRUE;
	echo $dom->saveXML(NULL, LIBXML_NOEMPTYTAG);*/
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

	$sFullFileName = $sCmsFolderTemporaryDirectory . Core_File::filenameCorrection($sFileName);
	Core_File::mkdir(dirname($sFullFileName), CHMOD, TRUE);

	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=sale, mode=file, destination=' . $sFullFileName);

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
elseif ($sType == 'sale' && $sMode == 'import' && !is_null($sFileName = Core_Array::getGet('filename')))
{
	$bDebug && Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write('1С, type=sale, mode=import, file=' . $sFileName);

	$sFullFileName = $sCmsFolderTemporaryDirectory . Core_File::filenameCorrection($sFileName);

	try
	{
		$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sFullFileName);
		$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
		$oShop_Item_Import_Cml_Controller->debug = $bDebug;
		//$oShop_Item_Import_Cml_Controller->itemDescription = 'text';
		$oShop_Item_Import_Cml_Controller->importOrders();

		is_file($sFullFileName) && Core_File::delete($sFullFileName);

		echo "{$BOM}success";
	}
	catch(Exception $exc)
	{
		echo sprintf("{$BOM}failure\n%s", $exc/*->getMessage()*/);
	}
}

die();