<?php
defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Driver_Google
 *
 * @package HostCMS 6
 * @subpackage Printlayout
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Driver_Google extends Printlayout_Driver_Controller
{
	protected $_extension = 'pdf';

	protected $_filePath = NULL;

	protected function _getCloud()
	{
		$oClouds = Core_Entity::factory('Site', CURRENT_SITE)->Clouds;
		$oClouds->queryBuilder()
			->where('clouds.type', '=', 'google')
			->where('clouds.active', '=', 1);

		return $oClouds->getFirst();
	}

	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		$oCloud = $this->_getCloud();

		if (!is_null($oCloud))
		{
			$oCloud_Controller = Cloud_Controller::factory($oCloud->id);

			if ($oCloud_Controller)
			{
				$fileId = $oCloud_Controller->upload($this->_sourceDocx, time() . '-' . rand(0, 99999) . '.docx', array('mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'));

				$this->_filePath = tempnam(CMS_FOLDER . TMP_DIR, 'GGL');
				
				if (!$oCloud_Controller->download($fileId, $this->_filePath, array('mimeType' => 'application/pdf')))
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$MESSAGE)
						->write('Printlayout_Driver_Google: download converted PDF error!');
				}

				// Удаляем tmp-файл
				Core_File::delete($this->_sourceDocx);

				// Удаляем tmp в облаке
				$oObject = new stdClass();
				$oObject->id = $fileId;
				$oCloud_Controller->delete($oObject);
			}
			else
			{
				throw new Core_Exception('Printlayout_Driver_Google: Cloud controller is NULL');
			}
		}

		return $this;
	}

	/**
	 * Get file
	 * @return string
	 */
	public function getFile()
	{
		return $this->_filePath;
	}

	/**
	 * Check available
	 */
	public function available()
	{
		return Core::moduleIsActive('cloud') && !is_null($this->_getCloud());
	}
}