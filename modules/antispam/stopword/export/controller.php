<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam_Stopword_Export_Controller
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Stopword_Export_Controller
{
	/**
	 * CSV data
	 * @var array
	 */
	protected $_aData = array();

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	protected function _prepareString($string)
	{
		return str_replace('"', '""', trim($string));
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if (!$oUser->superuser && $oUser->only_access_my_own)
		{
			return FALSE;
		}

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename = " . 'antispam_stopwords_' . date("Y_m_d_H_i_s") . '.csv' . ";");
		header("Content-Transfer-Encoding: binary");

		$offset = 0;
		$limit = 100;

		do {
			$oAntispam_Stopwords = Core_Entity::factory('Antispam_Stopword');
			$oAntispam_Stopwords->queryBuilder()
				->clearOrderBy()
				->orderBy('antispam_stopwords.id')
				->offset($offset)
				->limit($limit);

			$aAntispam_Stopwords = $oAntispam_Stopwords->findAll(FALSE);

			foreach ($aAntispam_Stopwords as $oAntispam_Stopword)
			{
				$this->_aData = array(
					sprintf('"%s"', $this->_prepareString($oAntispam_Stopword->value)),
				);

				$this->_printRow($this->_aData);
			}

			$offset += $limit;
		}
		while (count($aAntispam_Stopwords));

		exit();
	}

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		echo Core_Str::iconv('UTF-8', 'Windows-1251', implode(';', $aData)) . "\n";
		return $this;
	}
}