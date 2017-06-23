<?php

/**
 * Оплата наличными
 */
class Shop_Payment_System_Handler1 extends Shop_Payment_System_Handler
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

		return $this;
	}

	public function getNotification()
	{
		$this->xsl(
			Core_Entity::factory('Xsl')->getByName('ОплатаПриПолучении')
		);
		return parent::getNotification();
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}