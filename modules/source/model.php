<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Source_Model
 *
 * @package HostCMS
 * @subpackage Source
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Source_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Show badge
	 * @return string
	 */
	public function showBadge()
	{
		$title = htmlspecialchars((string) $this->service);

		switch ($this->service)
		{
			case 'google':
				$sResult = ' <span title="' . $title . '" class="badge badge-blue white"><i class="fa-brands fa-google"></i></span>';
			break;
			case 'direct.yandex.ru':
			case 'yandex.ru':
			case 'ya.ru':
			case 'yandex':
			case 'ya':
 				$sResult = ' <span title="' . $title . '" class="badge badge-darkorange white"><i class="fa-brands fa-yandex"></i></span>';
			break;
			case 'mail.ru':
 				$sResult = ' <span title="' . $title . '" class="badge badge-sky white">mail.ru</span>';
			break;
			case 'rambler.ru':
 				$sResult = ' <span title="' . $title . '" class="badge badge-primary white">Rambler</span>';
			break;
			case 'bing':
 				$sResult = ' <span title="' . $title . '" class="badge badge-default white"><i class="fa-brands fa-microsoft"></i></span>';
			break;
			case 'vk':
 				$sResult = ' <span title="' . $title . '" class="badge badge-sky white"><i class="fa-brands fa-vk"></i></span>';
			break;
			case 'telegram':
 				$sResult = ' <span title="' . $title . '" class="badge badge-sky white"><i class="fa-brands fa-telegram"></i></span>';
			break;
			case 'ok':
 				$sResult = ' <span title="' . $title . '" class="badge badge-warning white"><i class="fa-brands fa-odnoklassniki"></i></span>';
			break;
			case 'instagram':
 				$sResult = ' <span title="' . $title . '" class="badge badge-darkpink white"><i class="fa-brands fa-instagram"></i></span>';
			break;
			case 'facebook':
 				$sResult = ' <span title="' . $title . '" class="badge badge-primary white"><i class="fa-brands fa-facebook"></i></span>';
			break;
			case 'youtube':
 				$sResult = ' <span title="' . $title . '" class="badge badge-danger white"><i class="fa-brands fa-youtube"></i></span>';
			break;
			case 'tiktok':
 				$sResult = ' <span title="' . $title . '" class="badge badge-black white"><i class="fa-brands fa-tiktok"></i></span>';
			break;
			default:
				$sResult = ' <span title="' . $title . '" class="badge badge-palegreen white"><i class="fa-solid fa-tags"></i></span>';
			break;
		}

		return $sResult;
	}
}