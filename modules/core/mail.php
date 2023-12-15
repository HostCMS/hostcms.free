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

		$aConfig = $aPersonalConfig + (
			defined('CURRENT_SITE') && isset($aConfigDriver[CURRENT_SITE])
				? $aConfigDriver[CURRENT_SITE]
				: $aConfigDriver
		) + array(
			'host' => NULL,
			'port' => 25,
			'log' => FALSE,
			'timeout' => 5,
			'dkim' => FALSE
		);

		if (isset($aConfig['dkim']) && is_array($aConfig['dkim']))
		{
			$aConfig['dkim'] += array(
				'hash' => 'sha256', // sha256|sha1
				'passphrase' => '',
				'selector' => 'mail',
				'domain' => NULL,
				'identity' => NULL,
				'body_canonicalization' => 'relaxed',
			);
		}

		return $oDriver->config(
			$aConfig
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
	 * Get domain from $this->_from address, e.g. 'google.com' for 'my@google.com'
	 */
	protected function _getDomain()
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

		return $domain;
	}

	/**
	 * Generate Message-ID
	 * @param $uniqueid
	 * @return self
	 */
	public function messageId($uniqueid = NULL)
	{
		$domain = $this->_getDomain();

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

	/**
	 * Get the array of the "relaxed" header canonicalization
	 * @param $sHeaders Headers
	 * @return array
	 */
	protected function _dkimRelaxedCanonicalizeHeader($sHeaders)
	{
		/* The "relaxed" header canonicalization algorithm MUST apply the following steps in order:
			o Convert all header field names (not the header field values) to lowercase. For example, convert "SUBJect: AbC" to "subject: AbC".

			o Unfold all header field continuation lines as described in [RFC5322]; in particular, lines with terminators embedded in continued header field values (that is, CRLF sequences followed by WSP) MUST be interpreted without the CRLF. Implementations MUST NOT remove the CRLF at the end of the header field value.

			o Convert all sequences of one or more WSP characters to a single SP character. WSP characters here include those before and after a line folding boundary.

			o Delete all WSP characters at the end of each unfolded header field value.

			o Delete any WSP characters remaining before and after the colon separating the header field name from the header field value. The colon separator MUST be retained.
		*/
		$aHeaders = array();

		$aSignedHeaders = array(
			'from',
			'mime-version',
			'reply-to',
			'subject',
			'to'
		);

		$sHeaders = preg_replace("/\n\s+/", ' ', $sHeaders);

		$aLines = explode("\r\n", $sHeaders);
		foreach ($aLines as $line)
		{
			$line = preg_replace("/\s+/", ' ', $line);

			if (!empty($line))
			{
				$line = explode(':', $line, 2);

				// Convert all header field names (not the header field values) to lowercase.
				$headerName = trim(strtolower($line[0]));
				$headerValue = trim($line[1]);

				if (in_array($headerName, $aSignedHeaders) || $headerName == 'dkim-signature')
				{
					$aHeaders[$headerName] = $headerName . ':' . $headerValue;
				}
			}
		}

		return $aHeaders;
	}

	/**
	 * The "relaxed" Body Canonicalization Algorithm
	 * @param string $body
	 * @return string
	 */
	protected function _dkimRelaxedCanonicalizeBody($body)
	{
		/* The "relaxed" Body Canonicalization Algorithm

		  The "relaxed" body canonicalization algorithm MUST apply the following steps (a) and (b) in order:

		  a. Reduce whitespace:
			  * Ignore all whitespace at the end of lines. Implementations MUST NOT remove the CRLF at the end of the line.
			  * Reduce all sequences of WSP within a line to a single SP character.

		  b. Ignore all empty lines at the end of the message body. "Empty line" is defined in Section 3.4.3.
		  If the body is non-empty but does not end with a CRLF, a CRLF is added. (For email, this is only possible when using extensions to SMTP or non-SMTP transport mechanisms.)

		  The SHA-1 value (in base64) for an empty body (canonicalized to a null input) is:
		  2jmj7l5rSw0yVb/vlWAYkK/YBwk=

		  The SHA-256 value is:
		  47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=
		*/

		// Return CRLF for empty body
		if ($body === '')
		{
			return "\r\n";
		}

		$aLines = explode("\r\n", $body);
		foreach ($aLines as $key => $value)
		{
			// Ignore all whitespace at the end of lines
			$value = rtrim($value);

			// Reduce all sequences of WSP within a line to a single SP character.
			$aLines[$key] = preg_replace('/\s+/', ' ', $value);
		}

		$body = implode("\r\n", $aLines);

		return $this->_dkimSimpleCanonicalizeBody($body);
	}

	/**
	 * The "simple" Body Canonicalization Algorithm
	 * @param string $body
	 * @return string
	 */
	protected function _dkimSimpleCanonicalizeBody($body)
	{
		/* The "simple" body canonicalization algorithm ignores all empty lines at the end of the message body.
		  An empty line is a line of zero length after removal of the line terminator. If there is no body or
		  no trailing CRLF on the message body, a CRLF is added. It makes no other changes to the message body.
		  In more formal terms, the "simple" body canonicalization algorithm converts "*CRLF" at the end of the body to a single "CRLF".

		  Note that a completely empty or missing body is canonicalized as a single "CRLF"; that is, the canonicalized length will be 2 octets.
		*/

		// Return CRLF for empty body
		if ($body === '')
		{
			return "\r\n";
		}

		// Convert "*CRLF" at the end of the body to a single "CRLF"
		while (mb_substr($body, -4) == "\r\n\r\n")
		{
			// Remove "\r\n"
			$body = mb_substr($body, 0, -2);
		}

		if (mb_substr($body, -2) != "\r\n")
		{
			$body .= "\r\n";
		}

		return $body;
	}

	/**
	 * Get DKIM-Signature header or empty string
	 * @param string $dkimRelaxedCanonicalizeHeader
	 * @param string dkimRelaxedCanonicalizeHeader
	 * @return string
	 */
	protected function _getDkimSignature($dkimRelaxedCanonicalizeHeader, $body)
	{
		if (!Core_File::isFile($this->_config['dkim']['private_key']))
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write('Core_Mail::_getDkimSignature. The private_key ' . $this->_config['dkim']['private_key'] . ' does not exist');

			return '';
		}

		$body = $this->_config['dkim']['body_canonicalization'] == 'relaxed'
			? $this->_dkimRelaxedCanonicalizeBody($body)
			: $this->_dkimSimpleCanonicalizeBody($body);

		$FWS = "\r\n\t";

		// The hash of the canonicalized body part of the message
		$bh = rtrim(
			chunk_split(base64_encode(
				pack("H*", hash($this->_config['dkim']['hash'], $body))
			), 64,
			$FWS)
		);

		$domain = !is_null($this->_config['dkim']['domain'])
			? $this->_config['dkim']['domain']
			: $this->_getDomain();

		$DkimSignature = "DKIM-Signature: v=1;{$FWS}" .
			"a=rsa-{$this->_config['dkim']['hash']};{$FWS}" .
			"q=dns/txt;{$FWS}" .
			"s={$this->_config['dkim']['selector']};{$FWS}" .
			"t=" . time() . ";{$FWS}" .
			"c=relaxed/{$this->_config['dkim']['body_canonicalization']};{$FWS}" .
			"h=" . implode(':', array_keys($dkimRelaxedCanonicalizeHeader)) . ";{$FWS}" .
			"d={$domain};{$FWS}" .
			(is_null($this->_config['dkim']['identity'])
				? ''
				: "i={$this->_config['dkim']['identity']};{$FWS}"
			) .
			"bh={$bh};{$FWS}" .
			'b=';

		// Get the canonicalized version of the $DkimSignature
		$DkimSignatureCanonicalized = $this->_dkimRelaxedCanonicalizeHeader($DkimSignature);

		switch ($this->_config['dkim']['hash'])
		{
			case 'sha256':
				$algorithm = OPENSSL_ALGO_SHA256;
			break;
			case 'sha1':
				$algorithm = OPENSSL_ALGO_SHA1;
			break;
			default:
				throw new Core_Exception('Wrong dkim hash algorithm (supports "sha256" or "sha1"): ' . htmlspecialchars($this->_config['dkim']['hash']));
		}

		$private_key = openssl_pkey_get_private(Core_File::read($this->_config['dkim']['private_key']), $this->_config['dkim']['passphrase']);

		$sData = implode("\r\n", $dkimRelaxedCanonicalizeHeader) . "\r\n" . $DkimSignatureCanonicalized['dkim-signature'];
		if (openssl_sign($sData, $signature, $private_key, $algorithm))
		{
			$DkimSignature .= rtrim(
				chunk_split(base64_encode($signature), 64, $FWS)
			) . "\r\n";
		}
		else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write('Core_Mail::_getDkimSignature. openssl_sign error!');

			$DkimSignature = '';
		}

		return $DkimSignature;
	}
}