<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Favicon_Controller_Tab
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Site_Favicon_Controller_Tab extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'site_id'
	);

	/**
	 * Form controller
	 * @var Admin_Form_Controller
	 */
	protected $_Admin_Form_Controller = NULL;

	/**
	 * Site object
	 * @var Site_Model
	 */
	protected $_oSite = NULL;

	/**
	 * Constructor.
	 * @param Admin_Form_Controller $Admin_Form_Controller controller
	 */
	public function __construct(Admin_Form_Controller $Admin_Form_Controller)
	{
		parent::__construct();

		$this->_Admin_Form_Controller = $Admin_Form_Controller;
	}

	/**
	 * Get site favicons
	 * @return array
	 */
	protected function _getSiteFavicons()
	{
		$oSite = Core_Entity::factory('Site', $this->site_id);
		return $oSite->Site_Favicons->findAll(FALSE);
	}

	/**
	 * Get rel
	 * @return array
	 */
	protected function _getRels()
	{
		return array(
			'icon' => 'icon',
			'shortcut icon' => 'shortcut icon',
			'apple-touch-icon' => 'apple-touch-icon',
			'apple-touch-icon-precomposed' => 'apple-touch-icon-precomposed'
		);
	}

	/**
	 * Get sizes
	 * @return array
	 */
	protected function _getSizes()
	{
		return array(
			'16x16' => '16x16',
			'32x32' => '32x32',
			'48x48' => '48x48',
			'120x120' => '120x120',
			'180x180' => '180x180',
			'192x192' => '192x192',
			'256x256' => '256x256',
			'512x512' => '512x512'
		);
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$sFormPath = $this->_Admin_Form_Controller->getPath();

		$aSite_Favicons = $this->_getSiteFavicons();

		$oSiteFaviconDiv = Admin_Form_Entity::factory('Div');

		$oDivOpen = Admin_Form_Entity::factory('Code')->html('<div class="row site_favicons item_div clear">');
		$oDivClose = Admin_Form_Entity::factory('Code')->html('</div>');

		$oRel = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Site_Favicon.rel'))
			->name('siteFaviconRel_[]')
			->options($this->_getRels())
			->divAttr(array('class' => 'form-group col-xs-6 col-md-3'));

		$oSizes = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Site_Favicon.sizes'))
			->name('siteFaviconSizes_[]')
			->options($this->_getSizes())
			->divAttr(array('class' => 'form-group col-xs-6 col-md-2'));

		$oFavicon = Admin_Form_Entity::factory('File');
		$oFavicon
			->type('file')
			->caption(Core::_('Site_Favicon.filename'))
			->divAttr(array('class' => 'input-group1 col-xs-10 col-md-5 col-lg-6'))
			->name("siteFaviconFile_[]")
			->largeImage(
				array(
					'show_params' => FALSE,
				)
			)
			->smallImage(
				array(
					'show' => FALSE
				)
			);

		if (count($aSite_Favicons))
		{
			foreach ($aSite_Favicons as $oSite_Favicon)
			{
				$oRel = clone $oRel;
				$oSizes = clone $oSizes;
				$oFavicon = clone $oFavicon;

				$oSiteFaviconDiv
					->add($oDivOpen)
					->add(
						$oRel
							->value($oSite_Favicon->rel)
							->name("siteFaviconRel_{$oSite_Favicon->id}")
							->id("siteFaviconRel_{$oSite_Favicon->id}")
					)
					->add(
						$oSizes
							->value($oSite_Favicon->sizes)
							->name("siteFaviconSizes_{$oSite_Favicon->id}")
							->id("siteFaviconSizes_{$oSite_Favicon->id}")
					)
					->add(
							$oFavicon
							->largeImage(
								array(
									'path' => $oSite_Favicon->filename != '' && Core_File::isFile($oSite_Favicon->getFaviconPath())
										? $oSite_Favicon->getFaviconHref()
										: '',
									'show_params' => FALSE,
									'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][0][{$this->site_id}]=1&site_favicon_id={$oSite_Favicon->id}', action: 'deleteFavicon', windowId: '{$windowId}'}); return false",
								)
							)
							->name("siteFaviconFile_{$oSite_Favicon->id}")
							->id("siteFaviconFile_{$oSite_Favicon->id}")
					)
					->add($this->imgBox())
					->add($oDivClose)
				;
			}
		}
		else
		{
			$oSiteFaviconDiv
				->add($oDivOpen)
				->add($oRel)
				->add($oSizes)
				->add($oFavicon)
				->add($this->imgBox())
				->add($oDivClose)
			;
		}

		return $oSiteFaviconDiv;
	}

	/**
	 * Apply object property
	 */
	public function applyObjectProperty()
	{
		$aSite_Favicons = $this->_getSiteFavicons();
		foreach ($aSite_Favicons as $oSite_Favicon)
		{
			$rel = Core_Array::getPost("siteFaviconRel_{$oSite_Favicon->id}");

			if (!is_null($rel) && $rel !== '')
			{
				$oSite_Favicon
					->rel(Core_Array::getPost("siteFaviconRel_{$oSite_Favicon->id}", '', 'trim'))
					->sizes(Core_Array::getPost("siteFaviconSizes_{$oSite_Favicon->id}", '', 'trim'))
					->save();

				if (isset($_FILES["siteFaviconFile_{$oSite_Favicon->id}"]))
				{
					$aFile = $_FILES["siteFaviconFile_{$oSite_Favicon->id}"];

					$oSite_Favicon->saveFavicon($aFile['name'], $aFile['tmp_name']);
				}
			}
			else
			{
				$oSite_Favicon->markDeleted();
			}
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$aFavicons = Core_Array::getPost('siteFaviconRel_', array(), 'array');

		if (count($aFavicons))
		{
			$aSiteFaviconRel = Core_Array::getPost('siteFaviconRel_');
			$aSiteFaviconSizes = Core_Array::getPost('siteFaviconSizes_');
			// $aSiteFaviconFile = Core_Array::getPost('siteFaviconFile_');

			foreach ($aFavicons as $key => $favicon)
			{
				if ($favicon !== '')
				{
					$oSite_Favicon = Core_Entity::factory('Site_Favicon')
						->rel(Core_Array::get($aSiteFaviconRel, $key, '', 'trim'))
						->sizes(Core_Array::get($aSiteFaviconSizes, $key, '', 'trim'))
						->save();

					if (isset($_FILES['siteFaviconFile_']['name'][$key]))
					{
						var_dump($_FILES['siteFaviconFile_']['name'][$key]);
						var_dump($_FILES['siteFaviconFile_']['tmp_name'][$key]);
						$oSite_Favicon->saveFavicon($_FILES['siteFaviconFile_']['name'][$key], $_FILES['siteFaviconFile_']['tmp_name'][$key]);
					}

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(\"#{$windowId} select[name='siteFaviconRel_\\[\\]']\").eq(0).prop('name', 'siteFaviconRel_{$oSite_Favicon->id}');
						$(\"#{$windowId} select[name='siteFaviconSizes_\\[\\]']\").eq(0).prop('name', 'siteFaviconSizes_{$oSite_Favicon->id}');
						$(\"#{$windowId} select[name='siteFaviconFile_\\[\\]']\").eq(0).prop('name', 'siteFaviconFile_{$oSite_Favicon->id}');
						")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
			}
		}
	}

	public function imgBox($addFunction = '$.cloneSiteFavicon', $deleteOnclick = '$.deleteNewSiteFavicon(this)')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		ob_start();
			Admin_Form_Entity::factory('Div')
				->class('no-padding add-remove-property margin-top-20 pull-left')
				->add(
					Admin_Form_Entity::factory('Div')
						->class('btn btn-palegreen')
						->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-plus-circle close"></i>'))
						->onclick("{$addFunction}('{$windowId}', this);")
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('btn btn-darkorange btn-delete')
						->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-minus-circle close"></i>'))
						->onclick($deleteOnclick)
				)
				->execute();

		return Admin_Form_Entity::factory('Code')->html(ob_get_clean());
	}
}