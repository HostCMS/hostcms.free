<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sites.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Site_Controller_Backend extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('user_id');

		parent::setObject($object);

		$oMainTab = Admin_Form_Entity::factory('Tab')
			->caption('Main')
			->name('main');

		$this->addTab($oMainTab);

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		ob_start();

		?><div class="col-xs-12">
			<div class="backend-wrapper">
				<div class="text"><?php echo Core::httpsUses() ? 'https' : 'http';?>://<?php echo $_SERVER['HTTP_HOST']?>/</div>
				<div><input type="text" name="backend" class="form-control" value="<?php echo Core::$mainConfig['backend']?>"/></div>
				<div class="text">/</div>
			</div>
		</div><?php

		$oMainRow1->add(
			Admin_Form_Entity::factory('Code')
			->html(ob_get_clean())
		);

		$this->title(Core::_('Site.backend_title'));

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Site_Controller_AccountInfo.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty777777()
	{
		//parent::_applyObjectProperty();

		$secret_csrf = Core_Array::getPost('secret_csrf', '', 'trim');
		$this->_checkCsrf($secret_csrf);

		$backend = Core_Array::getPost('backend', '', 'trim');

		if (Core::$mainConfig['backend'] != '' && $backend != '')
		{
			$oldDir = CMS_FOLDER . Core::$mainConfig['backend'] . '/';

			if (is_dir($oldDir) && is_writable($oldDir))
			{
				$dir = CMS_FOLDER . $backend . '/';

				//$bReturn = rename($oldDir, $dir);
				$bReturn = FALSE;

				if ($bReturn)
				{
					$aConfig = Core::$config->get('core_config');
					$aConfig['backend'] = $backend;
					Core::$config->set('core_config', $aConfig);

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(window).unbind(); window.location = '/" . Core_Str::escapeJavascriptVariable($backend) . "/site/index.php'")
						->execute();
					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

		/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$secret_csrf = Core_Array::getPost('secret_csrf', '', 'trim');
			$this->_checkCsrf($secret_csrf);

			$backend = Core_Array::getPost('backend', '', 'trim');

			if (Core::$mainConfig['backend'] != $backend && $backend != '')
			{
				$oldDir = CMS_FOLDER . Core::$mainConfig['backend'] . '/';

				if (is_dir($oldDir) && is_writable($oldDir))
				{
					$dir = CMS_FOLDER . $backend . '/';

					$bReturn = rename($oldDir, $dir);

					if ($bReturn)
					{
						$aConfig = Core::$config->get('core_config');
						$aConfig['backend'] = $backend;
						Core::$config->set('core_config', $aConfig);

						ob_start();
						Core_Html_Entity::factory('Script')
							->value("$(window).unbind(); window.location = '/" . Core_Str::escapeJavascriptVariable($backend) . "/site/index.php'")
							->execute();
						$this->_Admin_Form_Controller->addMessage(ob_get_clean());
					}
					else
					{
						$this->addMessage(
							Core_Message::get(Core::_('Site.rename_backend_error'), 'error')
						);
						return TRUE;
					}
				}
				
				return FALSE;
			}
			else
			{
				return NULL;
			}
			
			
		}

		return parent::execute($operation);
	}

	/**
	 * Get save button
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _getSaveButton()
	{
		return NULL;
	}
}