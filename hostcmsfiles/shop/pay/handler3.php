<?php

/**
 * Безналичная оплата от юридического лица
 */
class Shop_Payment_System_Handler3 extends Shop_Payment_System_Handler
{
	/**
	 * Метод, запускающий выполнение обработчика
	 */
	function execute()
	{
		parent::execute();

		$this->printNotification();

		return $this;
	}

	protected function _processOrder()
	{
		parent::_processOrder();

		// Установка XSL-шаблонов в соответствии с настройками в узле структуры
		$this->setXSLs();

		// Отправка писем клиенту и пользователю
		$this->send();

		// Отправка клиенту письма со счетом.
		$this->sendInvoice();

		return $this;
	}

	/**
	 * Отправка клиенту письма со счетом.
	 */
	function sendInvoice()
	{
		$sInvoice = $this->getInvoice();
		$sInvoice = str_replace(">", ">\n", $sInvoice);

		$subject = 'Банковский счет';

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

	public function getInvoice()
	{
		$this->xsl(
			Core_Entity::factory('Xsl')->getByName('БанковскийСчет')
		);
		return parent::getInvoice();
	}

	public function getNotification()
	{
		$this->xsl(
			Core_Entity::factory('Xsl')->getByName('ОплатаБезналичнаяОтЮрЛица')
		);
		return parent::getNotification();
	}
}
