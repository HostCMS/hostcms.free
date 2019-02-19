<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Log system
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Log
{
	/**
	 * Backend property
	 * @var int
	 */
	static public $MESSAGE = 0;
	
	/**
	 * Backend property
	 * @var int
	 */
	static public $SUCCESS = 1;
	
	/**
	 * Backend property
	 * @var int
	 */
	static public $NOTICE = 2;
	
	/**
	 * Backend property
	 * @var int
	 */
	static public $WARNING = 3;
	
	/**
	 * Backend property
	 * @var int
	 */
	static public $ERROR = 4;

	/**
	 * The singleton instance.
	 * @var mixed
	 */
	static private $_instance = NULL;

	/**
	 * Logs directory
	 * @var string
	 */
	protected $_logDir = NULL;

	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		$this->_logDir = CMS_FOLDER . 'hostcmsfiles' . DIRECTORY_SEPARATOR . 'logs';
	}

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (!isset(self::$_instance))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Status
	 * @var int
	 */
	protected $_status = 0;

	/**
	 * Set status 
	 * @param string $status status
	 * @return self
	 */
	public function status($status)
	{
		$this->_status = intval($status);
		return $this;
	}

	/**
	 * User login
	 * @var string
	 */
	protected $_login = NULL;

	/**
	 * Set login
	 * @param string $login login
	 * @return self
	 */
	public function login($login)
	{
		$this->_login = $login;
		return $this;
	}

	/**
	 * Name of the site
	 * @var string
	 */
	protected $_site = NULL;

	/**
	 * Set site name
	 * @param string $site name
	 * @return self
	 */
	public function site($site)
	{
		$this->_site = $site;
		return $this;
	}

	/**
	 * E-mail notify mode
	 * @var boolean
	 */
	protected $_notify = TRUE;

	/**
	 * Set notify mode
	 * @param int $notify mode
	 * @return self
	 */
	public function notify($notify)
	{
		$this->_notify = $notify;
		return $this;
	}

	/**
	 * Generate name of log from timestamp
	 * @param string $date timestamp
	 * @return string
	 */
	public function getLogName($date)
	{
		return $this->_logDir . DIRECTORY_SEPARATOR . date('d_m_Y', Core_Date::sql2timestamp($date)) . '.log.csv';
	}

	/**
	 * Write message into log
	 * @param string $message message 
	 * @return self
	 */
	public function write($message)
	{
		if (is_null($this->_login))
		{
			$this->_login = isset($_SESSION['valid_user'])
				? $_SESSION['valid_user']
				: 'undefined';
		}

		if (is_null($this->_site))
		{
			$this->_site = defined('CURRENT_SITE')
				? Core_Entity::factory('Site', CURRENT_SITE)->name
				: '-';
		}

		$sHttpHost = Core_Array::get($_SERVER, 'HTTP_HOST', 'unknow');
		$page = 'http://' . $sHttpHost . Core_Array::get($_SERVER, 'REQUEST_URI');
		$user_ip = Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');

		$fname = $this->getLogName(date('Y-m-d'));

		// Delete old log files
		if (defined('LOG_DAYS_LIMIT') && !is_file($fname) && is_dir($this->_logDir))
		{
			$this->_deleteOldLogs();
		}

		$sDate = Core_Date::timestamp2sql(time());

		$aWrite = array(
			$sDate, $this->_login, $message, $this->_status, $this->_site, $page, $user_ip
		);

		if (is_file($fname) && !is_writable($fname))
		{
			@unlink($fname);
		}

		// Without Core_File, because exception will be able to call Core_Log again
		if ($f_log = @fopen($fname, 'a'))
		{
			if (flock($f_log, LOCK_EX))
			{
				fputcsv($f_log, $aWrite);
				flock($f_log, LOCK_UN);
			}

			fclose($f_log);
		}

		if ($this->_notify && defined('MAIL_EVENTS_STATUS') && $this->_status >= MAIL_EVENTS_STATUS)
		{
			// Save old error level
			$iErrorLevel = error_reporting(E_ERROR);

			$message_mail = Core::_('Core.error_message', $sDate, $message,
				Core::_('Core.error_log_level_' . $this->_status),
				$this->_login, $this->_site, $page, $user_ip, 'HostCMS', 'www.hostcms.ru'
			);

			$email = defined('ERROR_EMAIL')
				? ERROR_EMAIL
				: SUPERUSER_EMAIL;

			$subject = "HostCMS - {$sHttpHost}: " . trim(strip_tags(mb_substr($message, 0, 150)));

			Core_Mail::instance()
				->to($email)
				->from($email)
				->subject($subject)
				->message($message_mail)
				->contentType('text/plain')
				->header('X-HostCMS-Reason', 'Alert')
				->header('Precedence', 'bulk')
				->send();

			error_reporting($iErrorLevel);
		}

		return $this;
	}

	/**
	 * Delete old log files
	 * @return self
	 */
	protected function _deleteOldLogs()
	{
		if ($handle = @opendir($this->_logDir))
		{
			while (FALSE !== ($file = @readdir($handle)))
			{
				if ($file != '.' && $file != '..')
				{
					if (Core_File::getExtension($file) == 'csv')
					{
						$aFileName = explode('.', $file);

						if (isset($aFileName[0]))
						{
							$aFileTime = explode('_', $aFileName[0]);

							if (count($aFileTime) == 3)
							{
								$file_date = mktime(23, 59, 59, intval($aFileTime[1]), intval($aFileTime[0]), intval($aFileTime[2]));

								if ($file_date < (time() - LOG_DAYS_LIMIT * 86400))
								{
									@unlink($this->_logDir . DIRECTORY_SEPARATOR . $file);
								}
							}
						}
					}
				}
			}
			@closedir($handle);
		}

		return $this;
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_status = 0;
		$this->_site = $this->_login = NULL;
		$this->_notify = TRUE;

		return $this;
	}
}