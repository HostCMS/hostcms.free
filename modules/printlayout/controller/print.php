<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Controller_Print
 *
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Printlayout_Controller_Print extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title', // Form Title
		'skipColumns', // Array of skipped columns
		'buttonName',
		'printlayout',
		'send'
	);

	protected $_oMeta = NULL;

	protected $_object = NULL;

	protected $_email = NULL;

	protected $_oPrintlayout_Controller = NULL;

	protected $_newWindowId = 'id_content';

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->buttonName(Core::_('Admin_Form.apply'));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			$printlayout_id = intval(Core_Array::getGet('printlayout_id'));

			$shop_price_id = intval(Core_Array::getGet('shop_price_id', 0));
			$price = $shop_price_id
				? '&shop_price_id=' . $shop_price_id
				: '';

			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$this->_newWindowId = 'Printlayout_Print_' . time();

			$oCore_Html_Entity_Form = Core::factory('Core_Html_Entity_Form');

			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
				->id($this->_newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form
				->action($this->_Admin_Form_Controller->getPath() . '?printlayout_id=' . $printlayout_id . $price)
				->target('_blank')
				->method('post');

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				foreach ($checkedItems as $key => $value)
				{
					$oCore_Html_Entity_Form
						->add(
							 Core::factory('Core_Html_Entity_Input')
								->name('hostcms[checked][' . $datasetKey . '][' . $key . ']')
								->value(1)
								->type('hidden')
						)->add(
							 Core::factory('Core_Html_Entity_Input')
								->name('hostcms[action]')
								->value('print')
								->type('hidden')
						)->add(
							 Core::factory('Core_Html_Entity_Input')
								->name('hostcms[operation]')
								->value('apply')
								->type('hidden')
						);

					break;
				}
			}

			$type = intval(Core_Array::getGet('type'));

			// Shop_Order
			if ($type == 0)
			{
				$oShop_Order = Core_Entity::factory('Shop_Order')->getById($key, FALSE);
				!is_null($oShop_Order) && $this->_email = $oShop_Order->email;
			}

			$this->_prepare();

			$this->_oMeta = new Core_Meta();

			foreach ($this->_oPrintlayout_Controller->replace as $replaceSearch => $replaceValue)
			{
				!is_array($replaceValue)
					&& $this->_oMeta->addObject($replaceSearch, $replaceValue);
			}

			$oCore_Html_Entity_Form
				->add(Admin_Form_Entity::factory('Code')->html($this->_showEditForm()));

			// Download button
			$oCore_Html_Entity_Download = Core::factory('Core_Html_Entity_A')
				->title(Core::_('Printlayout.download'))
				->class('btn btn-success white download-button')
				->href('javascript:void(0);')
				->onclick('setTimeout(function() { bootbox.hideAll(); }, 500); $(this).closest("form").submit();')
				->add(
					Core::factory('Core_Html_Entity_I')
						->class('fa fa-download no-margin')
				);

			// Send mail button
			$oCore_Html_Entity_Mail = Core::factory('Core_Html_Entity_A')
				->title(Core::_('Printlayout.mail'))
				->class('btn btn-warning white mail-button')
				->href('javascript:void(0);')
				->onclick('
					$("#' . $windowId . ' .modal-dialog").width("60%");
					$("#' . $windowId . ' .modal-body").height(500);

					$("#' . $windowId . ' .control-group").addClass("printlayout-radio-inline");

					$("#' . $windowId . ' .download-button").addClass("hidden");
					$("#' . $windowId . ' .mail-button").addClass("hidden");
					$("#' . $windowId . ' .message-address, .message-text, .message-button, .deal-siteuser, .message-subject, .message-emails").removeClass("hidden");

					$("#' . $windowId . ' .email-select").select2({
						language: "' . Core_i18n::instance()->getLng() . '",
						minimumInputLength: 2,
						placeholder: "' . Core::_('Informationsystem_Item.type_tag') . '",
						tags: true,
						allowClear: true,
						multiple: true,
						width: "100%"
					});

					$("#' . $windowId . ' input[name*=\"action\"]").val("sendMail");
					$("#' . $windowId . ' input[name*=\"operation\"]").val("send");

					$(this).closest("form").removeAttr("target");
				')
				->add(
					Core::factory('Core_Html_Entity_I')
						->class('fa fa-envelope no-margin')
				);

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add($oCore_Html_Entity_Download)
						->add($oCore_Html_Entity_Mail)
				);

			$oCore_Html_Entity_Div->execute();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			ob_start();

			$iHeight = $this->_rowsCount < 5
				? 200
				: 100 + $this->_rowsCount * 30;

			Core::factory('Core_Html_Entity_Script')
				->value("$(function() {
					$('#{$this->_newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 250, height: {$iHeight}, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		elseif ($operation == 'apply')
		{
			$this
				->_prepare()
				->_print();
		}
		elseif ($operation == 'send' && $this->send)
		{
			$this
				->_prepare()
				->_send();

			return TRUE;
		}

		return $this;
	}

	abstract protected function _print();

	abstract protected function _prepare();

	protected function _send()
	{
		$oUser = Core_Auth::getCurrentUser();

		$userEmail = strlen(Core_Array::getPost('from'))
			? strval(Core_Array::getPost('from', ''))
			: $oUser->getEmail();

		$aEmails = Core_Array::getPost('email');
		if (is_array($aEmails))
		{
			$this->_oPrintlayout_Controller->execute();

			$subject = strval(Core_Array::getPost('subject'));
			$text = strval(Core_Array::getPost('text'));

			foreach ($aEmails as $key => $sEmail)
			{
				// Delay 0.350s for second mail and others
				$key > 0 && usleep(350000);

				$oCore_Mail = Core_Mail::instance()
					->clear()
					->to($sEmail)
					->from($userEmail)
					->subject($subject)
					->message($text)
					->contentType('text/plain')
					->header('X-HostCMS-Reason', 'Print')
					->messageId();

				$oCore_Mail->attach(array(
					'filepath' => $this->_oPrintlayout_Controller->getFilePath(),
					'filename' => $this->_oPrintlayout_Controller->getFileName()
				));

				$oCore_Mail->send();
			}

			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser = NULL;

				$representative = strval(Core_Array::getPost('representative'));

				$aTmp = explode('_', $representative);

				if (count($aTmp) == 2) // Представитель
				{
					switch($aTmp[0])
					{
						case 'person':
							$oEntity = Core_Entity::factory('Siteuser_Person')->getById($aTmp[1]);
						break;
						case 'company':
							$oEntity = Core_Entity::factory('Siteuser_Company')->getById($aTmp[1]);
						break;
						default:
							$oEntity = NULL;
					}

					!is_null($oEntity)
						&& $oSiteuser = $oEntity->Siteuser;
				}
				elseif (count($aTmp) == 1) // Клиент
				{
					$oSiteuser = Core_Entity::factory('Siteuser')->getById($aTmp[0]);
				}

				if (!is_null($oSiteuser))
				{
					$aConfig = Core_Config::instance()->get('siteuser_config', array());

					if ((!isset($aConfig['save_emails']) || $aConfig['save_emails']) && $oSiteuser->id)
					{
						$oSiteuser_Email = Core_Entity::factory('Siteuser_Email');
						$oSiteuser_Email->siteuser_id = $oSiteuser->id;
						$oSiteuser_Email->subject = $subject;
						$oSiteuser_Email->email = implode(', ', $aEmails);
						$oSiteuser_Email->from = $userEmail;
						$oSiteuser_Email->type = 0;
						$oSiteuser_Email->text = $text;
						$oSiteuser_Email->save();

						$oSiteuser_Email_Attachment = Core_Entity::factory('Siteuser_Email_Attachment');
						$oSiteuser_Email_Attachment->siteuser_email_id = $oSiteuser_Email->id;
						$oSiteuser_Email_Attachment->save();

						$oSiteuser_Email_Attachment->saveFile($this->_oPrintlayout_Controller->getFilePath(), $this->_oPrintlayout_Controller->getFileName());
					}
				}
			}

			// Delete file
			$this->_oPrintlayout_Controller->deleteFile();

			$this->addMessage(
				Core_Message::get(Core::_('Printlayout.sendMail_success'), 'success')
			);
		}
		else
		{
			$this->addMessage(
				Core_Message::get(Core::_('Printlayout.sendMail_error'), 'error')
			);
		}
	}

	protected $_rowsCount = 0;

	/**
	 * Show edit form
	 * @return boolean
	 */
	protected function _showEditForm()
	{
		$this->_rowsCount = 0;

		$oPrintlayout = Core_Entity::factory('Printlayout')->getById($this->printlayout);

		$oUser = Core_Auth::getCurrentUser();

		ob_start();

		if (!is_null($oPrintlayout))
		{
			$aColors = array(
				'blue',
				'success',
				'danger',
				'warning',
			);

			$message_text = !is_null($this->_oMeta)
				? $this->_oMeta->apply($oPrintlayout->mail_template)
				: $oPrintlayout->mail_template;

			$subject = !is_null($this->_oMeta)
				? $this->_oMeta->apply($oPrintlayout->file_mask)
				: $oPrintlayout->file_mask;

			?>
			<div class="form-group col-xs-12 message-subject hidden">
				<span class="caption"><?php echo Core::_('Printlayout.from')?></span>
				<input class="form-control" name="from" type="text" value="<?php echo htmlspecialchars($oUser->getEmail())?>"/>
			</div>
			<div class="form-group col-xs-12">
				<div class="control-group">
				<?php
					$aPrintlayout_Drivers = Core_Entity::factory('Printlayout_Driver')->getAllByActive(1);
					foreach ($aPrintlayout_Drivers as $key => $oPrintlayout_Driver)
					{
						$oPrintlayout_Driver_Controller = Printlayout_Driver_Controller::factory($oPrintlayout_Driver->driver);

						if ($oPrintlayout_Driver_Controller->available())
						{
							$this->_rowsCount++;

							$color = isset($aColors[$key])
								? $aColors[$key]
								: 'success';

							$checked = $key == 0
								? 'checked="checked"'
								: '';

							?><div class="radio">
								<label>
									<input name="driver_id" value="<?php echo htmlspecialchars($oPrintlayout_Driver->id)?>" type="radio" class="colored-<?php echo $color?>" <?php echo $checked?>>
									<span class="text"> <?php echo htmlspecialchars($oPrintlayout_Driver->name)?></span>
								</label>
							</div><?php
						}
					}
				?>
				</div>
			</div>
			<?php

			$windowId = $this->_Admin_Form_Controller->getWindowId();

			if (Core::moduleIsActive('siteuser'))
			{
				$oSelectSiteusers = Admin_Form_Entity::factory('Select')
					->id('representative')
					->name('representative')
					// ->value($this->_email)
					->caption(Core::_('Printlayout.siteuser'))
					->divAttr(array('class' => 'form-group col-xs-12 deal-siteuser hidden'))
					->execute();

				$oScriptSiteusers = Admin_Form_Entity::factory('Script')
					->value('
						$(function(){
							$("#' . $windowId . ' #representative").selectPersonCompany({
								language: "' . Core_i18n::instance()->getLng() . '",
								placeholder: ""
							});
							$("#' . $windowId . ' #representative").on("select2:select", function (e) {
								$.showEmails(e.params.data);
							});
						});
					')
					->execute();
			}
			?>
			<div class="form-group col-xs-12 message-emails hidden">
				<span class="caption">E-mail</span>
				<select name="email[]" class="form-control email-select" multiple="multiple">
					<?php
					if (!is_null($this->_email))
					{
						?><option selected="selected"><?php echo htmlspecialchars($this->_email)?></option><?php
					}
					?>
				</select>
			</div>
			<div class="form-group col-xs-12 message-subject hidden">
				<span class="caption"><?php echo Core::_('Printlayout.subject')?></span>
				<input class="form-control" name="subject" type="text" value="<?php echo htmlspecialchars($subject)?>"/>
			</div>
			<div class="form-group col-xs-12 message-text hidden">
				<span class="caption"><?php echo Core::_('Printlayout.message_text')?></span>
				<textarea name="text" class="form-control" rows="5" placeholder="<?php echo Core::_('Printlayout.message_text')?>"><?php echo htmlspecialchars($message_text)?></textarea>
			</div>
			<div class="form-group col-xs-12 message-button hidden">
				<?php
				$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
					->name('send')
					->type('submit')
					->class('applyButton btn btn-palegreen pull-right')
					->value(Core::_('Printlayout.send'))
					->onclick(
						'setTimeout(function() { bootbox.hideAll(); }, 500); '
						. $this->_Admin_Form_Controller->getAdminSendForm(array('action' => 'sendMail', 'operation' => 'send'))
					)
					->controller($this->_Admin_Form_Controller)
					->execute();
				?>
			</div>
			<?php
		}

		return ob_get_clean();
	}
}