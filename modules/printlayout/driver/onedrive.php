<?php
defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Driver_Onedrive
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Printlayout_Driver_Onedrive extends Printlayout_Driver_Controller
{
	/**
	 * Extension
	 * @var string|NULL
	 */
	protected $_extension = 'pdf';

	/**
	 * File path
	 * @var string|NULL
	 */
	protected $_filePath = NULL;

	/**
	 * Get cloud
	 * @return object
	 */
	protected function _getCloud()
	{
		$oClouds = Core_Entity::factory('Site', CURRENT_SITE)->Clouds;
		$oClouds->queryBuilder()
			->where('clouds.type', '=', 'onedrive')
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

				$this->_filePath = tempnam(CMS_FOLDER . TMP_DIR, 'OND');

				if (!$oCloud_Controller->download($fileId, $this->_filePath, array('format' => 'pdf')))
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$MESSAGE)
						->write('Printlayout_Driver_Onedrive: download converted PDF error!');
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
				throw new Core_Exception('Printlayout_Driver_Onedrive: Cloud controller is NULL');
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