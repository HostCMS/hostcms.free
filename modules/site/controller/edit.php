<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		if (!$object->id)
		{
			$object->lng = Core::_('Site.lng_default');
		}

		parent::setObject($object);

		$oSiteTabAccessRights = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Site.site_chmod'))
			->name('AccessRights');

		$oSiteTabFormats = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Site.site_dates'))
			->name('Formats');

		$oSiteTabErrors = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Site.site_errors'))
			->name('Errors');

		$oSiteTabRobots = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Site.site_robots_txt'))
			->name('Robots');

		$oSiteTabLicense = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Site.site_licence'))
			->name('License');

		$oSiteTabCache = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Site.site_cache_options'))
			->name('Cache');

		$oMainTab = $this->getTab('main');

		$this->addTabAfter($oSiteTabAccessRights, $oMainTab)
			->addTabAfter($oSiteTabFormats, $oSiteTabAccessRights)
			->addTabAfter($oSiteTabErrors, $oSiteTabFormats)
			->addTabAfter($oSiteTabRobots, $oSiteTabErrors)
			->addTabAfter($oSiteTabLicense, $oSiteTabRobots);

		// Hide Cache tab
		if (Core::moduleIsActive('cache'))
		{
			$this->addTabAfter($oSiteTabCache, $oSiteTabLicense);
		}
		else
		{
			$this->skipColumns += array(
				'html_cache_use' => 'html_cache_use',
				'html_cache_with' => 'html_cache_with',
				'html_cache_without' => 'html_cache_without',
				'html_cache_clear_probability' => 'html_cache_clear_probability',
			);
		}

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

		$oSiteTabAccessRights
			->add($oSiteTabAccessRightsRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oSiteTabFormats
			->add($oSiteTabFormatsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteTabFormatsRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oSiteTabErrors
			->add($oSiteTabErrorsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteTabErrorsRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oSiteTabRobots
			->add($oSiteTabRobotsRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oSiteTabLicense
			->add($oSiteTabLicenseRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oSiteTabCache
			->add($oSiteTabCacheRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteTabCacheRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteTabCacheRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteTabCacheRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		/* $oMainRow1 */
		$this->getField('active')->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainTab->move($this->getField('active'), $oMainRow1);

		/* $oMainRow2 */
		$this->getField('coding')->divAttr(array('class' => 'form-group col-lg-3 col-md-3 col-sm-6'));
		$oMainTab->move($this->getField('coding'), $oMainRow2);
		$this->getField('sorting')->divAttr(array('class' => 'form-group col-lg-3 col-md-3 col-sm-6'));
		$oMainTab->move($this->getField('sorting'), $oMainRow2);

		$this->getField('locale')->divAttr(array('class' => 'form-group col-lg-3 col-md-3 col-sm-6'));

		// Список локалей, если доступен
		if (Core::isFunctionEnable('php_uname') && mb_substr(php_uname(), 0, 7) != "Windows"
			&& Core::isFunctionEnable('exec'))
		{
			@exec("locale -a", $sys_result);

			if (isset($sys_result) && count($sys_result) > 0)
			{
				$aLocales = array();

				foreach ($sys_result as $sLocale)
				{
					$sLocale = iconv('ISO-8859-1', 'UTF-8//IGNORE//TRANSLIT', trim($sLocale));
					$aLocales[$sLocale] = $sLocale;
				}

				$oMainTab->delete($this->getField('locale'));

				$oLocaleField = Admin_Form_Entity::factory('Select')
					->name('locale')
					->caption(Core::_('Site.locale'))
					->divAttr(array('class' => 'form-group col-lg-3 col-md-3 col-sm-6'))
					->options($aLocales)
					->value($this->_object->locale);

				$oMainRow2->add($oLocaleField);
			}
			else
			{
				$oMainTab->move($this->getField('locale'), $oMainRow2);
			}
		}
		else
		{
			$oMainTab->move($this->getField('locale'), $oMainRow2);
		}

		$oMainTab->delete(
			$this->getField('timezone')
		);

		$aTimezones = DateTimeZone::listIdentifiers();

		$oTimezoneField = Admin_Form_Entity::factory('Select');
		$oTimezoneField
			->name('timezone')
			->caption(Core::_('Site.timezone'))
			->divAttr(array('class' => 'form-group col-lg-3 col-md-3 col-sm-6'))
			->options(
				array('' => Core::_('site.default')) + array_combine($aTimezones, $aTimezones)
			)
			->value($this->_object->timezone);

		$oMainRow2->add($oTimezoneField);

		/* $oMainRow3 */
		$this->getField('max_size_load_image')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('max_size_load_image'), $oMainRow3);
		$this->getField('max_size_load_image_big')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('max_size_load_image_big'), $oMainRow3);

		/* $oMainRow4 */
		$this->getField('admin_email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('admin_email'), $oMainRow4);
		$this->getField('lng')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('lng'), $oMainRow4);

		/* $oMainRow5 */
		$this->getField('send_attendance_report')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('send_attendance_report'), $oMainRow5);
		$this->getField('safe_email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('safe_email'), $oMainRow5);

		/* $oMainRow6 */
		$this->getField('uploaddir')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('uploaddir'), $oMainRow6);
		$this->getField('nesting_level')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('nesting_level'), $oMainRow6);

		/* $oMainRow7 */
		$sFormPath = $this->_Admin_Form_Controller->getPath();
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oIcoFileField = Admin_Form_Entity::factory('File');
		$oIcoFileField
			->type("file")
			->caption(Core::_('Site.ico_files_uploaded'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->name("icofile")
			->id("icofile")
			->largeImage(
				array(
					'path' => is_file($this->_object->getIcoFilePath())
						? $this->_object->getIcoFileHref()
						: (
							is_file($this->_object->getPngFilePath())
								? $this->_object->getPngFileHref()
								: ''
						),
					'show_params' => FALSE,
					'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteIcoFile', windowId: '{$windowId}'}); return false",
				)
			)
			->smallImage(
				array(
					'show' => FALSE
				)
			);

		$oMainRow7->add($oIcoFileField);

		/* $oSiteTabAccessRightsRow1 */
		$this->getField('chmod')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('chmod'), $oSiteTabAccessRightsRow1);
		$this->getField('files_chmod')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('files_chmod'), $oSiteTabAccessRightsRow1);

		/* $oSiteTabFormatsRow1 */
		$this->getField('date_format')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('date_format'), $oSiteTabFormatsRow1);
		$this->getField('date_time_format')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('date_time_format'), $oSiteTabFormatsRow1);

		/* $oSiteTabFormatsRow2 */
		$this->getField('css_left')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('css_left'), $oSiteTabFormatsRow2);
		$this->getField('css_right')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('css_right'), $oSiteTabFormatsRow2);

		/* $oSiteTabErrorsRow1 & $oSiteTabErrorsRow2 & $oSiteTabErrorsRow3 */
		$this->getField('error')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('error'), $oSiteTabErrorsRow1);

		$oMainTab->delete(
			 $this->getField('error404')
		)->delete(
			 $this->getField('error403')
		)->delete(
			 $this->getField('closed')
		);

		$Structure_Controller_Edit = new Structure_Controller_Edit($this->_Admin_Form_Action);

		$aStructureData = array(' … ') + $Structure_Controller_Edit->fillStructureList($this->_object->id);

		$oSelect_404 = Admin_Form_Entity::factory('Select');
		$oSelect_404
			->options(
				$aStructureData
			)
			->name('error404')
			->value($this->_object->error404)
			->caption(Core::_('Site.error404'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

		$oSiteTabErrorsRow1->add($oSelect_404);

		$oSelect_403 = Admin_Form_Entity::factory('Select');
		$oSelect_403
			->options(
				$aStructureData
			)
			->name('error403')
			->value($this->_object->error403)
			->caption(Core::_('Site.error403'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oSiteTabErrorsRow2->add($oSelect_403);

		$oSelect_503 = Admin_Form_Entity::factory('Select');
		$oSelect_503
			->options(
				$aStructureData
			)
			->name('closed')
			->value($this->_object->closed)
			->caption(Core::_('Site.closed'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oSiteTabErrorsRow2->add($oSelect_503);

		/* $oSiteTabRobotsRow1 */
		$this->getField('robots')->rows(15)->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainTab->move($this->getField('robots'), $oSiteTabRobotsRow1);

		/* $oSiteTabLicenseRow1 */
		$this->getField('key')->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainTab->move($this->getField('key'), $oSiteTabLicenseRow1);

		/* $oSiteTabCache */
		$this->getField('html_cache_use')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
		$oMainTab->move($this->getField('html_cache_use'), $oSiteTabCacheRow1);

		$this->getField('html_cache_with')->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainTab->move($this->getField('html_cache_with'), $oSiteTabCacheRow2);

		$this->getField('html_cache_without')->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainTab->move($this->getField('html_cache_without'), $oSiteTabCacheRow3);

		$this->getField('html_cache_clear_probability')->divAttr(array('class' => 'form-group col-sm-6 col-md-6 col-sm-6 col-md-6 col-lg-6'));
		$oMainTab->move($this->getField('html_cache_clear_probability'), $oSiteTabCacheRow4);

		$oMainTab->delete(
			$this->getField('notes')
		);

		$this->title($this->_object->id
			? Core::_('Site.site_edit_site_form_title', $this->_object->name)
			: Core::_('Site.site_add_site_form_title'));

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Site_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		if (
			// Поле файла существует
			!is_null($aFileData = Core_Array::getFiles('icofile', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0)
		{
			// ICO
			if (Core_File::isValidExtension($aFileData['name'], array('ico')))
			{
				$this->_object->saveIcoFile($aFileData['tmp_name']);
			}
			// PNG
			elseif (Core_File::isValidExtension($aFileData['name'], array('png')))
			{
				$this->_object->savePngFile($aFileData['tmp_name']);
			}
			else
			{
				$this->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		$this->addMessage('<script type="text/javascript">$.loadSiteList()</script>');

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill sites list
	 * @return array
	 */
	public function fillSites()
	{
		$aReturn = array(' … ');

		$aSites = Core_Entity::factory('Site')->findAll(FALSE);
		foreach ($aSites as $oSite)
		{
			$aReturn[$oSite->id] = $oSite->name;
		}

		return $aReturn;
	}

}