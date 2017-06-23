<?php

/**
 * Почтовый перевод
 */
class Shop_Payment_System_Handler22 extends Shop_Payment_System_Handler
{
	// Чекбокс "Выплатить наличными деньгами"? 0 - нет, 1 - да
	private $_payWithCash = 1;
	function execute()
	{
		parent::execute();
		$this->printNotification();
		return $this;
	}
	protected function _processOrder()
	{
		parent::_processOrder();
		$this->setXSLs();
		$this->send();
		$this->sendInvoice();
		return $this;
	}
	function sendInvoice()
	{
		$sInvoice = $this->getInvoice();
		$sInvoice = str_replace(">", ">\n", $sInvoice);
		$subject = 'Квитанция почтового перевода';
		Core_Mail::instance()
			->to($this->_shopOrder->email)
			->from($this->_shopOrder->Shop->getFirstEmail())
			->subject($subject)
			->message($sInvoice)
			->contentType('text/html')
			->header('X-HostCMS-Reason', 'OrderInvoice')
			->header('Precedence', 'bulk')
			->send();
		return $this;
	}
	protected function _processXml()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeProcessXml', $this);
		$sXml = $this->_prepareXml()->getXml();
		$return = Xsl_Processor::instance()
			->xml($sXml)
			->xsl($this->_xsl)
			->process();
		$this->_shopOrder->clearEntities();
		Core_Event::notify('Shop_Payment_System_Handler.onAfterProcessXml', $this);
		return $return;
	}
	protected function _prepareXml()
	{
		$oShop = parent::_prepareXml();

		$oShop
			->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('paywithcash')
					->value($this->_payWithCash ? 'v' : '')
			)->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('numprop')
					->value(Core_Str::ucfirst(Core_Inflection::num2str($this->_shopOrder->getAmount(), 'ru')))
			);

		return $oShop;
	}
	public function getInvoice()
	{
		$this->xsl(Core_Entity::factory('Xsl')->getByName('Форма112ф'));
		return parent::getInvoice();
	}
	public function getNotification()
	{
		$this->xsl(Core_Entity::factory('Xsl')->getByName('ОплатаПочтовымПереводом'));
		return parent::getNotification();
	}
}