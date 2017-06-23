<?php
/**
 * Way to Pay
 */
 class Shop_Payment_System_Handler21 extends Shop_Payment_System_Handler
{
	/**
	 * Идентификатор сервиса
	 * @var int
	 */
	private $_service_id = 1234;
	
	/**
	 * Секретный ключ
	 * @var string
	 */
	private $_secret_key = 'put your key here';
	
	/**
	 * Международное название валюты из списка валют магазина
	 * @var string
	 */
	protected $_currency_name = 'RUR';
	
	/**
	 * Идентификатор валюты
	 * @var string
	 */
	protected $_currency_id = 1;
	
	public function __construct(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		parent::__construct($oShop_Payment_System_Model);
		$oCurrency = Core_Entity::factory('Shop_Currency')->getByCode($this->_currency_name);
		!is_null($oCurrency) && $this->_currency_id = $oCurrency->id;
	}
	
	/**
	 * Метод, запускающий выполнение обработчика
	 * @return self
	 */
	public function execute()
	{
		parent::execute();

		$this->printNotification();

		return $this;
	}
	
	/*
	 * Обработка статуса оплаты
	 */
	function ProcessResult()
	{
		$wOutSum = Core_Array::getRequest('wOutSum');
		$wInvId = Core_Array::getRequest('wInvId');
		$wSignature = Core_Array::getRequest('wSignature');
		
		$our_signature = md5(sprintf("%s:%s:%s:%s:is_way_to_pay=1", $this->_service_id, $this->getSumWithCoeff(), $this->_shopOrder->id, $this->_secret_key));
		
		if($wSignature != '' && $wSignature == $our_signature)
		{
			if($this->_shopOrder->paid)
			{
				?><h1>Заказ № <?php echo $this->_shopOrder->id?> успешно оплачен!</h1><?php
			}
			else
			{
				?><h1>Заказ № <?php echo $this->_shopOrder->id?> не был оплачен!</h1><?php
			}
		}
		else
		{	
			?><h1>Ошибка обработки данных</h1><?php
		}
	}
	
	/**
	 * Метод отображающий формы для оплаты
	 */
	public function getNotification()
	{
		?>
        <script language='javascript' type='text/javascript' src='https://waytopay.org/api/interface/?MerchantId=<?php echo urlencode($this->_service_id)?>&OutSum=<?php echo urlencode($this->getSumWithCoeff())?>&InvId=<?php echo urlencode($this->_shopOrder->id)?>&InvDesc=<?php echo urlencode("Оплата заказа № ".$this->_shopOrder->id)?>&is_way_to_pay=1'></script>  
		<?php
	}
	
	public function getInvoice()
	{
		return $this->getNotification();
	}
	
	public function answerToPaymentSystem()
	{
		$wOutSum = Core_Array::getRequest('wOutSum');
		$wInvId = Core_Array::getRequest('wInvId');
		$wIsTest = Core_Array::getRequest('wIsTest');
		$wSignature = Core_Array::getRequest('wSignature');
		
		$our_signature = md5(sprintf("%s:%s:%s:%s:is_way_to_pay=1", $this->_service_id, $this->getSumWithCoeff(), $this->_shopOrder->id, $this->_secret_key));
		
		if($wSignature != '' 
			&& $wSignature == $our_signature 
			&& !$this->_shopOrder->paid
			&& !$wIsTest)
		{
			$this->_shopOrder->system_information = sprintf("Заказ оплачен через WaytoPay, данные заказа:\n№ заказа: %s\n", $wInvId);

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
			echo 'OK_'.$this->_shopOrder->id;
		}
		else
		{
			$this->_shopOrder->system_information = sprintf("Заказ НЕ оплачен через WaytoPay, данные заказа:\n\nДанные WtP:\nwOutSum=%s\nwInvId=%s\nwIsTest=%s\nwSignature=%s\n\nДанные магазина:\nСумма: %s\nНомер заказа: %s\nПодпись: %s\n", $wOutSum, $wInvId, $wIsTest ? "test mode" : "real mode", $wSignature, $this->getSumWithCoeff(), $this->_shopOrder->id, $our_signature);
			$this->_shopOrder->save();
			echo 'ERROR_wrong signature';
		}
		
		die();
	}
	
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_currency_id > 0
		&& $this->_shopOrder->shop_currency_id > 0
		? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
		$this->_shopOrder->Shop_Currency,
		Core_Entity::factory('Shop_Currency', $this->_currency_id)
		)
		: 0) * $this->_shopOrder->getAmount());
	}
	
	public function paymentProcessing()
	{
		// Пришло подтверждение оплаты, обработаем его
		if (isset($_REQUEST['is_way_to_pay']) && isset($_REQUEST['wIsTest']))
		{
			$this->answerToPaymentSystem();
			return TRUE;
		}
		elseif(isset($_REQUEST['is_way_to_pay']))
		{
			$this->ProcessResult();
			return TRUE;
		}
	}
}