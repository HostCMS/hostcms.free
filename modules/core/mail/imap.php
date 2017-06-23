<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IMAP, POP3 and NNTP
 * http://php.net/manual/en/book.imap.php
 *
 * @package HostCMS
 * @subpackage Core\Mail
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Mail_Imap extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'server', // сервер для соединения (IMAP, POP3)
		'port',
		'type', // imap, pop3
		'login',
		'password',
		'ssl', // SSL соединение
		'delete', // Удалять сообщения после получения
	);

	/**
	 * Create an instance of the object
	 */
	public function __construct()
	{
		parent::__construct();

		$this->delete = FALSE;
		$this->ssl = FALSE;
	}

	/**
	 * Протокол соединения
	 * @var string
	 */
	protected $_protocol = NULL;

	/**
	 * IMAP stream
	 * @var resource
	 */
	protected $_stream = NULL;

	/**
	 * Массив, содержащий письма
	 * @var array
	 */
	protected $_aMessages = array();

	/**
	 * Get messages
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_aMessages;
	}

	/**
	 * List of errors
	 * @var array
	 */
	protected $_aErrors = array();

	/**
	 * Get last error
	 * @return mixed
	 */
	public function getLastError()
	{
		return isset($this->_aErrors[0]) ? $this->_aErrors[0] : NULL;
	}

	/**
	 * Get errors
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_aErrors;
	}

	/**
	 * Заголовки письма
	 * @var str
	 */
	protected $_headers = '';

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		// Если указан нестандартный порт, записываем его номер
		$aServer = explode(':', $this->server);

		$this->server = $aServer[0];
		is_null($this->port) && isset($aServer[1]) && $this->port = intval($aServer[1]);

		switch ($this->type)
		{
			case 'imap':
				is_null($this->port) && $this->port = $this->ssl ? 993 : 143;
				$this->_protocol = '/imap';
			break;
			case 'pop3':
				is_null($this->port) && $this->port = $this->ssl ? 995 : 110;
				$this->_protocol = '/pop3';
			break;
			default:
				throw new Core_Exception("Wrong type '%type', 'imap' and 'pop3' are possible.",
					array('%type' => $this->type)
				);
		}

		// Безопасное соединение TSL/SSL
		$this->ssl && $this->_protocol .= '/ssl';

		$this->_protocol .= '/novalidate-cert/notls';

		// Формируем имя ящика
		$mailbox = '{' . $this->server . ':' . $this->port . $this->_protocol . '}INBOX';

		$aParam = $this->ssl
			? array('DISABLE_AUTHENTICATOR' => 'GSSAPI') // PLAIN
			: array();

		// Устанавливаем соединение с почтовым сервером
		$this->_stream = version_compare(PHP_VERSION, '5.3.2') >= 0
			? @imap_open($mailbox, $this->login, $this->password, NULL, 0, $aParam)
			: @imap_open($mailbox, $this->login, $this->password);

		// Соединение с почтовым сервером не установлено
		if (!$this->_stream)
		{
			$this->_aErrors = imap_errors();
			return $this;
		}

		// Количество писем в почтовом ящике
		$iCount = @imap_num_msg($this->_stream);

		if (!$iCount)
		{
			// imap_errors для пресечения вывода сообщений об ошибках, в том числе, если ящик пуст
			imap_errors();
			imap_alerts();
			imap_close($this->_stream);
			return $this;
		}

		$i = 0;
		$this->_aMessages = array();

		while ($i < $iCount)
		{
			$imap_fetchheader = imap_fetchheader($this->_stream, $i + 1);

			// Fix bug 'Fatal error: imap_headerinfo(): Address buffer overflow'
			// header can't be more 16K length
			if (strlen($imap_fetchheader) > 16384)
			{
				// skip broken message
				$i++;
				continue;
			}

			// Метаданные сообщения (структура)
			$structure = imap_fetchstructure($this->_stream, $i + 1);

			// Тип сообщения
			$this->_aMessages[$i]['type'] = $structure->subtype;

			// Разбираем сообщение на части
			$this->_parseMessage($i);

			!isset($this->_aMessages[$i]['code']) && $this->_aMessages[$i]['code'] = '';

			// Сохраняем заголовки
			$this->_headers = '';
			$this->_aMessages[$i]['headers'] = imap_fetchbody($this->_stream, $i + 1, 0);

			// Заголовки письма
			$aImap_fetchheader = explode("\n", trim($imap_fetchheader));

			$fetchheader = array();
			foreach ($aImap_fetchheader as $key => $value)
			{
				$aValue = explode(':', $value);
				isset($aValue[1]) && $fetchheader[strtolower(trim(strval($aValue[0])))] = strtolower(trim(strval($aValue[1])));
			}
			$this->_aMessages[$i]['fetchheader'] = $fetchheader;

			$this->_aMessages[$i]['fetchheader_str'] = $this->_saveHeaders($fetchheader, $this->_aMessages[$i]['code']);

			// Заголовки письма
			$header_message = imap_headerinfo($this->_stream, $i + 1);

			// Дата письма
			$this->_aMessages[$i]['date'] = strftime("%d.%m.%Y %H:%M:%S", strtotime(
				isset($header_message->date) ? $header_message->date : $header_message->MailDate
			));

			// From
			if (isset($header_message->from))
			{
				// Узнаем отправителя
				$from = $header_message->from;
				foreach ($from as $key => $value)
				{
					// Почтовый ящик отправителя
					$this->_aMessages[$i]['from'] = $value->mailbox . "@" . $value->host;
				}
			}
			else
			{
				$this->_aMessages[$i]['from'] = '';
			}

			// Reply To заменяет From если передан
			if (isset($header_message->reply_to))
			{
				// Узнаем отправителя
				$reply_to = $header_message->reply_to;
				foreach ($reply_to as $key => $value)
				{
					// Почтовый ящик отправителя
					$this->_aMessages[$i]['from'] = $value->mailbox . "@" . $value->host;
				}
			}

			/*$this->_aMessages[$i]['subject'] = isset($header_message->subject)
				? iconv_mime_decode($header_message->subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8')
				: '';*/

			$this->_aMessages[$i]['subject'] = isset($header_message->subject)
				? mb_decode_mimeheader($header_message->subject)
				: '';

			$i++;
		}

		// Удалить письма после просмотра
		$this->delete && $this->_deleteMessages($this->_stream);

		// imap_errors() для пресечения вывода сообщений об ошибках, в том числе, если ящик пуст
		imap_errors();
		imap_alerts();

		imap_close($this->_stream);

		return $this;
	}

	/**
	 * Разбор структуры сообщения и сохранение результата в массив
	 *
	 * @param array $structure Массив со структурой сообщения
	 * @return array
	 */
	protected function _structure2array($structure)
	{
		$aStructureParts = array();
		$j = 0;

		if (isset($structure->parts))
		{
			// Разбираем структуру сообщения
			// [parts][0][]
			foreach ($structure->parts as $part_num => $part_param)
			{
				// [parts][0][type]
				foreach ($part_param as $attribute => $attributeValue)
				{
					if (is_array($attributeValue))
					{
						// [parts][0][parameters][]
						foreach ($attributeValue as $parameter_values)
						{
							if (is_object($parameter_values) && isset($parameter_values->attribute) && isset($parameter_values->value))
							{
								$aStructureParts[$j][mb_strtolower($attribute)][mb_strtolower(strval($parameter_values->attribute))] = strval($parameter_values->value);
							}
						}
					}
					elseif(is_object($attributeValue))
					{
						foreach ($attributeValue as $parameter_values_part_num => $parameter_values_part_values)
						{
							if (is_array($parameter_values_part_values))
							{
								$aStructureParts[$j][mb_strtolower($attribute)][mb_strtolower(strval($parameter_values_part_num))] = $parameter_values_part_values;
							}
							else
							{
								$aStructureParts[$j][mb_strtolower($attribute)][mb_strtolower(strval($attributeValue->attribute))] = strval($attributeValue->value);
							}
						}
					}
					else
					{
						$aStructureParts[$j][mb_strtolower($attribute)] = $attributeValue;
					}
				}

				if (isset($part_param->parts))
				{
					$aStructureParts[$j]['parts'] = $this->_structure2array($part_param);
				}

				$j++;
			}
		}
		else
		{
			foreach ($structure as $attribute => $attributeValue)
			{
				if (is_array($attributeValue))
				{
					// [parts][0][parameters][]
					foreach ($attributeValue as $parameter_num => $parameter_values)
					{
						if (!is_array($parameter_values))
						{
							// [parts][0][parameters][0][attribute]
							foreach ($parameter_values as $parameter_values_part_values)
							{
								if (!is_array($parameter_values_part_values) && isset($parameter_values->attribute) && isset($parameter_values->value))
								{
									$aStructureParts[$j][strtolower($parameter_values->attribute)] = $parameter_values->value;
								}
							}
						}
					}
				}
				else
				{
					$aStructureParts[$j][strtolower($attribute)] = $attributeValue;
				}
			}
		}

		return $aStructureParts;
	}

	/**
	 * Сохранение структуры сообщения
	 *
	 * @param array $aStructureParts Массив со структурой
	 * @return str
	 */
	protected function _saveStructure($aStructureParts)
	{
		if (is_array($aStructureParts) && count($aStructureParts))
		{
			foreach ($aStructureParts as $key => $val)
			{
				if (is_array($val) || is_object($val))
				{
					$this->_saveStructure($val);
				}
				else
				{
					$this->_headers .= "\n{$key}: {$val}";
				}
			}
		}
		return $this->_headers;
	}

	/**
	 * Разбор сообщения по частям
	 *
	 * @param int $i Порядковый номер письма в ящике
	 */
	protected function _parseMessage($i)
	{
		// Метаданные сообщения
		$structure = imap_fetchstructure($this->_stream, $i + 1);

		// Если сообщение состоит из нескольких частей, формируем массив, каждый элемент которого будет содержанием соответствующей части сообщения
		$aStructureParts = $this->_aMessages[$i]['structure_array'] = $this->_structure2array($structure);

		$this->_headers = '';

		$this->_aMessages[$i]['body'] = '';
		$this->_aMessages[$i]['subtype'] = '';
		$this->_aMessages[$i]['structure'] = $this->_saveStructure($aStructureParts);

		// Индекс элемента массива вложений
		$n = 0;

		foreach ($aStructureParts as $iStructurePartNumber => $aStructurePart)
		{
			/*
			Виды $aStructurePart['type']
			[0] = "text"
			[1] = "multipart"
			[2] = "message"
			[3] = "application"
			[4] = "audio"
			[5] = "image"
			[6] = "video"
			[7] = "other"
			*/
			$body = imap_fetchbody($this->_stream, $i + 1, $iStructurePartNumber + 1);
			$body = $this->_bodyDecode($body, $aStructurePart['encoding']);

			// $aStructurePart encoding
			$charset = isset($aStructurePart['charset']) && $aStructurePart['charset'] != ''
				? $aStructurePart['charset']
				: NULL;

			is_null($charset) && $charset = isset($aStructurePart['parameters']['charset'])
				&& $aStructurePart['parameters']['charset'] != ''
					? $aStructurePart['parameters']['charset']
					: NULL;

			!is_null($charset)
				&& $body = mb_convert_encoding($body, 'UTF-8', $charset);

			$partType = Core_Array::get($aStructurePart, 'type', 0);

			switch ($partType)
			{
				// multipart
				case 1:
					if (isset($aStructurePart['parts']))
					{
						// Можно добавить параметр с предпочтительной кодировкой
						foreach ($aStructurePart['parts'] as $iPartNumber => $aPart)
						{
							// $aPart encoding
							$partCharset = isset($aPart['charset']) && $aPart['charset'] != ''
								? $aPart['charset']
								: NULL;

							is_null($partCharset) && $partCharset = isset($aPart['parameters']['charset'])
								&& $aPart['parameters']['charset'] != ''
									? $aPart['parameters']['charset']
									: NULL;

							/*
							()Root Message Part (multipart/related)
							(1) The text parts of the message (multipart/alternative)
							(1.1) Plain text version (text/plain)
							(1.2) HTML version (text/html)
							(2) The background stationary (image/gif)
							*/
							$massageNumber = $iStructurePartNumber + 1 + (
								($iPartNumber + 1) / 10
							);

							$sPartBody = $this->_bodyDecode(
								imap_fetchbody($this->_stream, $i + 1, $massageNumber), $aPart['encoding']
							);

							!is_null($partCharset)
								&& $sPartBody = mb_convert_encoding($sPartBody, 'UTF-8', $partCharset);

							$this->_aMessages[$i]['multipart'][$aPart['subtype']] = $sPartBody;
						}

						if (isset($this->_aMessages[$i]['multipart']['HTML']))
						{
							$this->_aMessages[$i]['subtype'] = 'HTML';
							$this->_aMessages[$i]['body'] = $this->_aMessages[$i]['multipart'][
								$this->_aMessages[$i]['subtype']
							];
						}
						elseif (isset($this->_aMessages[$i]['multipart']['PLAIN']))
						{
							$this->_aMessages[$i]['subtype'] = 'PLAIN';
							$this->_aMessages[$i]['body'] = $this->_aMessages[$i]['multipart'][
								$this->_aMessages[$i]['subtype']
							];
						}
						else
						{
							// В сообщение идет первый блок
							$defaultPart = $aStructurePart['parts'][0];

							$this->_aMessages[$i]['body'] = $this->_aMessages[$i]['multipart'][
								$defaultPart['subtype']
							];

							$this->_aMessages[$i]['subtype'] = $defaultPart['subtype'];
						}
					}
				break;
				// text
				case 0:
				// message
				case 2:
					// Если уже было тело письма, остальные идут как вложения
					if (!strlen($this->_aMessages[$i]['body']))
					{
						$this->_aMessages[$i]['subtype'] = Core_Array::get($aStructurePart, 'subtype', 'text');
						$this->_aMessages[$i]['body'] .= $body;

						// Если было тело письма, то остальное пойдет во вложения
						break;
					}
				// Other files
				default:
					// Тип вложения
					$this->_aMessages[$i]['attachments'][$n]['type'] = $partType;
					$this->_aMessages[$i]['attachments'][$n]['body'] = $body;

					if (isset($aStructurePart['parameters']['name']))
					{
						$this->_aMessages[$i]['attachments'][$n]['name'] = mb_decode_mimeheader(
							$aStructurePart['parameters']['name']
						);

					}
					elseif (isset($aStructurePart['dparameters']['filename']))
					{
						$this->_aMessages[$i]['attachments'][$n]['name'] = mb_decode_mimeheader(
							$aStructurePart['dparameters']['filename']
						);
					}
					$n++;
				break;
			}
		}

		return $this;
	}

	/**
	 * Удаление всех писем из почтового ящика
	 * @param resource $stream Идентификатор открытого соединения с почтовым сервером
	 */
	protected function _deleteMessages($stream)
	{
		// Количество писем в почтовом ящике
		$iCount = imap_num_msg($stream);

		// Помечаем на удаление все письма в ящике
		for ($i = 0; $i < $iCount; $i++)
		{
			imap_delete($stream, $i + 1);
		}

		// Удаляем письма, помеченные на удаление
		imap_expunge($stream);

		return $this;
	}

	/**
	 * Перекодировщик тела письма
	 *
	 * @param string $text Закодированная строка
	 * @param int $encoding Кодировка: 0 - 7bit, 1 - 8bit, 2 - Binary, 3 - Base64, 4 - Quoted-Printable, 5 - other
	 * @return string Раскодированная строка
	 */
	protected function _bodyDecode($text, $encoding = 0)
	{
		switch ($encoding)
		{
			case 1: // 8bit
				return imap_qprint(imap_8bit($text));
			case 2: // Binary
				return imap_base64(imap_binary($text));
			case 3: // Base64
				return imap_base64($text);
			case 4: // Quoted-Printable
				return imap_qprint($text);
			case 0: // 7bit
			case 5:
			default:
				return $text;
		}
	}

	/**
	 * Проверка строки на то, является ли она закодированной base64
	 *
	 * @param $string строка
	 * @return boolean
	 */
	protected function _isBase64($string)
	{
		if (empty($string))
		{
			return FALSE;
		}

		$length = mb_strlen($string);

		// Длина строки должна быть кратна 4
		// Исключено, т.к. могут быть переводы строк, которые меняют шаг
		/*if (($length % 4) > 0)
		{
			return FALSE;
		}*/
		// Проверка на равно, который разрешен в начале или в конце строки
		if (mb_strpos(mb_substr($string, 2, ($length - 4)), '=') === FALSE)
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Перекодировщик из кодировки письма в кодировку системы
	 *
	 * @param string $str Закодированная строка
	 * @param string $code Кодировка
	 * @return str
	 */
	protected function _headerDecode($str, $code = '')
	{
		// Передана пустая строка
		if ($str == '')
		{
			return $str;
		}

		// Декодирование MIME-заголовков сообщения
		$str_decoded = @imap_mime_header_decode($str);

		$str = '';

		// Составляем декодированную строку
		if (is_array($str_decoded))
		{
			foreach ($str_decoded as $obj)
			{
				$str .= $obj->text;
			}
		}

		return $this->_iconv($str, $code);
	}

	/**
	 * Перекодирование текста из кодировки письма в кодировку системы
	 *
	 * @param string $text Текст
	 * @param string $sourceCode Исходная кодировка
	 * @return string
	 */
	protected function _iconv($text, $sourceCode)
	{
		$text = strval($text);
		$sourceCode = trim(strtolower($sourceCode));

		if (strstr($sourceCode, 'utf'))
		{
			$sourceCode = 'utf-8';
		}
		elseif (strstr($sourceCode, 'koi8-r'))
		{
			$sourceCode = 'koi8-r';
		}
		elseif (strstr($sourceCode, 'koi8-u'))
		{
			$sourceCode = 'koi8-u';
		}
		elseif (strstr($sourceCode, 'windows-1251'))
		{
			$sourceCode = 'windows-1251';
		}

		if ($sourceCode != '' && $sourceCode != 'utf-8' && $sourceCode != 'x-unknown')
		{
			$text = @iconv($sourceCode, 'UTF-8//IGNORE', $text);
		}

		return $text;
	}

	/**
	 * Сохранение заголовков письма
	 *
	 * @param array $headers Массив заголовков
	 * @param string $code Кодировка
	 * @return string Строка заголовков
	 */
	protected function _saveHeaders($headers, $code)
	{
		if (is_array($headers))
		{
			foreach ($headers as $header => $value)
			{
				// Если есть подзаголовки
				if (is_array($value))
				{
					$array = array();
					foreach ($value as $v)
					{
						$array[] = $v;
					}

					$this->_saveHeaders($array, $code);
				}
				else
				{
					$this->_headers .= $header . ': ' . (
						strstr($value, '=?')
							? $this->_headerDecode($value, $code)
							: $this->_iconv($value, $code)
					) . "\n";
				}
			}
		}

		return $this->_headers;
	}
}