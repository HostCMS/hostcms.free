<?php
/**
 * 1PS.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'oneps');

$sAdminFormAction = '/admin/oneps/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('oneps.title'));

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('oneps.title'));

Core_Message::show(Core::_('oneps.introduction'));

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$oMainTab->add(Admin_Form_Entity::factory('Code')->html('
	<style>
		.oneps_patch p { margin: 5px 15px 5px 0; /*text-align: justify;*/ }
	</style>
'));

$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Code')->html('
		<div class="oneps_patch form-group col-xs-12 col-md-6">
			<h2><a href="http://1ps.ru/cost/regionseo/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16" target="_blank">Продвижение сайта в поисковиках</a></h2>
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-4">
					<img src="/modules/oneps/image/image03.png">
				</div>
				<div class="col-xs-12 col-md-8">
					<p>Комплексная услуга, включает в себя внутреннюю и внешнюю оптимизацию под поисковые системы Яндекс, Google и Mail.ru. Уже через 1-2 месяца после работ по оптимизации у сайта улучшатся позиции по 50-150 запросам в поисковых системах и трафик с поисковиков вырастет в среднем вдвое. Подробный отчет по всем этапам работ.</p>
				</div>
				<div class="col-xs-12 text-align-center margin-top-10">
					<input value="Заказать за 10 500 руб." class="applyButton btn btn-blue" onclick="window.open(\'http://1ps.ru/cost/regionseo/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16\'); return false" type="submit">
				</div>
			</div>
		</div>
	')
)->add(
	Admin_Form_Entity::factory('Code')->html('
		<div class="oneps_patch form-group col-xs-12 col-md-6">
			<h2><a href="http://1ps.ru/cost/crowd/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16" target="_blank">Крауд-маркетинг</a></h2>
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-4">
					<img src="/modules/oneps/image/image00.png">
				</div>
				<div class="col-xs-12 col-md-8">
					<p>Крауд-маркетинг это комплекс мероприятий направленных на поддержку репутации компании в Сети. Услуга направлена на повышение продаж, улучшение узнаваемость бренда и увеличение трафика на сайт. Вы получите не просто естественные ссылки, а лояльно настроенных клиентов. Работаем с форумами, блогами, сервисами вопрос-ответ, справочниками организаций и соцсетями.</p>
				</div>
				<div class="col-xs-12 text-align-center margin-top-10">
					<input value="Заказать за 11 000 руб." class="applyButton btn btn-blue" onclick="window.open(\'http://1ps.ru/cost/crowd/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16\'); return false" type="submit">
				</div>
			</div>
		</div>
	')
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Code')->html('
		<div class="oneps_patch form-group col-xs-12 col-md-6">
			<h2><a href="http://1ps.ru/cost/context/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16" target="_blank">Контекстная реклама</a></h2>
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-4">
					<img src="/modules/oneps/image/image01.png">
				</div>
				<div class="col-xs-12 col-md-8">
					<p>Разработка, настройка, запуск и сопровождение рекламы в Яндекс.Директ и Google Adwords. Гарантированное размещение рекламы вашего сайта на первых страницах поисковых систем Яндекса, Google, Mail.Ru и Рамблера. Оплата только за фактический переход клиента на сайт. Рекламные кампании ведут сертифицированные специалисты Яндекс.Директ и Google Adwords.</p>
				</div>
				<div class="col-xs-12 text-align-center margin-top-10">
					<input value="Заказать за 4 600 руб." class="applyButton btn btn-blue" onclick="window.open(\'http://1ps.ru/cost/context/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16\'); return false" type="submit">
				</div>
			</div>
		</div>
	')
)->add(
	Admin_Form_Entity::factory('Code')->html('
		<div class="oneps_patch form-group col-xs-12 col-md-6">
			<h2><a href="http://1ps.ru/cost/audit/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16" target="_blank">Веб-аналитика и аудит сайта</a></h2>
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-4">
					<img src="/modules/oneps/image/image02.png">
				</div>
				<div class="col-xs-12 col-md-8">
					<p>Юзабилити-аудит сайта поможет улучшить поведенческие факторы и увеличить конверсию сайта, SEO-аудит поможет выявить основные технические проблемы, мешающие продвижению.  В рамках услуги предоставляется подробный отчет со списком необходимых доработок сайта. После устранения ошибок у ресурса увеличится конверсия, количество заказов и посещаемость.</p>
				</div>
				<div class="col-xs-12 text-align-center margin-top-10">
					<input value="Заказать за 3 700 руб." class="applyButton btn btn-blue" onclick="window.open(\'http://1ps.ru/cost/audit/?p=610331&utm_source=hostcms&utm_medium=site&utm_campaign=promo_modul&utm_content=16\'); return false" type="submit">
				</div>
			</div>
		</div>
	')
));

Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add($oMainTab)
	->execute();

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('oneps.title'))
	->module($sModule)
	->execute();