<?php

header("Content-Type: text/html; charset=UTF-8");

define('INSTALL_FOLDER', dirname(__FILE__) . DIRECTORY_SEPARATOR);

@ini_set('display_errors', 1);
@error_reporting(E_ALL);

@set_time_limit(9000);
//ini_set('memory_limit', '64M');
ini_set('max_execution_time', '9000');

if (isset($_POST['hostcms']['action'])
	&& ($_POST['hostcms']['action'] == 'step_5'
	|| $_POST['hostcms']['action'] == 'step_6'
	|| $_POST['hostcms']['action'] == 'step_7'
	|| $_POST['hostcms']['action'] == 'step_8')
)
{
	require_once('../bootstrap.php');

	Core_Database::instance()->query('SET SESSION wait_timeout = 28800');
	Core_Database::instance()->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
}
else
{
	//date_default_timezone_set('Europe/Moscow');

	/* Решение проблемы trict Standards: Implicit cloning object of class 'kernel' because of 'zend.ze1_compatibility_mode' */
	if (version_compare(PHP_VERSION, '5.3', '<'))
	{
		ini_set('zend.ze1_compatibility_mode', 0);
	}

	define('HOSTCMS', TRUE);
	define('DEFAULT_LNG', 'ru');
	// rtrim fix IIS bug with trailing slash
	define('CMS_FOLDER', rtrim(realpath(dirname(__FILE__) . '/../'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
	require_once(CMS_FOLDER . 'modules/core/core.php');

	Core::setModulesPath();
	Core::registerCallbackFunction();
	Core::$config = Core_Config::instance();
	Core::mainConfig();

	mb_internal_encoding('UTF-8');
}

!defined('TMP_DIR') && define('TMP_DIR', 'hostcmsfiles/tmp/');

$lng = strtolower(htmlspecialchars(
	substr(Core_Array::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE'), 0, 2)
));

(isset($_REQUEST[session_name()]) || isset($_COOKIE[session_name()])) && !isset($_SESSION) && @session_start();

ob_start();

if (!is_null(Core_Array::getGet('skinName')))
{
	$skinName = Core_Array::getGet('skinName');

	$aConfig = Core_Config::instance()->get('skin_config');
	if (isset($aConfig[$skinName]))
	{
		Core::$mainConfig['skin'] = $_SESSION['skin'] = $skinName;
	}
	else
	{
		throw new Core_Exception('Skin does not allow.');
	}
}
elseif(isset($_SESSION['skin']))
{
	Core::$mainConfig['skin'] = $_SESSION['skin'];
}

$aInstallConfig = Core_Config::instance()->get('install_config');
$aLng = Core_Array::get($aInstallConfig, 'lng', array());

$oCore_Skin = Core_Skin::instance('bootstrap')
	->setMode('install');

if (isset($aLng[$lng]))
{
	// Устанавливаем полученный язык
	Core_I18n::instance()->setLng($lng);
	$oCore_Skin->setLng($lng);
}

$oAdmin_Answer = $oCore_Skin->answer();

//$openWindow = TRUE;

$formSettings = Core_Array::getPost('hostcms', array())
	+ array(
		'action' => NULL,
		'window' => 'id_content',
	);

$windowId = Core_Str::escapeJavascriptVariable($formSettings['window']);

$lng_value = Core_Array::getPost('lng_value');
if (isset($aLng[$lng_value]))
{
	!isset($_SESSION) && @session_start();
	$_SESSION['current_lng'] = $lng_value;
}

$lngName = isset($_SESSION['current_lng']) ? $_SESSION['current_lng'] : DEFAULT_LNG;
Core_I18n::instance()->setLng($lngName);

// Минимальные требования к системе
define('PHP_version', '5.2.2');
define('GD_version', '2.0');
define('PCRE_version', '7.0');
define('JSON', Core::_('install.constant_on'));
define('MbString', Core::_('install.constant_on'));
define('MySQL_Drivers', 'PDO, mysql');
define('SimpleXML', Core::_('install.constant_on'));
define('Iconv', Core::_('install.constant_on'));
define('XSLT', Core::_('install.supported'));
define('Data_limit', '2'); // Mb
define('TIME_limit', '30'); // Sec
define('DISC_space', '30'); // Mb
define('RAM_space', '32M');
define('SAFE_MODE', Core::_('install.constant_off'));
define('REGISTER_GLOBALS', Core::_('install.constant_off'));
define('MAGIC_QUOTES_GPC', Core::_('install.constant_off'));
define('DUMP_file', 'dump_41.sql');
define('LICENCE_file', 'licence.txt');
define('FILE_MODE', 644);
define('DIR_MODE', 755);
define('CHMOD_FILE', octdec(FILE_MODE));
define('CHMOD', octdec(DIR_MODE));

$Site_Controller_Template = Site_Controller_Template::instance()
	->templatePath(CMS_FOLDER . TMP_DIR)
	->chmodFile(octdec(FILE_MODE))
	->server('http://www.hostcms.ru');

// Step 0: Set language
if (!is_null(Core_Array::getPost('step_0')))
{
	$iStep = 1;
	$title = Core::_('Install.step_' . $iStep);

	?><form method="post" action="index.php" id="install">
		<div class="row">
			<div class="col-xs-12">
				<div style="height: 400px; width: 100%; overflow: auto; border: 1px solid rgba(100,100,100,.3); padding: 15px; margin: 15px 0"><?php echo nl2br(Core_File::read(LICENCE_file))?></div>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12">
				<label><input type="checkbox" name="license_agree" checked="checked" value="on" onclick="$('#applyButton').toggleDisabled()" class="colored-success"><span class="text"><?php echo Core::_('install.license_agreement')?></span></label>
			</div>
		</div>

		<div class="row">
			<!-- step_2 -->
			<div class="col-xs-12 text-align-right">
				<button id="applyButton" name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'step_2',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
					<?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i>
				</button>
			</div>
		</div>
	</form>
	<?php
}
elseif ($formSettings['action'] == 'step_2')
{
	$iStep = 2;
	$title = Core::_('Install.step_' . $iStep);

	/*if (isset($_POST['step2']) && !isset($_POST['license_agree']))
	{
		?><div id="error_message"><?php echo Core::_('install.license_agreement_error')?></div><?php
	}*/
	?><form method="post" action="index.php" id="install">
	<table width="100%" class="table table-bordered table-striped table-condensed ">
		<thead class="bordered-palegreen">
		<tr>
			<th width="350"><?php echo Core::_('install.table_field_param')?></th>
			<th width="140"><?php echo Core::_('install.table_field_need')?></th>
			<th width="140"><?php echo Core::_('install.table_field_thereis')?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$result = version_compare(phpversion(), PHP_version, '>=');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_php_version')?></td>
			<td><?php echo PHP_version?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php
			$php = phpversion();
			$php = $php ? $php : Core::_('install.undefined');
			?> <?php showbullet($result ? 0 : 2, $php)?>
			</td>
		</tr>
		<?php
		$gd = Core_Image::instance('gd')->getVersion();
		$result = version_compare($gd, GD_version, '>=');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_gd_version')?></td>
			<td><?php echo GD_version?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php
			$gd = $gd ? $gd : Core::_('install.undefined');
			?> <?php showbullet($result ? 0 : 2, $gd)?>
			</td>
		</tr>
		<?php
		$pcre = Core::getPcreVersion();
		$result = version_compare($pcre, PCRE_version, '>=');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_pcre_version')?></td>
			<td><?php echo PCRE_version?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php
			$pcre = $pcre ? $pcre : Core::_('install.undefined');
			?> <?php showbullet($result ? 0 : 2, $pcre)?>
			</td>
		</tr>
		<?php
		$result = function_exists('mb_internal_encoding');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_mbstring')?></td>
			<td><?php echo MbString?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php showbullet($result ? 0 : 2, $result ? Core::_('install.constant_on') : Core::_('install.constant_off'))?>
			</td>
		</tr>
		<?php
		$result = function_exists('json_encode');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_json')?></td>
			<td><?php echo JSON?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php showbullet($result ? 0 : 2, $result ? Core::_('install.constant_on') : Core::_('install.constant_off'))?>
			</td>
		</tr>
		<?php
		$result = function_exists('simplexml_load_string');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_simplexml')?></td>
			<td><?php echo SimpleXML?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php showbullet($result ? 0 : 2, $result ? Core::_('install.constant_on') : Core::_('install.constant_off'))?>
			</td>
		</tr>
		<?php
		$result = function_exists('iconv');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_iconv')?></td>
			<td><?php echo Iconv?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php showbullet($result ? 0 : 2, $result ? Core::_('install.constant_on') : Core::_('install.constant_off'))?>
			</td>
		</tr>
		<?php
		$result = class_exists('DomDocument') && class_exists('XsltProcessor');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_xslt_support')?></td>
			<td><?php echo XSLT?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php showbullet($result ? 0 : 2, $result ? XSLT : Core::_('install.unsupported'))?></td>
		</tr>
		<?php
		$bPDO = class_exists('PDO');
		$bMysql = function_exists('mysql_connect');
		$result = $bPDO || $bMysql;

		$aDbDrivers = array();
		$bPDO && $aDbDrivers[] = 'PDO';
		$bMysql && $aDbDrivers[] = 'mysql';
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_mysql')?></td>
			<td><?php echo MySQL_Drivers?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php showbullet($result ? 0 : 2, implode(', ', $aDbDrivers))?>
			</td>
		</tr>
		<?php
		$post_max_size = ini_get('post_max_size');
		$result = version_compare($post_max_size, Data_limit, '>=');
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_maximum_upload_data_size')?></td>
			<td><?php echo Data_limit?>М</td>
			<td class="<?php echo $result ? 'success' : 'warning'?>"><?php showbullet($result ? 0 : 1, $post_max_size ? $post_max_size : Core::_('install.undefined'))?>
			</td>
		</tr>
		<?php
		$max_execution_time = ini_get('max_execution_time');
		$result = $max_execution_time >= TIME_limit;
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_maximum_execution_time')?></td>
			<td><?php echo TIME_limit . " " . Core::_('install.seconds')?></td>
			<td class="<?php echo $result ? 'success' : 'warning'?>"><?php showbullet($result ? 0 : 1, $max_execution_time ? $max_execution_time . " " . Core::_('install.seconds') : Core::_('install.undefined'))?>
			</td>
		</tr>
		<?php
		$disk_free_space = @disk_free_space($_SERVER['DOCUMENT_ROOT']);
		$result = $disk_free_space >= DISC_space;
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_disc_space')?></td>
			<td><?php echo DISC_space . Core::_('install.megabytes')?></td>
			<td class="<?php echo $result ? 'success' : 'warning'?>"><?php showbullet($result ? 0 : 2, $disk_free_space ? round($disk_free_space / 1024 / 1024, 2) . Core::_('install.megabytes') : Core::_('install.undefined'))?>
			</td>
		</tr>
		<?php
		$memory_limit = ini_get('memory_limit');
		$result = Core_Str::convertSizeToBytes($memory_limit) >= Core_Str::convertSizeToBytes(RAM_space);
		?>
		<tr>
			<td><?php echo Core::_('install.table_field_ram_space')?></td>
			<td><?php echo RAM_space?></td>
			<td class="<?php echo $result ? 'success' : 'danger'?>"><?php showbullet($result ? 0 : 2, $memory_limit ? $memory_limit : Core::_('install.undefined'))?>
			</td>
		</tr>
		<?php
		// Safe Mode and etc. REMOVED as of PHP 5.4.0.
		if (version_compare(PHP_VERSION, '5.4', '<'))
		{
			$safe_mode = ini_get('safe_mode');
			$result = $safe_mode != 1;
			?>
			<tr>
				<td><?php echo Core::_('install.table_field_safe_mode')?></td>
				<td><?php echo SAFE_MODE?></td>
				<td class="<?php echo $result ? 'success' : 'warning'?>"><?php showbullet($result ? 0 : 1, $result ? Core::_('install.constant_off') : Core::_('install.constant_on'))?></td>
			</tr>
			<?php
			$result = ini_get('register_globals') != 1;
			?>
			<tr>
				<td><?php echo Core::_('install.table_field_register_globals')?></td>
				<td><?php echo REGISTER_GLOBALS?></td>
				<td class="<?php echo $result ? 'success' : 'warning'?>"><?php showbullet($result ? 0 : 1, $result ? Core::_('install.constant_off') : Core::_('install.constant_on'))?></td>
			</tr>
			<?php
			$result = !(ini_get('magic_quotes_gpc') == 1 || ini_get('magic_quotes_runtime') == 1 || ini_get('magic_quotes_sybase') == 1);
			?>
			<tr>
				<td><?php echo Core::_('install.table_field_magic_quotes_gpc')?></td>
				<td><?php echo MAGIC_QUOTES_GPC?></td>
				<td class="<?php echo $result ? 'success' : 'warning'?>"><?php showbullet($result ? 0 : 1, $result ? Core::_('install.constant_off') : Core::_('install.constant_on'))?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>

	<hr />
	<?php showbullet(0, Core::_('install.parameter_corresponds'))?>
	<?php showbullet(1, Core::_('install.parameter_not_corresponds_but_it_is_safe'))?>
	<?php showbullet(2, Core::_('install.parameter_not_corresponds'))?>

	<div class="row">
		<div class="col-xs-12 text-align-right">
			<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'step_3',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
				<?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i>
			</button>
		</div>
	</div>
	</form>
	<?php
}
elseif ($formSettings['action'] == 'step_3')
{
	$iStep = 3;
	$title = Core::_('Install.step_' . $iStep);

	?><form method="post" action="index.php" id="install" class="form-horizontal form-bordered" role="form">

		<h4><?php echo Core::_('install.access_parameter')?></h4>

		<div class="form-group">
			<label for="inputChmodFile" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('install.file_access')?></label>
			<div class="col-sm-3">
				<input name="file_mode" type="text" class="form-control" id="inputChmodFile" placeholder="<?php echo Core::_('install.example', FILE_MODE)?>" value="<?php echo htmlspecialchars(Core_Array::getGet('file_mode', FILE_MODE))?>">
			</div>

			<label for="inputChmodDir" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('install.directory_access')?></label>
			<div class="col-sm-3">
				<input name="dir_mode" type="text" class="form-control" id="inputChmodDir" placeholder="<?php echo Core::_('install.example', DIR_MODE)?>" value="<?php echo htmlspecialchars(Core_Array::getGet('dir_mode', DIR_MODE))?>">
			</div>
		</div>

		<h4><?php echo Core::_('install.database_params')?></h4>

		<div class="form-group">
			<label for="inputHost" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.mysql_server')?></label>
			<div class="col-sm-4">
				<input name="host" type="text" class="form-control" id="inputHost" placeholder="<?php echo Core::_('install.example', 'localhost')?>" value="<?php echo htmlspecialchars(Core_Array::getGet('host'))?>">
			</div>

			<label for="inputDriver" class="col-sm-2 control-label no-padding-right"><?php echo Core::_('install.database_driver')?></label>
			<div class="col-sm-3">
				<select name="driver" class="form-control" id="inputDriver">
					<option value="0">MySQL</option>
					<option value="1" <?php echo Core_Array::getGet('driver') == 1 || version_compare(phpversion(), '5.3', '>=') ? 'selected="selected"' : '' ?>>PDO MySQL</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="inputUser" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.database_login')?></label>
			<div class="col-sm-4">
				<input name="user" type="text" class="form-control" id="inputUser" value="<?php echo htmlspecialchars(Core_Array::getGet('user'))?>">
			</div>
		</div>

		<div class="form-group">
			<label for="inputPassword" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.database_pass')?></label>
			<div class="col-sm-4">
				<input name="password" type="password" class="form-control" id="inputPassword" value="<?php echo htmlspecialchars(Core_Array::getGet('password'))?>">
			</div>
		</div>

		<div class="form-group">
			<label for="inputDatabase" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.database_mysql')?></label>
			<div class="col-sm-4">
				<input name="database" type="text" class="form-control" id="inputDatabase" value="<?php echo htmlspecialchars(Core_Array::getGet('database'))?>">
			</div>

			<label for="storageEngine" class="col-sm-2 control-label no-padding-right"><?php echo Core::_('install.database_storage_engine')?></label>
			<div class="col-sm-3">
				<select name="storageEngine" class="form-control" id="storageEngine">
					<option value="0">MyISAM</option>
					<option value="1" <?php echo Core_Array::getGet('storageEngine') == 1 ? 'selected="selected"' : '' ?>>InnoDB</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="inputCreateDatabase" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.create_database')?></label>
			<div class="col-sm-9 checkbox">
				<label>
					<input class="colored-danger" id="inputCreateDatabase" type="checkbox" name="create_database" value="on" <?php echo Core_Array::getGet('create_database') == 'on' ? 'checked="checked"' : '' ?>>
					<span class="text"><?php echo Core::_('install.create_database_flag')?></span>
				</label>
			</div>
		</div>

		<div class="form-group">
			<label for="inputClearDatabase" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.clear_database')?></label>
			<div class="col-sm-9 checkbox">
				<label>
					<input class="colored-danger" id="inputClearDatabase" type="checkbox" name="clear_database" value="on" <?php echo Core_Array::getGet('clear_database') == 'on' ? 'checked="checked"' : '' ?>>
					<span class="text"><?php echo Core::_('install.clear_database_caution')?></span>
				</label>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 text-align-right">
				<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'step_4',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
					<?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i>
				</button>
			</div>
		</div>
	</form>
	<?php
}
elseif ($formSettings['action'] == 'step_4')
{
	$iStep = 4;
	$title = Core::_('Install.step_' . $iStep);

	// Если передано значение прав доступа к файлам
	$file_mode = octdec(Core_Array::getRequest('file_mode', FILE_MODE));
	$dir_mode = octdec(Core_Array::getRequest('dir_mode', DIR_MODE));

	// Устанавливаем права
	setChmod(CMS_FOLDER, $file_mode, $dir_mode);

	$sConfigPath = CMS_FOLDER . 'modules/core/config/database.php';

	$host = trim(Core_Array::getPost('host'));
	$user = trim(Core_Array::getPost('user'));
	$password = trim(Core_Array::getPost('password'));
	$database = trim(Core_Array::getPost('database'));

	switch (Core_Array::getPost('driver'))
	{
		case 0:
		default:
			$driver = 'mysql';
		break;
		case 1:
			$driver = 'pdo';
		break;
	}

	switch (Core_Array::getPost('storageEngine'))
	{
		case 0:
		default:
			$storageEngine = 'MyISAM';
		break;
		case 1:
			$storageEngine = 'InnoDB';
		break;
	}

	$configContent = "<?php

return array (
	'default' => array (
		'driver' => '" . $driver . "',
		'host' => '" . addslashes($host) . "',
		'username' => '" . addslashes($user) . "',
		'password' => '" . addslashes($password) . "',
		'database' => '" . addslashes($database) . "',
		'storageEngine' => '" . addslashes($storageEngine) . "'
	)
);";

	try
	{
		$flag = Core_File::write($sConfigPath, $configContent, $file_mode);
	}
	catch (Exception $e)
	{
		$flag = FALSE;
		Core_Message::show($e->getMessage(), 'error');
	}

	$bCreateDatabase = Core_Array::getPost('create_database') == 'on';

	?><form method="post" action="index.php">
	<div class="row">
		<div class="col-xs-12">
			<table border="0" class="table margin-bottom-10">
				<thead>
				<tr>
					<th width="250"><?php echo Core::_('install.action')?></th>
					<th><?php echo Core::_('install.result')?></th>
					<th><?php echo Core::_('install.comment')?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><?php echo Core::_('install.store_database_params')?></td>
					<td><?php showbullet($flag ? 0 : 2, $flag ? Core::_('install.success') : Core::_('install.error'))?></td>
					<td><?php echo $flag ? '' : Core::_('install.not_enough_rights_error', $sConfigPath)?>
					</td>
				</tr>
				<tr>
					<td><?php echo Core::_('install.database_connection')?></td>
					<td><?php
					try
					{
						$Core_DataBase = Core_DataBase::instance();

						// Set NULL database if create new database
						$bCreateDatabase && $Core_DataBase->setConfig(array(
							'host' => $host,
							'username' => $user,
							'password' => $password,
							'database' => NULL
						));

						$Core_DataBase->connect();
						$connect_db = TRUE;
					}
					catch (Exception $e)
					{
						$sMessage = strip_tags($e->getMessage());
						$connect_db = FALSE;
					}

					$flag = $flag && $connect_db;
					?> <?php showbullet($connect_db ? 0 : 2, $connect_db
						? Core::_('install.success')
						: Core::_('install.sql_error', $sMessage))?>

					<td><?php echo $connect_db ? '' : Core::_('install.database_connection_check_params')?>
					</td>
				</tr>
				<?php

				if ($connect_db && $bCreateDatabase)
				{
				?>
				<tr>
					<td><?php echo Core::_('install.database_creation')?></td>
					<td><?php
					try
					{
						$Core_DataBase->setQueryType(99)->query(
							'CREATE DATABASE IF NOT EXISTS ' . $Core_DataBase->quoteColumnName($database)
						);
						$bDatabaseCreated = TRUE;

						// Set NULL database if create new database
						$bCreateDatabase && $Core_DataBase->setConfig(array(
							'host' => $host,
							'username' => $user,
							'password' => $password,
							'database' => $database
						))
						// reconnect
						->disconnect()
						->connect();
						//->selectDb($database);
					}
					catch (Exception $e)
					{
						$sMessage = strip_tags($e->getMessage());
						$bDatabaseCreated = FALSE;
					}

					$flag = $flag && $bDatabaseCreated;
					showbullet($bDatabaseCreated ? 0 : 2, $bDatabaseCreated ? Core::_('install.success') : Core::_('install.sql_error', $sMessage));

					?><td><?php echo $bDatabaseCreated ? '' : Core::_('install.attention_message')?>
					</td>
				</tr>
				<?php
				}

				// Очистка базы
				if (Core_Array::getPost('clear_database') == 'on')
				{
					?><tr>
					<td><?php echo Core::_('install.database_clearing')?></td>
					<td><?php

					// Удаление всех таблиц текущего соединения
					try
					{
						$aTables = $Core_DataBase->getTables();
						foreach ($aTables as $sTable) {
							$Core_DataBase->setQueryType(6)->query('DROP TABLE ' . $Core_DataBase->quoteColumnName($sTable));
						}
						$e_b = TRUE;
					}
					catch (Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
						$e_b = FALSE;
					}

					showbullet($e_b ? 0 : 2, $e_b ? Core::_('install.success') : Core::_('install.error'));

					?></td>
					<td></td>
					</tr>
					<?php
				}

				if ($connect_db)
				{
					?>
					<tr>
						<td><?php echo Core::_('install.sql_dump_loading')?></td>
						<td><?php
							try
							{
								$dumpfile = Core_File::read(DUMP_file);

								$e_b = Sql_Controller::instance()->execute($dumpfile);
								$flag = $flag && $e_b;
							}
							catch (Exception $e) {
								$flag = FALSE;
								Core_Message::show($e->getMessage(), 'error');
							}
							?> <?php showbullet($e_b ? 0 : 2, $e_b
								? Core::_('install.success')
								: Core::_('install.sql_dump_loading_error', $Core_DataBase->getVersion()))?>
						</td>
						<td></td>
					</tr>
					<?php
				}

				if ($connect_db)
				{
					?><tr>
						<td><?php echo Core::_('install.lng_installing')?></td>
						<td><?php
							$e_b = FALSE;
							try
							{
								$oConstant = Core_Entity::factory('Constant')->getByName('DEFAULT_LNG');

								if ($oConstant)
								{
									$oConstant->value = $lngName;
									$oConstant->save();
									$e_b = TRUE;
								}
							}
							catch (Exception $e) {
								$sMessage = strip_tags($e->getMessage());
								$e_b = FALSE;
							}

							showbullet($e_b ? 0 : 2, $e_b
								? Core::_('install.success')
								: Core::_('install.sql_error', $sMessage)
							);
						?>
						</td>
						<td></td>
					</tr><?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>

	<?php
	if ($flag)
	{
		?><div class="row">
			<div class="col-xs-12 text-align-right">
				<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'step_5',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
					<?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i>
				</button>
			</div>
		</div><?php
	}
	else
	{
		$sAdditionalParams = "&host=" . urlencode($host) .
			"&user=" . urlencode($user) .
			"&password=" . urlencode($password) .
			"&database=" . urlencode($database) .
			"&file_mode=" . urlencode(Core_Array::getPost('file_mode')) .
			"&dir_mode=" . urlencode(Core_Array::getPost('dir_mode')) .
			"&driver=" . urlencode(Core_Array::getPost('driver'));

		Core_Array::getPost('clear_database') == 'on' && $sAdditionalParams .= "&clear_database=on";
		Core_Array::getPost('create_database') == 'on' && $sAdditionalParams .= "&create_database=on";

		?><div class="row">
			<div class="col-xs-12">
				<button name="process" class="btn btn-danger" onclick="$.adminSendForm({buttonObject: this,action: 'step_3',operation: '',additionalParams: '<?php echo$sAdditionalParams ?>',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
					<i class="fa fa-arrow-left"></i> <?php echo Core::_('Install.back')?>
				</button>
			</div>
		</div><?php
	}
	?>

	</form>
	<?php
}
elseif ($formSettings['action'] == 'step_5')
{
	$iStep = 5;
	$title = Core::_('Install.step_' . $iStep);

	?><div class="row">
		<div class="col-xs-12">
			<div class="alert alert-warning"><?php echo Core::_('Install.step_5_warning1')?></div>
			<div class="alert alert-warning"><?php echo Core::_('Install.step_5_warning2')?></div>
			<p><?php echo Core::_('Install.step_5_warning3')?></p>
		</div>
	</div>

	<div class="widget">
		<div class="widget-header bordered-bottom bordered-palegreen">
			<span class="widget-caption"><?php echo Core::_('Install.license-caption')?></span>
		</div>
		<div class="widget-body">
			<div>
				<form class="form-horizontal form-bordered" role="form" action="index.php" method="post">
					<div class="form-group">
						<label for="inputLogin" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.login')?></label>
						<div class="col-sm-9">
							<input name="login" type="text" class="form-control" id="inputLogin" placeholder="<?php echo Core::_('Install.login-placeholder')?>">
						</div>
					</div>
					<div class="form-group">
						<label for="inputLicense" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.license')?></label>
						<div class="col-sm-9">
							<input name="license" type="text" class="form-control" id="inputLicense" placeholder="<?php echo Core::_('Install.license')?>">
						</div>
					</div>
					<div class="form-group">
						<label for="inputPin" class="col-sm-3 control-label no-padding-right"><?php echo Core::_('Install.pin')?></label>
						<div class="col-sm-9">
							<input name="pin" type="text" class="form-control" id="inputPin" placeholder="<?php echo Core::_('Install.pin')?>">
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12 text-align-right">
							<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'step_6',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false"><?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
}
elseif ($formSettings['action'] == 'step_6')
{
	$iStep = 6;
	$title = Core::_('Install.step_' . $iStep);

	$login = Core_Array::getPost('login');
	$license = Core_Array::getPost('license');
	$pin = Core_Array::getPost('pin');

	if ($login != '')
	{
		$oConstantLogin = Core_Entity::factory('Constant')->getByName('HOSTCMS_USER_LOGIN');
		if (is_null($oConstantLogin))
		{
			$oConstantLogin = Core_Entity::factory('Constant');
			$oConstantLogin->name = 'HOSTCMS_USER_LOGIN';
			$oConstantLogin->active = 1;
		}
		$oConstantLogin->value = trim($login);
		$oConstantLogin->save();
	}

	if ($license != '')
	{
		$oConstantNumber = Core_Entity::factory('Constant')->getByName('HOSTCMS_CONTRACT_NUMBER');
		if (is_null($oConstantNumber))
		{
			$oConstantNumber = Core_Entity::factory('Constant');
			$oConstantNumber->name = 'HOSTCMS_CONTRACT_NUMBER';
			$oConstantNumber->active = 1;
		}
		$oConstantNumber->value = trim($license);
		$oConstantNumber->save();
	}

	if ($pin != '')
	{
		$oConstantPin = Core_Entity::factory('Constant')->getByName('HOSTCMS_PIN_CODE');
		if (is_null($oConstantPin))
		{
			$oConstantPin = Core_Entity::factory('Constant');
			$oConstantPin->name = 'HOSTCMS_PIN_CODE';
			$oConstantPin->active = 1;
		}
		$oConstantPin->value = trim($pin);
		$oConstantPin->save();
	}

	?><form method="post" action="index.php"><?php

	try
	{
		$oMarket_Controller = Market_Controller::instance();
		$oMarket_Controller
			->login($login)
			->contract($license)
			->pin($pin)
			->cms_folder(CMS_FOLDER)
			->php_version(phpversion())
			->mysql_version(Core_DataBase::instance()->getVersion())
			->update_id(HOSTCMS_UPDATE_NUMBER)
			->update_server(HOSTCMS_UPDATE_SERVER)
			->installMode(TRUE)
			->category_id(array(14, 15))
			->limit(999);

		// Вывод списка
		$oMarket_Controller->getMarket();

		//echo "Total: ", $oMarket_Controller->total;
		//echo ', count=', count($oMarket_Controller->items);

		?><div class="row"><?php

		foreach ($oMarket_Controller->items as $object)
		{
			?><div class="col-xs-12 col-sm-6 col-lg-4 market-item">
				<div class="databox databox-xlg databox-halved radius-bordered databox-shadowed databox-vertical">
					<div class="databox-top bg-white padding-10">
						<div class="row">
							<div class="col-xs-4">
								<a target="_blank" href="<?php echo $object->url?>">
									<img src="<?php echo $object->image_small?>" style="width: 80px; height: 80px" class="market-item-image bordered-3 bordered-white" />
								</a>
							</div>
							<div class="col-xs-8 text-align-left padding-10">
								<span class="databox-header carbon no-margin">
									<a target="_blank" href="<?php echo $object->url?>"><?php echo htmlspecialchars($object->name)?></a>
								</span>
								<span class="databox-text lightcarbon no-margin">
									<?php echo htmlspecialchars($object->category_name)?>
								</span>
							</div>
						</div>
					</div>
					<div class="databox-bottom bg-white no-padding">
						<div class="databox-row row-4">
							<div class="databox-cell cell-12 text-align-left">
								<div class="databox-text darkgray"><?php echo $object->description?></div>
							</div>
						</div>
						<div class="databox-row row-6">
							<div class="databox-row row-6 padding-10">
								<div class="databox-cell cell-6 no-padding">
									<div class="databox-text black market-item-price"><?php echo floatval($object->price)
										? number_format(round($object->price), 0, ',', ' ') . ' ' . (
											$object->currency == 'руб.'
												? '<i class="fa fa-rub"></i>'
												: $object->currency
										)
										: Core::_('Market.free')
									?></div>
								</div>

								<div class="databox-cell cell-6 no-padding">
									<?php
									if ($object->price == 0 || $object->paid)
									{
										?><a class="btn btn-labeled btn-default pull-right" onclick="$.adminSendForm({buttonObject: this,action: 'step_7',operation: '',additionalParams: 'template_id=<?php echo $object->id?>',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
											<i class="btn-label fa fa-check"></i>
											<?php echo Core::_('Market.install')?>
										</a><?php
									}
									else
									{
										?><a class="btn btn-labeled btn-palegreen pull-right" target="_blank" href="<?php echo $object->url?>">
											<i class="btn-label fa fa-shopping-cart"></i><?php echo Core::_('Market.buy')?>
										</a><?php
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		?></div><?php
	}
	catch (Exception $e)
	{
		Core_Message::show($e->getMessage(), 'error');
	}

	?><div class="row">
		<div class="col-xs-12 text-align-right">
			<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'step_7',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
				<?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i>
			</button>
		</div>
	</div>
	</form>
	<?php
}
elseif ($formSettings['action'] == 'step_7')
{
	$iStep = 7;
	$title = Core::_('Install.step_' . $iStep);

	?><form method="post" action="index.php"><?php

	$iTemplateId = intval(Core_Array::getRequest('template_id'));
	if ($iTemplateId)
	{
		try
		{
			$oAdmin_Form_Controller = Admin_Form_Controller::create();
			$oAdmin_Form_Controller->module(Core_Module::factory('install'));

			$oMarket_Controller = Market_Controller::instance();
			$oMarket_Controller
				->login(defined('HOSTCMS_USER_LOGIN') ? HOSTCMS_USER_LOGIN : '')
				->contract(defined('HOSTCMS_CONTRACT_NUMBER') ? HOSTCMS_CONTRACT_NUMBER : '')
				->pin(defined('HOSTCMS_PIN_CODE') ? HOSTCMS_PIN_CODE : '')
				->cms_folder(CMS_FOLDER)
				->php_version(phpversion())
				->mysql_version(Core_DataBase::instance()->getVersion())
				->update_id(defined('HOSTCMS_UPDATE_NUMBER') ? HOSTCMS_UPDATE_NUMBER : '')
				->update_server(HOSTCMS_UPDATE_SERVER)
				->controller($oAdmin_Form_Controller)
				->installMode(TRUE);

			$oMarket_Controller->getModule($iTemplateId);

			// Читаем modules.xml
			$oModuleXml = $oMarket_Controller->parseModuleXml();

			if (is_object($oModuleXml))
			{
				$aXmlFields = $oModuleXml->xpath("fields/field");

				if (count($aXmlFields))
				{
					$aFields = $oMarket_Controller->getFields($aXmlFields);

					foreach ($aFields as $aFieldsValue)
					{
						$oForm_Field = $oMarket_Controller->getFormField($aFieldsValue);
						$oForm_Field->execute();
					}
				}
				else
				{
					// Устанавливаем сразу без опций модуля
				}
			}
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}
	}

	?><input type="hidden" name="template_id" value="<?php echo $iTemplateId?>" />

	<div class="row">
		<div class="col-xs-12 text-align-right">
			<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'step_8',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
				<?php echo Core::_('Install.next')?> <i class="fa fa-arrow-right"></i>
			</button>
		</div>
	</div>

	</form>
	<?php
}
elseif ($formSettings['action'] == 'step_8')
{
	$iStep = 8;
	$title = Core::_('Install.step_' . $iStep);

	$iTemplateId = Core_Array::getPost('template_id');

	if ($iTemplateId)
	{
		try
		{
			$oMarket_Controller = Market_Controller::instance();
			$oMarket_Controller
				->login(defined('HOSTCMS_USER_LOGIN') ? HOSTCMS_USER_LOGIN : '')
				->contract(defined('HOSTCMS_CONTRACT_NUMBER') ? HOSTCMS_CONTRACT_NUMBER : '')
				->pin(defined('HOSTCMS_PIN_CODE') ? HOSTCMS_PIN_CODE : '')
				->cms_folder(CMS_FOLDER)
				->php_version(phpversion())
				->mysql_version(Core_DataBase::instance()->getVersion())
				->update_id(defined('HOSTCMS_UPDATE_NUMBER') ? HOSTCMS_UPDATE_NUMBER : '')
				->update_server(HOSTCMS_UPDATE_SERVER)
				->installMode(TRUE);

			$oModule = $oMarket_Controller->getModule($iTemplateId);

			// Читаем modules.xml
			$oMarket_Controller->parseModuleXml();

			$oMarket_Controller->applyModuleOptions();

			$oMarket_Controller->install();

			Core_Message::show(Core::_('install.template_install_success'));
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}
	}

	$domain = "*." . str_replace('www.', '', $_SERVER['HTTP_HOST']);

	$oSites = Core_Entity::factory('Site');
	$oSites->queryBuilder()
		->clearOrderBy()
		->orderBy('id', 'DESC')
		->limit(1);

	$aSites = $oSites->findAll(FALSE);

	// Есть дополнительный сайт
	if (isset($aSites[0]) && $aSites[0]->id != 1)
	{
		$site_id = $aSites[0]->id;

		$oSite = Core_Entity::factory('Site', 1);
		$oSite->active = 0;
		$oSite->sorting = 999;
		$oSite->save();

		// Переносим группу пользователей новому сайту
		$oUser_Group = Core_Entity::factory('User_Group', 1);
		$aSites[0]->add($oUser_Group);
	}
	else
	{
		$site_id = 1;
	}

	$oSite_Alias = Core_Entity::factory('Site_Alias');
	$oSite_Alias->name = $domain;
	$oSite_Alias->current = 1;

	Core_Entity::factory('Site', $site_id)
		->add($oSite_Alias);

	// Генерируем соль, устанавливаем новый пароль
	$password = 'admin';

	try
	{
		$salt = Core_Password::get(8);
		Core_File::write(CMS_FOLDER . 'modules/core/config/hash.php', "<?php

return array (
	'salt' => '{$salt}',
	'hash' => 'sha1',
);");

		// Change password
		$oUser = Core_Entity::factory('User')->getByLogin('admin', FALSE);

		if (!is_null($oUser))
		{
			$oUser->password = Core_Hash::factory('sha1')
				->salt($salt)
				->hash($password);

			$oUser->save();
		}
		else
		{
			echo 'User `admin` not found!';
		}
	}
	catch (Exception $e){}

	echo Core::_('install.attention_complete_install');
	?><form method="post" action="index.php">

	<div class="row">
		<div class="col-xs-12 text-align-right">
			<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'start',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
				<?php echo Core::_('Install.start')?> <i class="fa fa-arrow-right"></i>
			</button>
		</div>
	</div>
	</form>
	<?php
}
elseif ($formSettings['action'] == 'start')
{
	$iStep = 0;
	$title = '';

	try
	{
		Core_File::deleteDir(INSTALL_FOLDER);
		?><script type="text/javascript">
		document.location = '/';
		</script><?php
	}
	catch (Exception $e)
	{
		Core_Message::show($e->getMessage(), 'error');

		// Try again
		?><form method="post" action="index.php">
		<div class="row">
			<div class="col-xs-12">
				<button name="process" class="btn btn-info" onclick="$.adminSendForm({buttonObject: this,action: 'start',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '<?php echo $windowId?>'}); return false">
					<?php echo Core::_('Install.start')?> <i class="fa fa-arrow-right"></i>
				</button>
			</div>
		</div>
		</form>
		<?php
	}
}
else // Show step 0
{
	$iStep = 0;
	$title = Core::_('Install.step_' . $iStep);

	$oCore_Skin->changeLanguage();

	//$openWindow = FALSE;
}

$content = ob_get_clean();

ob_start();
?>
<style type="text/css">body.hostcms-bootstrap1:before { background-image: url("/modules/skin/bootstrap/img/bg.jpg"); }</style>

<!-- Navbar -->
<div class="navbar">
	<div class="navbar-inner">
		<div class="navbar-container">
			<!-- Navbar Barnd -->
			<div class="navbar-header pull-left">
				<a href="//www.hostcms.ru" class="navbar-brand" target="_blank">
					<img src="/modules/skin/bootstrap/img/logo-white.png" alt="(^) HostCMS" title="HostCMS" />
				</a>
			</div>
			<!-- /Navbar Barnd -->
		</div>
	</div>
</div>
<!-- /Navbar -->

<!-- Main Container -->
<div class="main-container container-fluid">
	<!-- Page Container -->
	<div class="page-container">

		<!-- Page Sidebar -->
		<div class="page-sidebar" id="sidebar">
			<!-- Page Sidebar Header-->
			<div class="sidebar-header-wrapper">
				<div class="searchinput"></div>
			</div>
			<!-- /Page Sidebar Header -->
			<!-- Sidebar Menu -->
			<ul class="nav sidebar-menu">
				<?php
				$aIcons = array(
					0 => 'glyphicon glyphicon-flag',
					1 => 'glyphicon glyphicon-file',
					2 => 'glyphicon glyphicon-dashboard',
					3 => 'glyphicon glyphicon-cog',
					4 => 'glyphicon glyphicon-list-alt',
					5 => 'fa fa-edit',
					6 => 'fa fa-th',
					7 => 'glyphicon glyphicon-tasks',
					8 => 'glyphicon glyphicon-ok-sign themesecondary',
				);

				for ($i = 0; $i < 9; $i++)
				{
					?><li id="menu-step_<?php echo $i?>">
						<a>
							<i class="menu-icon <?php echo Core_Array::get($aIcons, $i)?>"></i>
							<span class="menu-text">
								<?php echo htmlspecialchars(Core::_('install.menu_' . $i))?>
							</span>
						</a>
					</li>
				<?php
				}
				?>
			</ul>
			<!-- /Sidebar Menu -->
		</div>
		<!-- /Page Sidebar -->
		<!-- Page Content -->
		<div class="page-content">
			<!-- Page Breadcrumb -->
			<div class="page-breadcrumbs">
				<ul class="breadcrumb">
					<!-- <li>
						<i class="fa fa-home"></i>
						<a href="#">Home</a>
					</li> -->
					<li class="active">
						<i class="fa fa-home"></i>
						<?php echo Core::_('Install.menu_' . $iStep)?>
					</li>
				</ul>
			</div>
			<!-- /Page Breadcrumb -->
			<!-- Page Header -->
			<div class="page-header position-relative">
				<div class="header-title">
					<h1>
						<?php echo Core::_('Install.title')?>
					</h1>
				</div>
				<!--Header Buttons-->
				<div class="header-buttons">
					<a class="fullscreen" href="mailto:support@hostcms.ru" target="_blank">
						<i class="fa fa-life-ring"></i>
					</a>
				</div>
			</div>
			<!-- /Page Header -->
			<!-- Page Body -->
			<div class="page-body">
				<h5 class="row-title before-palegreen"><?php echo htmlspecialchars($title)?></h5>
				<div class="widget">
					<div class="widget-body">
						<?php echo $content ?>
					</div>
				</div>
			</div>
			<!-- /Page Body -->
		</div>
		<!-- /Page Content -->
	</div>
	<!-- /Page Container -->
</div>

<script>
$.currentMenu('step_<?php echo $iStep?>');
</script>

<style>
</style>
<?php

$oAdmin_Answer
	->ajax(Core_Array::getRequest('_', FALSE))
	//->openWindow($openWindow)
	->windowSettings(array(
		'Minimize' => FALSE,
		'Maximize' => FALSE,
		'Close' => FALSE
	))
	->addTaskbar(FALSE)
	->content(ob_get_clean())
	->module('step_' . $iStep)
	->title($title)
	->execute();

/**
 * Устанавливает рекурсивно права на директории и файлы
 *
 * @param string $path
 * @param string $filemode
 * @param string $dirmode
 */
function setChmod($path, $filemode, $dirmode)
{
	// Файл
	if (!is_dir($path))
	{
		return @chmod($path, $filemode);
	}

	if (!is_link($path))
	{
		$dh = @opendir($path);

		if ($dh)
		{
			while ($file = readdir($dh))
			{
				if ($file != '.' && $file != '..') {
					$fullpath = $path. '/' . $file;
					if (!is_dir($fullpath))
					{
						@chmod($fullpath, $filemode);
					}
					else
					{
						@chmod($fullpath, $dirmode);
						setChmod($fullpath, $filemode, $dirmode);
					}
				}
			}

			closedir($dh);
		}
	}
	return @chmod($path, $dirmode);
}

function showbullet($level, $text)
{
	$level = intval($level);

	switch ($level)
	{
		case 2:
			$img = 'bullet_red.gif';
		break;
		case 1:
			$img = 'bullet_orange.gif';
		break;
		case 0:
			$img = 'bullet_green.gif';
		break;
		default:
			$img = 'bullet_black.gif';
		break;
	}
	?><div style="background: url('images/<?php echo $img?>') no-repeat 0px 50%; padding-left: 15px"><?php echo $text?></div><?php
}
