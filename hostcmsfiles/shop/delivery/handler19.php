<?php
/**
 * Доставка PickPoint
 */
class Shop_Delivery_Handler19 extends Shop_Delivery_Handler
{
	/* Логин в системе PickPoint */
	protected $_login = 'apitest';

	/* Пароль в системе PickPoint */
	protected $_password = 'apitest';

	/* Номер договора(ИКН) в системе PickPoint */
	protected $_ikn = '9990003041';

	/* Местоположение магазина */
	protected $_from = 'Москва';

	/* Режим работы, 0 - Тестовый, 1 - Рабочий */
	protected $_mode = 0;

	/* Пункт выдачи (назначения) отправления */
	protected $_ptn = NULL;

	/* Название пунктп выдачи (назначения) отправления */
	protected $_ptn_name = NULL;

	/* Уникальный идентификатор сессии */
	protected $_sessionId = NULL;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Delivery_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (Core_Array::getPost('change_delivery_point') && strlen(Core_Array::getPost('point_number')))
		{
			ob_start();

			$this->_ptn = Core_Array::getPost('point_number', NULL);

			$this->_ptn_name = Core_Array::getPost('point_name', NULL);

			$sApiUrl = $this->getApiUrl();

			$this->_sessionId = $this->getSessionId();

			try {
				if (!is_null($this->_sessionId))
				{
					$aCalcTariffParams = array(
						'SessionId' => $this->_sessionId,
						'IKN' => $this->_ikn,
						'FromCity' => $this->_from,
						'FromRegion' => '',
						'PTNumber' => $this->_ptn,
						'Length' => '30',
						'Depth' => '30',
						'Width' => '30'
					);

					$calcTariffJson = json_encode($aCalcTariffParams);

					$Core_Http = Core_Http::instance('curl')
						->clear()
						->method('POST')
						->timeout(60)
						->url($sApiUrl . 'calctariff')
						->additionalHeader('Content-Type', 'application/json')
						->rawData($calcTariffJson)
						->execute();

					$oCalcTariff = json_decode($Core_Http->getBody());

					if ($oCalcTariff->ErrorCode)
					{
						throw new Exception("Ошибка: " . strval($oCalcTariff->ErrorMessage));
					}

					if (isset($oCalcTariff->Services))
					{
						?>
							<table>
								<thead>
									<tr>
										<th></th>
										<th>Название</th>
										<th>Цена</th>
										<th>НДС</th>
										<th>Срок поставки</th>
									</tr>
								</thead>
								<tbody>
						<?php
						$_SESSION['hostcmsOrder']['pickpoint'] = array();

						$oShop_Currency = $this->_Shop_Delivery_Model->Shop->Shop_Currency;

						foreach ($oCalcTariff->Services as $key => $oServiceTariff)
						{
							$oServiceTariff->point_name = $this->_ptn_name;
							$_SESSION['hostcmsOrder']['pickpoint'][$key] = $oServiceTariff;
							?>
								<tr>
									<td><input value="<?php echo $key?>" name="pickpoint_condition_id" type="radio" <?php if ($key == 0) echo 'checked="checked"'?>></td>
									<td><?php echo htmlspecialchars($oServiceTariff->Name), "\n", $this->_ptn_name?></td>
									<td><?php echo htmlspecialchars($oServiceTariff->Tariff), " ", htmlspecialchars($oShop_Currency->name)?></td>
									<td><?php echo htmlspecialchars($oServiceTariff->NDS), " ", htmlspecialchars($oShop_Currency->name)?></td>
									<td><?php echo htmlspecialchars($oCalcTariff->DPMin), " - ", htmlspecialchars($oCalcTariff->DPMax)?></td>
								</tr>
							<?php
						}
						?>
								</tbody>
							</table>
						<?php
					}
				}
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}

			$this->closeSession();

			Core::showJson(array('html' => ob_get_clean()));
		}
	}

	/*
	 * Адрес API
	 */
	public function getApiUrl()
	{
		return $this->_mode
			? 'https://e-solution.pickpoint.ru/api/'
			: 'https://e-solution.pickpoint.ru/apitest/';
	}

	public function closeSession()
	{
		$sApiUrl = $this->getApiUrl();

		try {
			$aSessionParams = array(
				'SessionId' => $this->_sessionId,
			);

			$sessionJson = json_encode($aSessionParams);

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('POST')
				->timeout(60)
				->url($sApiUrl . 'logout')
				->additionalHeader('Content-Type', 'application/json')
				->rawData($sessionJson)
				->execute();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/*
	 * Получение уникального идентификатора сессии
	 */
	public function getSessionId()
	{
		$sApiUrl = $this->getApiUrl();

		try {
			$aSessionParams = array(
				'Login' => $this->_login,
				'Password' => $this->_password
			);

			$sessionJson = json_encode($aSessionParams);

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('POST')
				->timeout(60)
				->url($sApiUrl . 'login')
				->additionalHeader('Content-Type', 'application/json')
				->rawData($sessionJson)
				->execute();

			$oSession = json_decode($Core_Http->getBody());

			if (!is_null($oSession->ErrorMessage))
			{
				throw new Exception("Ошибка: " . strval($oSession->ErrorMessage));
			}

			return isset($oSession->SessionId)
				? $oSession->SessionId
				: NULL;
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/*
	 * Execute business logic
	 */
	public function execute()
	{
		$oReturn = new StdClass;
		$oReturn->price = '';

		$oReturn->description = <<<EOF

		<script type="text/javascript" src="https://pickpoint.ru/select/postamat.js"></script>
		<script type="text/javascript">
			function hostcmsPickpoint(result){
				jQuery.loadingScreen('show');

				$.ajax({
					url: '/shop/cart/',
					type: 'POST',
					data: {'change_delivery_point':1, 'point_number':result.id, 'point_name':result.name},
					dataType: 'json',
					success: function(answer){
						$('div.pickpoint-answer').html(answer.html);

						jQuery.loadingScreen('hide');
					}
				});
			}
		</script>

		<a href="#" onclick="PickPoint.open(hostcmsPickpoint);return false">Выбрать на карте</a>

		<div class="pickpoint-answer"></div>
EOF;

		return $oReturn;
	}

	public function process($position)
	{
		$pickpoint_condition_id = Core_Array::getPost('pickpoint_condition_id');

		if (isset($_SESSION['hostcmsOrder']['pickpoint'][$pickpoint_condition_id]))
		{
			$oTmp = $_SESSION['hostcmsOrder']['pickpoint'][$pickpoint_condition_id];

			$tax = $oTmp->NDS > 0 && $oTmp->Tariff > 0
				? round($oTmp->NDS * 100 / $oTmp->Tariff)
				: 0;

			$_SESSION['hostcmsOrder']['shop_delivery_id'] = $this->_Shop_Delivery_Model->id;
			$_SESSION['hostcmsOrder']['shop_delivery_price'] = $oTmp->Tariff;
			$_SESSION['hostcmsOrder']['shop_delivery_rate'] = $tax;
			$_SESSION['hostcmsOrder']['shop_delivery_name'] = $oTmp->Name . "\n" . $oTmp->point_name;
		}
	}
}