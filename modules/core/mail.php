<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract mail
 *
 * <code>
 * Core_Mail::instance()
 * 	->to($email)
 * 	->from($email)
 * 	->subject($subject)
 * 	->message($message_mail)
 * 	->contentType('text/plain')
 * 	//->header('Cc', 'copy@email.com')
 * 	//->header('Bcc', 'hidden-copy@email.com')
 * 	->header('X-HostCMS-Reason', 'Alert')
 * 	->header('Precedence', 'bulk')
 * 	->attach(array(
 * 		'filepath' => $include_file,
 * 		'filename' => $file
 * 	))
 * 	->send();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Mail
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Mail
{
	/**
	 * Status
	 * @var string
	 */
	protected $_status = FALSE;

	/**
	 * Get status
	 * @return boolean
	 */
	public function getStatus()
	{
		return $this->_status;
	}

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return string
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	protected $_log = NULL;

	/**
	 * Log error
	 * @return boolean
	 */
	public function log()
	{
		if (!is_null($this->_log))
		{
			Core_Log::instance()->clear()
				->notify(FALSE) // avoid recursion
				->status(Core_Log::$MESSAGE)
				->write(sprintf('MAIL LOG: "%s"', $this->_log));
		}

		return TRUE;
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name
	 * @param array $aPersonalConfig
	 * @return object
	 */
	static public function instance($name = 'default', array $aPersonalConfig = array())
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		$aConfig = Core::$config->get('core_mail', array());

		if (!isset($aConfig[$name]) || !isset($aConfig[$name]['driver']))
		{
			throw new Core_Exception('Core_Mail "%name" configuration doesn\'t defined', array('%name' => $name));
		}

		$driver = self::_getDriverName($aConfig[$name]['driver']);
		$oDriver = new $driver();

		$aConfigDriver = Core_Array::get($aConfig, $aConfig[$name]['driver'], array());

		return $oDriver->config(
			$aPersonalConfig + (
				defined('CURRENT_SITE') && isset($aConfigDriver[CURRENT_SITE])
					? $aConfigDriver[CURRENT_SITE]
					: $aConfigDriver
			) + array(
				'host' => NULL,
				'port' => 25,
				'log' => FALSE,
				'timeout' => 5
			)
		);
	}

	/**
	 * Clear object
	 *
	 * @return self
	 */
	public function clear()
	{
		$this
			->bound('----------' . mb_strtoupper(uniqid(time())))
			->separator("\r\n")
			->chunklen(76)
			->contentType('text/plain');

		$this->_headers = $this->_files = array();

		$this->_to = $this->_from = $this->_subject = $this->_message
			= $this->_senderName = $this->_recipientName = $this->_log = NULL;

		$this->_multipartRelated = FALSE;

		return $this;
	}

	/**
	 * List of parameters
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Set parameters
	 * @param array $array parameters
	 * @return self
	 */
	public function config($array)
	{
		$this->_config = $array;
		return $this;
	}

	/**
	 * Send mail
	 * @param string $to recipient
	 * @param string $subject subject
	 * @param string $message content
	 */
	abstract protected function _send($to, $subject, $message);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->clear();
	}

	/**
	 * Separator
	 * @var string
	 */
	protected $_separator = NULL;

	/**
	 * Set separator
	 * @param string $separator separator
	 * @return self
	 */
	public function separator($separator)
	{
		$this->_separator = $separator;
		return $this;
	}

	/**
	 * Get separator
	 * @return string
	 */
	public function getSeparator()
	{
		return $this->_separator;
	}

	/**
	 * The chunk length.
	 * @var string
	 */
	protected $_chunklen = NULL;

	/**
	 * Set chunk length
	 * @param int $chunklen The chunk length
	 * @return self
	 */
	public function chunklen($chunklen)
	{
		$this->_chunklen = $chunklen;
		return $this;
	}

	/**
	 * Mail TO field
	 * @var string
	 */
	protected $_to = NULL;

	/**
	 * Set recipient address
	 * @param string $to address
	 * @return self
	 */
	public function to($to)
	{
		$this->_to = $to;
		return $this;
	}

	/**
	 * Get recipient address
	 * @return string
	 */
	public function getTo()
	{
		return $this->_to;
	}

	/**
	 * Mail FROM field
	 * @var string
	 */
	protected $_from = NULL;

	/**
	 * Set sender address
	 * @param string $from from value
	 * @return self
	 */
	public function from($from)
	{
		$this->_from = $from;
		return $this;
	}

	/**
	 * Get sender address
	 * @return string
	 */
	public function getFrom()
	{
		return $this->_from;
	}

	/**
	 * Subject
	 * @var string
	 */
	protected $_subject = NULL;

	/**
	 * Set subject
	 * @param string $subject subject text
	 * @return self
	 */
	public function subject($subject)
	{
		$this->_subject = $subject;
		return $this;
	}

	/**
	 * Get subject
	 * @return string
	 */
	public function getSubject()
	{
		return $this->_subject;
	}

	/**
	 * Message text
	 * @var string
	 */
	protected $_message = NULL;

	/**
	 * Set message
	 * @param string $message message text
	 * @return self
	 */
	public function message($message)
	{
		$this->_message = $message;
		return $this;
	}

	/**
	 * Get message
	 * @return string
	 */
	public function getMessage()
	{
		return $this->_message;
	}

	/**
	 * Sender name
	 * @var string
	 */
	protected $_senderName = NULL;

	/**
	 * Set sender name
	 * @param string $senderName name
	 * @return self
	 */
	public function senderName($senderName)
	{
		$this->_senderName = $senderName;
		return $this;
	}

	/**
	 * Recipient name
	 * @var string
	 */
	protected $_recipientName = NULL;

	/**
	 * Set recipient name
	 * @param string $recipientName name
	 * @return self
	 */
	public function recipientName($recipientName)
	{
		$this->_recipientName = $recipientName;
		return $this;
	}

	/**
	 * Boundary
	 * @var string
	 */
	protected $_bound = NULL;

	/**
	 * Set boundary
	 * @param string $bound boundary value
	 * @return self
	 */
	public function bound($bound)
	{
		$this->_bound = $bound;
		return $this;
	}

	/**
	 * Mail Content-Type
	 * @var string
	 */
	protected $_contentType = 'text/plain';

	/**
	 * Set content type
	 * @param string $contentType type
	 * @return self
	 */
	public function contentType($contentType)
	{
		$this->_contentType = $contentType;
		return $this;
	}

	/**
	 * Multipart related mode
	 * @var boolean
	 */
	protected $_multipartRelated = FALSE;

	/**
	 * Set multipart related mode
	 * @param boolean $multipartRelated mode
	 * @return self
	 */
	public function multipartRelated($multipartRelated)
	{
		$this->_multipartRelated = $multipartRelated;
		return $this;
	}

	/**
	 * Sanitize Header Value
	 * @param string $value
	 * @return string
	 */
	static public function sanitizeHeader($value)
	{
		return str_replace(array("\r", "\n", "\0"), '', (string) $value);
	}

	/**
	 * List of headers
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * Set headers
	 * @param string $name name
	 * @param string $value value
	 * @return self
	 */
	public function header($name, $value)
	{
		$this->_headers[$name] = !is_null($value)
			? self::sanitizeHeader($value)
			: '';

		return $this;
	}

	/**
	 * List of attached files
	 * @var array
	 */
	protected $_files = array();

	/**
	 * Attach file
	 * @param string $attach file path
	 *
	 * <code>
	 * $oCore_Mail->attach(array(
	 * 'filepath' => CMS_FOLDER . 'file.jpg',
	 * 'filename' => 'file.jpg',
	 * 'Content-ID' => '123456',
	 * // attachment or inline
	 * 'Content-Disposition' => 'attachment',
	 * 'Content-Type' => 'application/octet-stream'
	 * ));
	 * </code>
	 */
	public function attach($attach)
	{
		$this->_files[] = $attach;
		return $this;
	}

	protected function _getChunkedMessage()
	{
		return !is_null($this->_message)
			? chunk_split(base64_encode($this->_message), $this->_chunklen, $this->_separator)
			: '';
	}

	/**
	 * Send mail
	 *
	 * @return self
	 * @hostcms-event Core_Mail.onBeforeSend
	 * @hostcms-event Core_Mail.onAfterPrepareSend
	 */
	public function send()
	{
		Core_Event::notify('Core_Mail.onBeforeSend', $this);

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		$sFrom = !is_null($this->_senderName)
			// NO SPACES BETWEEN name and <email> // rolled back
			? '=?UTF-8?B?' . base64_encode($this->_senderName) . "?= <{$this->_from}>"
			: $this->_from;

		$this
			->header('From', $sFrom)
			->header('X-Mailer', 'HostCMS/7.0');

		$sTo = !is_null($this->_recipientName)
			// NO SPACES BETWEEN name and <email> // rolled back
			? '=?UTF-8?B?' . base64_encode($this->_recipientName) . "?= <{$this->_to}>"
			: (!is_null($this->_to) && strlen($this->_to)
				? "<{$this->_to}>"
				: ''
			);

		if (!isset($this->_headers['Reply-To']))
		{
			$this->header('Reply-To', "<{$this->_from}>");
		}

		if (!isset($this->_headers['Return-Path']))
		{
			$this->header('Return-Path', "<{$this->_from}>");
		}

		$bMultipart = count($this->_files) > 0;

		$this
			->header('MIME-Version', '1.0');

		if ($bMultipart)
		{
			$this->header('Content-Type', "multipart/mixed; boundary=\"{$this->_bound}\"");
		}
		else
		{
			$this
				->header('Content-Type', "{$this->_contentType}; charset=UTF-8")
				->header('Content-Transfer-Encoding', 'base64');
		}

		$sSingleSeparator = $this->_separator;
		$sDoubleSeparators = $sSingleSeparator . $sSingleSeparator;

		if (!$bMultipart)
		{
			$content = $this->_getChunkedMessage();
		}
		else
		{
			$content = "This is a multi-part message in MIME format.{$sDoubleSeparators}";

			// Для почтовых рассылок, чтобы картинки были внутри письма
			if ($this->_multipartRelated)
			{
				/*
				1. Multipart/Mixed
				1.1. multipart/related для рассылок, чтобы картинки были внутри письма || Multipart/Alternative для остальных случаев
					1.1.1.1. Text/Plain или Text/HTML
					1.1.1.2. Файлы, если есть
				*/
				$content .= "--{$this->_bound}{$sSingleSeparator}";

				// Change bound
				$this->_bound = '---------==' . strtoupper(uniqid(time()));

				// _multipartRelated для почтовых рассылок, чтобы картинки были внутри письма
				//$content .= 'Content-Type: ' . (count($this->_files) > 0 && $this->_multipartRelated ? 'multipart/related' : 'multipart/alternative') . ';';
				$content .= "Content-Type: Multipart/Related;";
				$content .= " boundary=\"{$this->_bound}\"";
				$content .= $sDoubleSeparators;
			}

			$content .= "--{$this->_bound}{$sSingleSeparator}";
			$content .= "Content-Type: {$this->_contentType}; charset=UTF-8{$sSingleSeparator}";
			$content .= "Content-Transfer-Encoding: base64";
			$content .= $sDoubleSeparators;
			$content .= $this->_getChunkedMessage();
			$content .= $sDoubleSeparators;

			foreach ($this->_files as $value)
			{
				if (isset($value['filepath']) && isset($value['filename']) && Core_File::isFile($value['filepath']))
				{
					try
					{
						$content .= "--{$this->_bound}{$sSingleSeparator}";

						$ContentType = isset($value['Content-Type'])
							? $value['Content-Type']
							: 'application/octet-stream';

						$content .= "Content-Type: {$ContentType};";
						$content .= " name=\"{$value['filename']}\"{$sSingleSeparator}";
						$content .= "Content-Transfer-Encoding: base64{$sSingleSeparator}";

						if (isset($value['Content-ID']))
						{
							$content .= "Content-ID: <{$value['Content-ID']}>{$sSingleSeparator}";
						}

						$ContentDisposition = isset($value['Content-Disposition'])
							? $value['Content-Disposition']
							: 'attachment';

						$filename = '=?UTF-8?B?' . base64_encode($value['filename']) . '?=';
						$content .= "Content-Disposition: {$ContentDisposition};";
						$content .= " filename=\"{$filename}\"";
						$content .= $sDoubleSeparators;

						$content .= chunk_split(base64_encode(Core_File::read($value['filepath'])), $this->_chunklen, $this->_separator);
					}
					catch (Exception $e){}
				}

				$content .= $sDoubleSeparators;
			}

			// Final bound with -- at the end of line
			$content .= "--{$this->_bound}--{$sSingleSeparator}";
		}

		$subject = $this->_subject != ''
			? '=?UTF-8?B?' . base64_encode($this->_subject) . '?='
			: '';

		Core_Event::notify('Core_Mail.onAfterPrepareSend', $this, array($sTo, $subject, $content));

		return $this->_send($sTo, $subject, $content);
	}

	/**
	 * Get server hostname
	 * @return string
	 */
	public function getServerHostname()
	{
		if (isset($_SERVER['SERVER_NAME']))
		{
			return $_SERVER['SERVER_NAME'];
		}

		$gethostname = gethostname();
		if ($gethostname !== FALSE)
		{
			return $gethostname;
		}

		return 'localhost.localdomain';
	}

	/**
	 * Generate Message-ID
	 * @param $uniqueid
	 * @return self
	 */
	public function messageId($uniqueid = NULL)
	{
		if (strpos($this->_from, '@') !== FALSE)
		{
			$aTmp = explode('@', $this->_from);
			$domain = array_pop($aTmp);
		}
		else
		{
			$domain = $this->getServerHostname();
		}

		$this->header('Message-ID', '<' . (is_null($uniqueid) ? Core::generateUniqueId() : sha1($uniqueid)) . '.' . date('YmdHis') . '@' . $domain . '>');

		return $this;
	}

	/**
	 * Convert $this->_headers to string with $this->_separator as separator
	 * @return string
	 */
	protected function _headersToString()
	{
		$aHeaders = array();
		foreach ($this->_headers as $headerName => $headerValue)
		{
			$aHeaders[] = "{$headerName}: {$headerValue}";
		}

		return implode($this->_separator, $aHeaders);
	}
}