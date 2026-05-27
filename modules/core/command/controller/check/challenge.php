<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Command_Controller_Check_Challenge extends Core_Command_Controller
{
	/**
	 * Cookie max age
	 * @var int
	 */
	static public $maxAge = 1209600; // 14 days
	
	/**
	 * Attempt
	 * @var int
	 */
	static public $attempt = 1;
	
	/**
	 * Генерирует уникальную серверную соль (Nonce) для конкретного IP и User-Agent
	 * Меняется каждый час
	 * @param int $offsetPeriods
	 * @return string
	 */
	static public function getChallengeNonce($offsetPeriods = 0)
	{
		$ip = Core::getClientIp();
		
		Core_Valid::ipv4($ip)
			&& $ip = Core_Ip::ipv4Network($ip, '255.255.255.0');
		
		$clientContext = $ip . Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');
		
		//$timeWindow = date('Y-m-d-H', time() + ($offsetPeriods * 3600));
		
		// Длительность периода в секундах
		$currentPeriod = floor(time() / self::$maxAge);
		$periodNumber = $currentPeriod + $offsetPeriods;
		
		return hash('sha256', CURRENT_SITE . '-' . CMS_FOLDER . '-' . $clientContext . '-' . $periodNumber);
	}

	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Check_Challenge.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Check_Challenge.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);

		$oCore_Response
			->header('Content-Type', "text/html; charset=UTF-8")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
			->header('Pragma', 'no-cache')
			->header('Retry-After', self::$maxAge)
			->status(503);

		/*
		"<?php echo $i18n['status_cookie']?>",  // [0]
		"<?php echo $i18n['status_bot']?>",     // [1]
		"<?php echo $i18n['status_js']?>",      // [2]
		"<?php echo $i18n['status_collect']?>", // [3]
		*/

		//'status_cookie'  => 'Проверка поддержки Cookie...',
		//'status_bot'     => 'Анализ паттернов автоматизации...',
		//'status_js'      => 'Проверка окружения...',
		//'status_collect' => 'Сбор телеметрии...',

		$lng = strtolower(substr(Core_Array::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '', 'str'), 0, 2));
		switch ($lng)
		{
			case 'en':
			default:
				$i18n = array(
					'title'                    => 'Security Check',
					'javascript_disabled'      => 'JavaScript is disabled',
					'javascript_disabled_desc' => 'To perform a security check and access the site, JavaScript must be enabled.',
					'initialization'           => '👋 We are checking your browser to protect the site from attacks. For a quick check, just move your cursor 🖱️ or, if you are on a phone, swipe your finger across the screen 👆.',
					'status_done'              => 'Redirecting ...',
					'err_cookie_title'         => 'Error: Cookies are disabled or blocked.',
					'err_cookie_desc'          => 'Please enable cookies in your browser settings and refresh the page.',
					'err_bot_title'            => 'Error: Automated request detected.',
					'err_bot_desc'             => 'Please use a regular browser.'
				);
			break;

			case 'ru':
				$i18n = array(
					'title'          => 'Проверка безопасности',
					'javascript_disabled' => 'JavaScript отключён',
					'javascript_disabled_desc' => 'Для проверки безопасности и доступа к сайту необходимо включить JavaScript.',
					'initialization'    => '👋 Мы проверяем ваш браузер, чтобы защитить сайт от атак. Для быстрой проверки просто пошевелите курсором 🖱️ или, если вы с телефона, сдвиньте палец по экрану 👆.',
					'status_done'    => 'Перенаправление ...',
					'err_cookie_title' => 'Ошибка: Cookie отключены или заблокированы.',
					'err_cookie_desc'  => 'Пожалуйста, включите поддержку Cookie в настройках браузера и обновите страницу.',
					'err_bot_title'    => 'Ошибка: Обнаружен автоматизированный запрос.',
					'err_bot_desc'     => 'Пожалуйста, используйте обычный браузер.'
				);
			break;

			case 'kk':
				$i18n = array(
					'title'                    => 'Қауіпсіздікті тексеру',
					'javascript_disabled'      => 'JavaScript өшірілген',
					'javascript_disabled_desc' => 'Қауіпсіздікті тексеру және сайтқа кіру үшін JavaScript қосу қажет.',
					'initialization'           => '👋 Біз сіздің браузеріңізді шабуылдардан қорғау үшін тексереміз. Жылдам тексеру үшін курсорды жылжытыңыз 🖱️ немесе телефоннан саусағыңызды экран бойымен сырғытыңыз 👆.',
					'status_done'              => 'Қайта бағыттау ...',
					'err_cookie_title'         => 'Қате: Cookie өшірілген немесе бұғатталған.',
					'err_cookie_desc'          => 'Браузер параметрлерінде cookie қолдауды қосыңыз және бетті жаңартыңыз.',
					'err_bot_title'            => 'Қате: Автоматтандырылған сұрау анықталды.',
					'err_bot_desc'             => 'Кәдімгі браузерді пайдаланыңыз.'
				);
			break;

			case 'de':
				$i18n = array(
					'title'                    => 'Sicherheitsüberprüfung',
					'javascript_disabled'      => 'JavaScript ist deaktiviert',
					'javascript_disabled_desc' => 'Für die Sicherheitsüberprüfung und den Zugriff auf die Seite muss JavaScript aktiviert sein.',
					'initialization'           => '👋 Wir überprüfen Ihren Browser, um die Website vor Angriffen zu schützen. Für eine schnelle Überprüfung bewegen Sie einfach den Cursor 🖱️ oder wischen Sie mit dem Finger über den Bildschirm 👆.',
					'status_done'              => 'Weiterleitung ...',
					'err_cookie_title'         => 'Fehler: Cookies sind deaktiviert oder blockiert.',
					'err_cookie_desc'          => 'Bitte aktivieren Sie Cookies in den Browsereinstellungen und laden Sie die Seite neu.',
					'err_bot_title'            => 'Fehler: Automatisierte Anfrage erkannt.',
					'err_bot_desc'             => 'Bitte verwenden Sie einen normalen Browser.'
				);
			break;

			case 'nl':
				$i18n = array(
					'title'                    => 'Veiligheidscontrole',
					'javascript_disabled'      => 'JavaScript is uitgeschakeld',
					'javascript_disabled_desc' => 'Voor de veiligheidscontrole en toegang tot de site moet JavaScript zijn ingeschakeld.',
					'initialization'           => '👋 We controleren uw browser om de site tegen aanvallen te beschermen. Voor een snelle controle beweegt u gewoon de cursor 🖱️ of veegt u met uw vinger over het scherm 👆.',
					'status_done'              => 'Doorverwijzen ...',
					'err_cookie_title'         => 'Fout: Cookies zijn uitgeschakeld of geblokkeerd.',
					'err_cookie_desc'          => 'Schakel cookies in uw browserinstellingen in en vernieuw de pagina.',
					'err_bot_title'            => 'Fout: Geautomatiseerd verzoek gedetecteerd.',
					'err_bot_desc'             => 'Gebruik een normale browser.'
				);
			break;

			case 'fr':
				$i18n = array(
					'title'                    => 'Contrôle de sécurité',
					'javascript_disabled'      => 'JavaScript est désactivé',
					'javascript_disabled_desc' => 'Pour effectuer le contrôle de sécurité et accéder au site, JavaScript doit être activé.',
					'initialization'           => '👋 Nous vérifions votre navigateur pour protéger le site contre les attaques. Pour une vérification rapide, déplacez simplement le curseur 🖱️ ou faites glisser votre doigt sur l\'écran 👆.',
					'status_done'              => 'Redirection ...',
					'err_cookie_title'         => 'Erreur : Cookies désactivés ou bloqués.',
					'err_cookie_desc'          => 'Veuillez activer les cookies dans les paramètres de votre navigateur et actualiser la page.',
					'err_bot_title'            => 'Erreur : Requête automatisée détectée.',
					'err_bot_desc'             => 'Veuillez utiliser un navigateur standard.'
				);
			break;

			case 'es':
				$i18n = array(
					'title'                    => 'Verificación de seguridad',
					'javascript_disabled'      => 'JavaScript está desactivado',
					'javascript_disabled_desc' => 'Para realizar la verificación de seguridad y acceder al sitio, JavaScript debe estar activado.',
					'initialization'           => '👋 Estamos comprobando su navegador para proteger el sitio de ataques. Para una comprobación rápida, mueva el cursor 🖱️ o deslice el dedo por la pantalla 👆.',
					'status_done'              => 'Redirigiendo ...',
					'err_cookie_title'         => 'Error: Cookies desactivadas o bloqueadas.',
					'err_cookie_desc'          => 'Habilite las cookies en la configuración de su navegador y actualice la página.',
					'err_bot_title'            => 'Error: Solicitud automatizada detectada.',
					'err_bot_desc'             => 'Utilice un navegador convencional.'
				);
			break;

			case 'ca':
				$i18n = array(
					'title'                    => 'Verificació de seguretat',
					'javascript_disabled'      => 'JavaScript està desactivat',
					'javascript_disabled_desc' => 'Per realitzar la verificació de seguretat i accedir al lloc, JavaScript ha d\'estar activat.',
					'initialization'           => '👋 Estem comprovant el vostre navegador per protegir el lloc d\'atacs. Per a una comprovació ràpida, moveu el cursor 🖱️ o llisqueu el dit per la pantalla 👆.',
					'status_done'              => 'Redirigint ...',
					'err_cookie_title'         => 'Error: Galetes desactivades o bloquejades.',
					'err_cookie_desc'          => 'Activeu les galetes a la configuració del navegador i actualitzeu la pàgina.',
					'err_bot_title'            => 'Error: Sol·licitud automatitzada detectada.',
					'err_bot_desc'             => 'Utilitzeu un navegador normal.'
				);
			break;

			case 'it':
				$i18n = array(
					'title'                    => 'Verifica di sicurezza',
					'javascript_disabled'      => 'JavaScript disattivato',
					'javascript_disabled_desc' => 'Per eseguire la verifica di sicurezza e accedere al sito, JavaScript deve essere abilitato.',
					'initialization'           => '👋 Stiamo verificando il tuo browser per proteggere il sito dagli attacchi. Per una verifica rapida, muovi il cursore 🖱️ o fai scorrere il dito sullo schermo 👆.',
					'status_done'              => 'Reindirizzamento ...',
					'err_cookie_title'         => 'Errore: Cookie disattivati o bloccati.',
					'err_cookie_desc'          => 'Abilita i cookie nelle impostazioni del browser e aggiorna la pagina.',
					'err_bot_title'            => 'Errore: Richiesta automatizzata rilevata.',
					'err_bot_desc'             => 'Utilizza un browser normale.'
				);
			break;

			case 'pt':
				$i18n = array(
					'title'                    => 'Verificação de segurança',
					'javascript_disabled'      => 'JavaScript desativado',
					'javascript_disabled_desc' => 'Para realizar a verificação de segurança e aceder ao site, o JavaScript tem de estar ativado.',
					'initialization'           => '👋 Estamos a verificar o seu navegador para proteger o site de ataques. Para uma verificação rápida, mova o cursor 🖱️ ou deslize o dedo pelo ecrã 👆.',
					'status_done'              => 'Redirecionamento ...',
					'err_cookie_title'         => 'Erro: Cookies desativados ou bloqueados.',
					'err_cookie_desc'          => 'Ative os cookies nas definições do navegador e atualize a página.',
					'err_bot_title'            => 'Erro: Pedido automatizado detetado.',
					'err_bot_desc'             => 'Utilize um navegador normal.'
				);
			break;

			case 'no':
				$i18n = array(
					'title'                    => 'Sikkerhetssjekk',
					'javascript_disabled'      => 'JavaScript er deaktivert',
					'javascript_disabled_desc' => 'For å utføre sikkerhetssjekken og få tilgang til nettstedet må JavaScript være aktivert.',
					'initialization'           => '👋 Vi sjekker nettleseren din for å beskytte nettstedet mot angrep. For en rask sjekk, beveg markøren 🖱️ eller sveip fingeren over skjermen 👆.',
					'status_done'              => 'Videresender ...',
					'err_cookie_title'         => 'Feil: Informasjonskapsler er deaktivert eller blokkert.',
					'err_cookie_desc'          => 'Aktiver informasjonskapsler i nettleserinnstillingene og oppdater siden.',
					'err_bot_title'            => 'Feil: Automatisert forespørsel oppdaget.',
					'err_bot_desc'             => 'Bruk en vanlig nettleser.'
				);
			break;

			case 'fi':
				$i18n = array(
					'title'                    => 'Turvatarkistus',
					'javascript_disabled'      => 'JavaScript on poistettu käytöstä',
					'javascript_disabled_desc' => 'Turvatarkistuksen suorittaminen ja sivustolle pääsy edellyttää JavaScriptin käyttöönottoa.',
					'initialization'           => '👋 Tarkistamme selaimesi suojataksemme sivuston hyökkäyksiltä. Nopeaa tarkistusta varten liikuta hiirtä 🖱️ tai pyyhkäise sormella näytöllä 👆.',
					'status_done'              => 'Uudelleenohjaus ...',
					'err_cookie_title'         => 'Virhe: Evästeet on poistettu käytöstä tai estetty.',
					'err_cookie_desc'          => 'Ota evästeet käyttöön selaimen asetuksissa ja päivitä sivu.',
					'err_bot_title'            => 'Virhe: Automaattinen pyyntö havaittu.',
					'err_bot_desc'             => 'Käytä tavallista selainta.'
				);
			break;

			case 'sv':
				$i18n = array(
					'title'                    => 'Säkerhetskontroll',
					'javascript_disabled'      => 'JavaScript är inaktiverat',
					'javascript_disabled_desc' => 'För att utföra säkerhetskontrollen och komma åt webbplatsen måste JavaScript vara aktiverat.',
					'initialization'           => '👋 Vi kontrollerar din webbläsare för att skydda webbplatsen mot attacker. För en snabb kontroll, rör bara på markören 🖱️ eller svep med fingret över skärmen 👆.',
					'status_done'              => 'Omdirigerar ...',
					'err_cookie_title'         => 'Fel: Cookies är inaktiverade eller blockerade.',
					'err_cookie_desc'          => 'Aktivera cookies i webbläsarinställningarna och uppdatera sidan.',
					'err_bot_title'            => 'Fel: Automatiserad begäran upptäckt.',
					'err_bot_desc'             => 'Använd en vanlig webbläsare.'
				);
			break;

			case 'da':
				$i18n = array(
					'title'                    => 'Sikkerhedstjek',
					'javascript_disabled'      => 'JavaScript er deaktiveret',
					'javascript_disabled_desc' => 'For at udføre sikkerhedstjekket og få adgang til webstedet skal JavaScript være aktiveret.',
					'initialization'           => '👋 Vi tjekker din browser for at beskytte siden mod angreb. For en hurtig kontrol skal du blot bevæge markøren 🖱️ eller swipe med fingeren på skærmen 👆.',
					'status_done'              => 'Videresender ...',
					'err_cookie_title'         => 'Fejl: Cookies er deaktiveret eller blokeret.',
					'err_cookie_desc'          => 'Aktivér cookies i browserindstillingerne, og opdater siden.',
					'err_bot_title'            => 'Fejl: Automatiseret anmodning registreret.',
					'err_bot_desc'             => 'Brug en almindelig browser.'
				);
			break;

			case 'cs':
				$i18n = array(
					'title'                    => 'Bezpečnostní kontrola',
					'javascript_disabled'      => 'JavaScript je zakázán',
					'javascript_disabled_desc' => 'Pro provedení bezpečnostní kontroly a přístup na web musí být JavaScript povolen.',
					'initialization'           => '👋 Kontrolujeme váš prohlížeč, abychom ochránili web před útoky. Pro rychlou kontrolu stačí pohnout kurzorem 🖱️ nebo přejet prstem po obrazovce 👆.',
					'status_done'              => 'Přesměrování ...',
					'err_cookie_title'         => 'Chyba: Cookies jsou zakázány nebo blokovány.',
					'err_cookie_desc'          => 'Povolte cookies v nastavení prohlížeče a obnovte stránku.',
					'err_bot_title'            => 'Chyba: Detekován automatizovaný požadavek.',
					'err_bot_desc'             => 'Použijte běžný prohlížeč.'
				);
			break;

			case 'hu':
				$i18n = array(
					'title'                    => 'Biztonsági ellenőrzés',
					'javascript_disabled'      => 'A JavaScript le van tiltva',
					'javascript_disabled_desc' => 'A biztonsági ellenőrzés elvégzéséhez és az oldal eléréséhez engedélyezni kell a JavaScriptet.',
					'initialization'           => '👋 Ellenőrizzük a böngészőjét, hogy megvédjük az oldalt a támadásoktól. A gyors ellenőrzéshez mozgassa a kurzort 🖱️ vagy húzza végig az ujját a képernyőn 👆.',
					'status_done'              => 'Átirányítás ...',
					'err_cookie_title'         => 'Hiba: A sütik le vannak tiltva vagy blokkolva vannak.',
					'err_cookie_desc'          => 'Engedélyezze a sütiket a böngésző beállításaiban, és frissítse az oldalt.',
					'err_bot_title'            => 'Hiba: Automatizált kérés észlelve.',
					'err_bot_desc'             => 'Kérjük, használjon hagyományos böngészőt.'
				);
			break;

			case 'ro':
				$i18n = array(
					'title'                    => 'Verificare de securitate',
					'javascript_disabled'      => 'JavaScript este dezactivat',
					'javascript_disabled_desc' => 'Pentru a efectua verificarea de securitate și a accesa site-ul, JavaScript trebuie să fie activat.',
					'initialization'           => '👋 Vă verificăm browserul pentru a proteja site-ul de atacuri. Pentru o verificare rapidă, mișcați cursorul 🖱️ sau glisați degetul pe ecran 👆.',
					'status_done'              => 'Redirecționare ...',
					'err_cookie_title'         => 'Eroare: Cookie-urile sunt dezactivate sau blocate.',
					'err_cookie_desc'          => 'Activați cookie-urile în setările browserului și reîmprospătați pagina.',
					'err_bot_title'            => 'Eroare: Solicitare automatizată detectată.',
					'err_bot_desc'             => 'Vă rugăm să folosiți un browser obișnuit.'
				);
			break;

			case 'ja':
				$i18n = array(
					'title'                    => 'セキュリティチェック',
					'javascript_disabled'      => 'JavaScriptが無効です',
					'javascript_disabled_desc' => 'セキュリティチェックを実行しサイトにアクセスするには、JavaScriptを有効にする必要があります。',
					'initialization'           => '👋 サイトを攻撃から保護するため、ブラウザを確認しています。簡単な確認のため、カーソルを動かす 🖱️ か、電話の場合は画面を指でスワイプしてください 👆。',
					'status_done'              => 'リダイレクト中 ...',
					'err_cookie_title'         => 'エラー：Cookieが無効またはブロックされています。',
					'err_cookie_desc'          => 'ブラウザの設定でCookieを有効にしてページを更新してください。',
					'err_bot_title'            => 'エラー：自動化されたリクエストが検出されました。',
					'err_bot_desc'             => '通常のブラウザを使用してください。'
				);
			break;

			case 'zh':
				$i18n = array(
					'title'                    => '安全检查',
					'javascript_disabled'      => 'JavaScript 已禁用',
					'javascript_disabled_desc' => '为了进行安全检查并访问网站，必须启用 JavaScript。',
					'initialization'           => '👋 我们正在检查您的浏览器以保护网站免受攻击。如需快速检查，请移动光标 🖱️ 或（如果您使用手机）在屏幕上滑动手指 👆。',
					'status_done'              => '跳转中 ...',
					'err_cookie_title'         => '错误：Cookie 已禁用或被阻止。',
					'err_cookie_desc'          => '请在浏览器设置中启用 Cookie 并刷新页面。',
					'err_bot_title'            => '错误：检测到自动化请求。',
					'err_bot_desc'             => '请使用常规浏览器。'
				);
			break;

			case 'pl':
				$i18n = array(
					'title'                    => 'Weryfikacja bezpieczeństwa',
					'javascript_disabled'      => 'JavaScript jest wyłączony',
					'javascript_disabled_desc' => 'Aby przeprowadzić weryfikację bezpieczeństwa i uzyskać dostęp do strony, JavaScript musi być włączony.',
					'initialization'           => '👋 Sprawdzamy Twoją przeglądarkę, aby chronić witrynę przed atakami. W celu szybkiego sprawdzenia porusz kursorem 🖱️ lub przesuń palcem po ekranie 👆.',
					'status_done'              => 'Przekierowanie ...',
					'err_cookie_title'         => 'Błąd: Pliki cookie są wyłączone lub zablokowane.',
					'err_cookie_desc'          => 'Włącz obsługę plików cookie w ustawieniach przeglądarki i odśwież stronę.',
					'err_bot_title'            => 'Błąd: Wykryto zautomatyzowane żądanie.',
					'err_bot_desc'             => 'Prosimy użyć zwykłej przeglądarki.'
				);
			break;

			case 'el':
				$i18n = array(
					'title'                    => 'Έλεγχος ασφαλείας',
					'javascript_disabled'      => 'Η JavaScript είναι απενεργοποιημένη',
					'javascript_disabled_desc' => 'Για να πραγματοποιηθεί ο έλεγχος ασφαλείας και να αποκτήσετε πρόσβαση στον ιστότοπο, πρέπει να ενεργοποιηθεί η JavaScript.',
					'initialization'           => '👋 Ελέγχουμε το πρόγραμμα περιήγησής σας για να προστατεύσουμε τον ιστότοπο από επιθέσεις. Για γρήγορο έλεγχο, απλώς μετακινήστε τον κέρσορα 🖱️ ή σύρετε το δάχτυλό σας στην οθόνη 👆.',
					'status_done'              => 'Ανακατεύθυνση ...',
					'err_cookie_title'         => 'Σφάλμα: Τα cookies είναι απενεργοποιημένα ή αποκλεισμένα.',
					'err_cookie_desc'          => 'Ενεργοποιήστε τα cookies στις ρυθμίσεις του προγράμματος περιήγησης και ανανεώστε τη σελίδα.',
					'err_bot_title'            => 'Σφάλμα: Εντοπίστηκε αυτοματοποιημένο αίτημα.',
					'err_bot_desc'             => 'Παρακαλούμε χρησιμοποιήστε ένα κανονικό πρόγραμμα περιήγησης.'
				);
			break;

			case 'tr':
				$i18n = array(
					'title'                    => 'Güvenlik Kontrolü',
					'javascript_disabled'      => 'JavaScript devre dışı',
					'javascript_disabled_desc' => 'Güvenlik kontrolünü gerçekleştirmek ve siteye erişmek için JavaScript etkinleştirilmelidir.',
					'initialization'           => '👋 Siteyi saldırılara karşı korumak için tarayıcınızı kontrol ediyoruz. Hızlı bir kontrol için imleci hareket ettirin 🖱️ veya telefondaysanız parmağınızı ekranda kaydırın 👆.',
					'status_done'              => 'Yönlendiriliyor ...',
					'err_cookie_title'         => 'Hata: Çerezler devre dışı veya engellenmiş.',
					'err_cookie_desc'          => 'Lütfen tarayıcı ayarlarından çerezleri etkinleştirin ve sayfayı yenileyin.',
					'err_bot_title'            => 'Hata: Otomatik istek algılandı.',
					'err_bot_desc'             => 'Lütfen normal bir tarayıcı kullanın.'
				);
			break;

			case 'bg':
				$i18n = array(
					'title'                    => 'Проверка за сигурност',
					'javascript_disabled'      => 'JavaScript е изключен',
					'javascript_disabled_desc' => 'За да се извърши проверка за сигурност и достъп до сайта, трябва да е активиран JavaScript.',
					'initialization'           => '👋 Проверяваме вашия браузър, за да защитим сайта от атаки. За бърза проверка просто преместете курсора 🖱️ или плъзнете пръст по екрана 👆.',
					'status_done'              => 'Пренасочване ...',
					'err_cookie_title'         => 'Грешка: Бисквитките са изключени или блокирани.',
					'err_cookie_desc'          => 'Моля, активирайте бисквитките в настройките на браузъра и опреснете страницата.',
					'err_bot_title'            => 'Грешка: Засечена е автоматизирана заявка.',
					'err_bot_desc'             => 'Моля, използвайте обикновен браузър.'
				);
			break;

			case 'ar':
				$i18n = array(
					'title'                    => 'التحقق الأمني',
					'javascript_disabled'      => 'JavaScript معطل',
					'javascript_disabled_desc' => 'لإجراء التحقق الأمني والوصول إلى الموقع، يجب تمكين JavaScript.',
					'initialization'           => '👋 نحن نتحقق من متصفحك لحماية الموقع من الهجمات. لإجراء فحص سريع، ما عليك سوى تحريك المؤشر 🖱️ أو تمرير إصبعك على الشاشة 👆.',
					'status_done'              => 'جارٍ إعادة التوجيه ...',
					'err_cookie_title'         => 'خطأ: ملفات تعريف الارتباط معطلة أو محظورة.',
					'err_cookie_desc'          => 'يرجى تمكين ملفات تعريف الارتباط في إعدادات المتصفح وتحديث الصفحة.',
					'err_bot_title'            => 'خطأ: تم اكتشاف طلب آلي.',
					'err_bot_desc'             => 'يرجى استخدام متصفح عادي.'
				);
			break;

			case 'ko':
				$i18n = array(
					'title'                    => '보안 확인',
					'javascript_disabled'      => 'JavaScript가 비활성화됨',
					'javascript_disabled_desc' => '보안 확인을 수행하고 사이트에 접근하려면 JavaScript를 활성화해야 합니다.',
					'initialization'           => '👋 공격으로부터 사이트를 보호하기 위해 브라우저를 확인하고 있습니다. 빠른 확인을 위해 커서를 움직이거나 🖱️ 휴대폰의 경우 화면을 손가락으로 스와이프하세요 👆.',
					'status_done'              => '리디렉션 중 ...',
					'err_cookie_title'         => '오류: 쿠키가 비활성화되었거나 차단되었습니다.',
					'err_cookie_desc'          => '브라우저 설정에서 쿠키를 활성화하고 페이지를 새로 고침하십시오.',
					'err_bot_title'            => '오류: 자동화된 요청이 감지되었습니다.',
					'err_bot_desc'             => '일반 브라우저를 사용하십시오.'
				);
			break;

			case 'he':
				$i18n = array(
					'title'                    => 'בדיקת אבטחה',
					'javascript_disabled'      => 'JavaScript מושבת',
					'javascript_disabled_desc' => 'כדי לבצע את בדיקת האבטחה ולגשת לאתר, יש להפעיל JavaScript.',
					'initialization'           => '👋 אנו בודקים את הדפדפן שלך כדי להגן על האתר מפני התקפות. לבדיקה מהירה, פשוט הזז את הסמן 🖱️ או החלק אצבע על המסך 👆.',
					'status_done'              => 'מפנה ...',
					'err_cookie_title'         => 'שגיאה: עוגיות מושבתות או חסומות.',
					'err_cookie_desc'          => 'אנא הפעל עוגיות בהגדרות הדפדפן ורענן את הדף.',
					'err_bot_title'            => 'שגיאה: זוהתה בקשה אוטומטית.',
					'err_bot_desc'             => 'אנא השתמש בדפדפן רגיל.'
				);
			break;

			case 'lv':
				$i18n = array(
					'title'                    => 'Drošības pārbaude',
					'javascript_disabled'      => 'JavaScript ir atspējots',
					'javascript_disabled_desc' => 'Lai veiktu drošības pārbaudi un piekļūtu vietnei, ir jāiespējo JavaScript.',
					'initialization'           => '👋 Mēs pārbaudām jūsu pārlūkprogrammu, lai aizsargātu vietni no uzbrukumiem. Ātrai pārbaudei vienkārši pakustiniet kursoru 🖱️ vai pavelciet ar pirkstu pa ekrānu 👆.',
					'status_done'              => 'Novirzīšana ...',
					'err_cookie_title'         => 'Kļūda: Sīkfaili ir atspējoti vai bloķēti.',
					'err_cookie_desc'          => 'Lūdzu, iespējojiet sīkfailus pārlūka iestatījumos un atsvaidziniet lapu.',
					'err_bot_title'            => 'Kļūda: Atklāts automatizēts pieprasījums.',
					'err_bot_desc'             => 'Lūdzu, izmantojiet parastu pārlūku.'
				);
			break;

			case 'uk':
				$i18n = array(
					'title'                    => 'Перевірка безпеки',
					'javascript_disabled'      => 'JavaScript вимкнено',
					'javascript_disabled_desc' => 'Для виконання перевірки безпеки та доступу до сайту необхідно увімкнути JavaScript.',
					'initialization'           => '👋 Ми перевіряємо ваш браузер, щоб захистити сайт від атак. Для швидкої перевірки просто поворушіть курсором 🖱️ або проведіть пальцем по екрану 👆.',
					'status_done'              => 'Перенаправлення ...',
					'err_cookie_title'         => 'Помилка: Cookie вимкнено або заблоковано.',
					'err_cookie_desc'          => 'Будь ласка, увімкніть підтримку Cookie в налаштуваннях браузера та оновіть сторінку.',
					'err_bot_title'            => 'Помилка: Виявлено автоматизований запит.',
					'err_bot_desc'             => 'Будь ласка, використовуйте звичайний браузер.'
				);
			break;

			case 'id':
				$i18n = array(
					'title'                    => 'Pemeriksaan Keamanan',
					'javascript_disabled'      => 'JavaScript dinonaktifkan',
					'javascript_disabled_desc' => 'Untuk melakukan pemeriksaan keamanan dan mengakses situs, JavaScript harus diaktifkan.',
					'initialization'           => '👋 Kami sedang memeriksa browser Anda untuk melindungi situs dari serangan. Untuk pemeriksaan cepat, cukup gerakkan kursor 🖱️ atau geser jari Anda di layar 👆.',
					'status_done'              => 'Mengalihkan ...',
					'err_cookie_title'         => 'Kesalahan: Cookie dinonaktifkan atau diblokir.',
					'err_cookie_desc'          => 'Aktifkan cookie di pengaturan peramban dan segarkan halaman.',
					'err_bot_title'            => 'Kesalahan: Permintaan otomatis terdeteksi.',
					'err_bot_desc'             => 'Silakan gunakan peramban biasa.'
				);
			break;

			case 'ms':
				$i18n = array(
					'title'                    => 'Pemeriksaan Keselamatan',
					'javascript_disabled'      => 'JavaScript dilumpuhkan',
					'javascript_disabled_desc' => 'Untuk melakukan pemeriksaan keselamatan dan mengakses laman, JavaScript mesti diaktifkan.',
					'initialization'           => '👋 Kami sedang menyemak pelayar anda untuk melindungi laman daripada serangan. Untuk semakan pantas, gerakkan kursor 🖱️ atau leretkan jari anda pada skrin 👆.',
					'status_done'              => 'Mengalihkan ...',
					'err_cookie_title'         => 'Ralat: Kuki dilumpuhkan atau disekat.',
					'err_cookie_desc'          => 'Sila aktifkan kuki dalam tetapan pelayar dan segarkan semula halaman.',
					'err_bot_title'            => 'Ralat: Permintaan automatik dikesan.',
					'err_bot_desc'             => 'Sila gunakan pelayar biasa.'
				);
			break;

			case 'th':
				$i18n = array(
					'title'                    => 'การตรวจสอบความปลอดภัย',
					'javascript_disabled'      => 'JavaScript ถูกปิดใช้งาน',
					'javascript_disabled_desc' => 'ในการดำเนินการตรวจสอบความปลอดภัยและเข้าถึงเว็บไซต์ ต้องเปิดใช้งาน JavaScript',
					'initialization'           => '👋 เรากำลังตรวจสอบเบราว์เซอร์ของคุณเพื่อปกป้องไซต์จากการโจมตี สำหรับการตรวจสอบอย่างรวดเร็ว เพียงเลื่อนเคอร์เซอร์ 🖱️ หรือปัดนิ้วบนหน้าจอ 👆',
					'status_done'              => 'กำลังเปลี่ยนเส้นทาง ...',
					'err_cookie_title'         => 'ข้อผิดพลาด: คุกกี้ถูกปิดใช้งานหรือถูกบล็อก',
					'err_cookie_desc'          => 'โปรดเปิดใช้งานคุกกี้ในการตั้งค่าเบราว์เซอร์และรีเฟรชหน้า',
					'err_bot_title'            => 'ข้อผิดพลาด: ตรวจพบคำขออัตโนมัติ',
					'err_bot_desc'             => 'โปรดใช้เบราว์เซอร์ปกติ'
				);
			break;

			case 'et':
				$i18n = array(
					'title'                    => 'Turvakontroll',
					'javascript_disabled'      => 'JavaScript on keelatud',
					'javascript_disabled_desc' => 'Turvakontrolli tegemiseks ja saidile juurdepääsuks peab JavaScript olema lubatud.',
					'initialization'           => '👋 Kontrollime teie brauserit, et kaitsta saiti rünnakute eest. Kiireks kontrolliks liigutage lihtsalt kursorit 🖱️ või libistage sõrmega üle ekraani 👆.',
					'status_done'              => 'Ümbersuunamine ...',
					'err_cookie_title'         => 'Viga: Küpsised on keelatud või blokeeritud.',
					'err_cookie_desc'          => 'Lubage küpsised brauseri seadetes ja värskendage lehte.',
					'err_bot_title'            => 'Viga: Tuvastati automatiseeritud päring.',
					'err_bot_desc'             => 'Palun kasutage tavalist brauserit.'
				);
			break;

			case 'hr':
				$i18n = array(
					'title'                    => 'Sigurnosna provjera',
					'javascript_disabled'      => 'JavaScript je onemogućen',
					'javascript_disabled_desc' => 'Za obavljanje sigurnosne provjere i pristup web-mjestu, JavaScript mora biti omogućen.',
					'initialization'           => '👋 Provjeravamo vaš preglednik kako bismo zaštitili stranicu od napada. Za brzu provjeru samo pomaknite kursor 🖱️ ili prijeđite prstom po ekranu 👆.',
					'status_done'              => 'Preusmjeravanje ...',
					'err_cookie_title'         => 'Greška: Kolačići su onemogućeni ili blokirani.',
					'err_cookie_desc'          => 'Omogućite kolačiće u postavkama preglednika i osvježite stranicu.',
					'err_bot_title'            => 'Greška: Otkriven je automatizirani zahtjev.',
					'err_bot_desc'             => 'Molimo koristite uobičajeni preglednik.'
				);
			break;

			case 'lt':
				$i18n = array(
					'title'                    => 'Saugumo patikra',
					'javascript_disabled'      => '„JavaScript“ išjungtas',
					'javascript_disabled_desc' => 'Norint atlikti saugumo patikrą ir pasiekti svetainę, „JavaScript“ turi būti įjungtas.',
					'initialization'           => '👋 Tikriname jūsų naršyklę, kad apsaugotume svetainę nuo atakų. Greitam patikrinimui tiesiog pajudinkite žymeklį 🖱️ arba braukite pirštu per ekraną 👆.',
					'status_done'              => 'Nukreipiama ...',
					'err_cookie_title'         => 'Klaida: Slapukai išjungti arba užblokuoti.',
					'err_cookie_desc'          => 'Įjunkite slapukus naršyklės nustatymuose ir atnaujinkite puslapį.',
					'err_bot_title'            => 'Klaida: Aptikta automatizuota užklausa.',
					'err_bot_desc'             => 'Prašome naudoti įprastą naršyklę.'
				);
			break;

			case 'sk':
				$i18n = array(
					'title'                    => 'Bezpečnostná kontrola',
					'javascript_disabled'      => 'JavaScript je zakázaný',
					'javascript_disabled_desc' => 'Na vykonanie bezpečnostnej kontroly a prístup na stránku musí byť JavaScript povolený.',
					'initialization'           => '👋 Kontrolujeme váš prehliadač, aby sme ochránili stránku pred útokmi. Pre rýchlu kontrolu stačí pohnúť kurzorom 🖱️ alebo prejsť prstom po obrazovke 👆.',
					'status_done'              => 'Presmerovanie ...',
					'err_cookie_title'         => 'Chyba: Súbory cookie sú zakázané alebo blokované.',
					'err_cookie_desc'          => 'Povoľte súbory cookie v nastaveniach prehliadača a obnovte stránku.',
					'err_bot_title'            => 'Chyba: Zistená automatizovaná požiadavka.',
					'err_bot_desc'             => 'Použite bežný prehliadač.'
				);
			break;

			case 'sr':
				$i18n = array(
					'title'                    => 'Bezbednosna provera',
					'javascript_disabled'      => 'JavaScript je onemogućen',
					'javascript_disabled_desc' => 'Da biste obavili bezbednosnu proveru i pristupili sajtu, JavaScript mora biti omogućen.',
					'initialization'           => '👋 Проверавамо ваш прегледач да бисмо заштитили сајт од напада. За брзу проверу, једноставно померите курсор 🖱️ или превуците прстом по екрану 👆.',
					'status_done'              => 'Preusmeravanje ...',
					'err_cookie_title'         => 'Greška: Kolačići su onemogućeni ili blokirani.',
					'err_cookie_desc'          => 'Omogućite kolačiće u podešavanjima pregledača i osvežite stranicu.',
					'err_bot_title'            => 'Greška: Otkriven automatizovani zahtev.',
					'err_bot_desc'             => 'Molimo vas koristite običan pregledač.'
				);
			break;

			case 'sl':
				$i18n = array(
					'title'                    => 'Varnostno preverjanje',
					'javascript_disabled'      => 'JavaScript je onemogočen',
					'javascript_disabled_desc' => 'Za izvedbo varnostnega preverjanja in dostop do spletnega mesta mora biti JavaScript omogočen.',
					'initialization'           => '👋 Preverjamo vaš brskalnik, da zaščitimo spletno mesto pred napadi. Za hitro preverjanje premaknite kazalec 🖱️ ali podrsajte s prstom po zaslonu 👆.',
					'status_done'              => 'Preusmerjanje ...',
					'err_cookie_title'         => 'Napaka: Piškotki so onemogočeni ali blokirani.',
					'err_cookie_desc'          => 'Omogočite piškotke v nastavitvah brskalnika in osvežite stran.',
					'err_bot_title'            => 'Napaka: Zaznana avtomatizirana zahteva.',
					'err_bot_desc'             => 'Uporabite običajen brskalnik.'
				);
			break;

			case 'vi':
				$i18n = array(
					'title'                    => 'Kiểm tra bảo mật',
					'javascript_disabled'      => 'JavaScript đã bị tắt',
					'javascript_disabled_desc' => 'Để thực hiện kiểm tra bảo mật và truy cập trang web, JavaScript phải được bật.',
					'initialization'           => '👋 Chúng tôi đang kiểm tra trình duyệt của bạn để bảo vệ trang web khỏi các cuộc tấn công. Để kiểm tra nhanh, chỉ cần di chuyển con trỏ 🖱️ hoặc vuốt ngón tay trên màn hình 👆.',
					'status_done'              => 'Đang chuyển hướng ...',
					'err_cookie_title'         => 'Lỗi: Cookie bị tắt hoặc bị chặn.',
					'err_cookie_desc'          => 'Vui lòng bật cookie trong cài đặt trình duyệt và làm mới trang.',
					'err_bot_title'            => 'Lỗi: Đã phát hiện yêu cầu tự động.',
					'err_bot_desc'             => 'Vui lòng sử dụng trình duyệt thông thường.'
				);
			break;

			case 'tl':
				$i18n = array(
					'title'                    => 'Pagsusuri sa Seguridad',
					'javascript_disabled'      => 'Naka-disable ang JavaScript',
					'javascript_disabled_desc' => 'Upang maisagawa ang pagsusuri sa seguridad at ma-access ang site, dapat na naka-enable ang JavaScript.',
					'initialization'           => '👋 Sinusuri namin ang iyong browser upang maprotektahan ang site mula sa mga pag-atake. Para sa mabilis na pagsusuri, igalaw lamang ang cursor 🖱️ o i-swipe ang iyong daliri sa screen 👆.',
					'status_done'              => 'Nire-redirect ...',
					'err_cookie_title'         => 'Error: Ang cookies ay naka-disable o naka-block.',
					'err_cookie_desc'          => 'Paki-enable ang cookies sa mga setting ng browser at i-refresh ang pahina.',
					'err_bot_title'            => 'Error: May nakitang automated na kahilingan.',
					'err_bot_desc'             => 'Mangyaring gumamit ng regular na browser.'
				);
			break;

			case 'is':
				$i18n = array(
					'title'                    => 'Öryggisskoðun',
					'javascript_disabled'      => 'JavaScript er óvirkt',
					'javascript_disabled_desc' => 'Til að framkvæma öryggisskoðun og fá aðgang að síðunni verður JavaScript að vera virkt.',
					'initialization'           => '👋 Við erum að athuga vafrann þinn til að vernda síðuna fyrir árásum. Fyrir hraða athugun skaltu einfaldlega hreyfa bendilinn 🖱️ eða strjúka fingrinum yfir skjáinn 👆.',
					'status_done'              => 'Endurbeini ...',
					'err_cookie_title'         => 'Villa: Vefkökur eru óvirkar eða læstar.',
					'err_cookie_desc'          => 'Vinsamlegast virkjaðu vefkökur í stillingum vafrans og endurhlaðið síðuna.',
					'err_bot_title'            => 'Villa: Sjálfvirk beiðni greind.',
					'err_bot_desc'             => 'Vinsamlegast notaðu venjulegan vafra.'
				);
			break;
		}

		ob_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title><?php echo $i18n['title']?></title>
<style>
body { background: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: system-ui, -apple-system, sans-serif; }
.challenge-box { background: white; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); padding: 2.5rem; text-align: center; max-width: 32rem; width: 90%; }
.spinner { border: 4px solid #e5e7eb; border-top: 4px solid #2563eb; border-radius: 50%; width: 44px; height: 44px; animation: spin 1s linear infinite; margin: 1.5rem auto; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.message { color: #1f2937; font-size: 1.1rem; margin: 1rem 0; }
.error { color: #dc2626; background: #fee2e2; padding: 0.75rem; border-radius: 8px; font-size: 0.95rem; }
.sub-message { color: #6b7280; font-size: 0.9rem; margin-top: 1.5rem; }
</style>
</head>
<body>

<noscript>
	<div class="challenge-box">
		<h2>⚠️ <?php echo $i18n['javascript_disabled']?></h2>
		<div class="message error"><?php echo $i18n['javascript_disabled_desc']?></div>
	</div>
</noscript>

<div class="challenge-box" id="main-box">
	<div class="spinner" id="spinner"></div>
	<h2 id="title"><?php echo $i18n['title']?></h2>
	<div class="message" id="status-message"><?php echo $i18n['initialization']?></div>
	<div class="sub-message" id="extra-info"></div>
</div>

<script>
(async function(){const _0x1a2b='<?php echo self::getChallengeNonce()?>',_0x3c4d=<?php echo time()?>,_0x5e6f=<?php echo intval(self::$maxAge)?>,_0x7g8h=<?php echo intval(self::$attempt)?>,_0x9i0j=0,_0xak1l=0,_0xbm2m=1,_0xcn3n=['<?php echo $i18n['status_done']?>'],_0xdo4o=['<?php echo $i18n['err_cookie_title']?>','<?php echo $i18n['err_bot_title']?>'],_0xep5p=['<?php echo $i18n['err_cookie_desc']?>','<?php echo $i18n['err_bot_desc']?>'],_0xfq6q=document.getElementById('status-message'),_0xgr7r=document.getElementById('extra-info'),_0xhs8s=document.getElementById('spinner');function _0xjt0t(_0xku1u){_0xfq6q.textContent=_0xcn3n[_0xku1u]||'...';}function _0xlv2v(_0xmw3w){_0xfq6q.textContent=_0xdo4o[_0xmw3w];_0xfq6q.classList.add('error');_0xhs8s.style.display='none';_0xgr7r.textContent=_0xep5p[_0xmw3w];}function _0xyn4x(_0xzo5y){function _0xap6z(_0xbq7a,_0xcr8b){return(_0xbq7a>>>_0xcr8b)|(_0xbq7a<<(32-_0xcr8b));}var _0xds9c=Math.pow,_0xet0d=_0xds9c(2,32),_0xfu1e='length',_0xgv2f,_0xhw3g,_0xix4h='',_0xjy5i=[],_0xkz6j=_0xzo5y[_0xfu1e]*8,_0xla7k=_0xyn4x.h=_0xyn4x.h||[],_0xmb8l=_0xyn4x.k=_0xyn4x.k||[],_0xnc9m=_0xmb8l[_0xfu1e],_0xoda0={};for(var _0xpeb1=2;_0xnc9m<64;_0xpeb1++){if(!_0xoda0[_0xpeb1]){for(_0xgv2f=0;_0xgv2f<313;_0xgv2f+=_0xpeb1)_0xoda0[_0xgv2f]=_0xpeb1;_0xla7k[_0xnc9m]=(_0xds9c(_0xpeb1,.5)*_0xet0d)|0;_0xmb8l[_0xnc9m++]=(_0xds9c(_0xpeb1,1/3)*_0xet0d)|0;}}_0xzo5y+='\x80';while(_0xzo5y[_0xfu1e]%64-56)_0xzo5y+='\x00';for(_0xgv2f=0;_0xgv2f<_0xzo5y[_0xfu1e];_0xgv2f++){_0xhw3g=_0xzo5y.charCodeAt(_0xgv2f);if(_0xhw3g>>8)return;_0xjy5i[_0xgv2f>>2]|=_0xhw3g<<((3-_0xgv2f%4)*8);}_0xjy5i[_0xjy5i[_0xfu1e]]=((_0xkz6j/_0xet0d)|0);_0xjy5i[_0xjy5i[_0xfu1e]]=(_0xkz6j);for(_0xhw3g=0;_0xhw3g<_0xjy5i[_0xfu1e];){var _0xqfc2=_0xjy5i.slice(_0xhw3g,_0xhw3g+=16),_0xrgd3=_0xla7k;_0xla7k=_0xla7k.slice(0,8);for(_0xgv2f=0;_0xgv2f<64;_0xgv2f++){var _0xshe4=_0xqfc2[_0xgv2f-15],_0xtif5=_0xqfc2[_0xgv2f-2],_0xujg6=_0xla7k[0],_0xvkh7=_0xla7k[4],_0xwli8=_0xla7k[7]+(_0xap6z(_0xvkh7,6)^_0xap6z(_0xvkh7,11)^_0xap6z(_0xvkh7,25))+((_0xvkh7&_0xla7k[5])^((~_0xvkh7)&_0xla7k[6]))+_0xmb8l[_0xgv2f]+(_0xqfc2[_0xgv2f]=(_0xgv2f<16)?_0xqfc2[_0xgv2f]:(_0xqfc2[_0xgv2f-16]+(_0xap6z(_0xshe4,7)^_0xap6z(_0xshe4,18)^(_0xshe4>>>3))+_0xqfc2[_0xgv2f-7]+(_0xap6z(_0xtif5,17)^_0xap6z(_0xtif5,19)^(_0xtif5>>>10)))|0),_0xymj9=(_0xap6z(_0xujg6,2)^_0xap6z(_0xujg6,13)^_0xap6z(_0xujg6,22))+((_0xujg6&_0xla7k[1])^(_0xujg6&_0xla7k[2])^(_0xla7k[1]&_0xla7k[2]));_0xla7k=[(_0xwli8+_0xymj9)|0].concat(_0xla7k);_0xla7k[4]=(_0xla7k[4]+_0xwli8)|0;}for(_0xgv2f=0;_0xgv2f<8;_0xgv2f++)_0xla7k[_0xgv2f]=(_0xla7k[_0xgv2f]+_0xrgd3[_0xgv2f])|0;}for(_0xgv2f=0;_0xgv2f<8;_0xgv2f++){for(_0xhw3g=3;_0xhw3g+1;_0xhw3g--){var _0xznk0=(_0xla7k[_0xgv2f]>>(_0xhw3g*8))&255;_0xix4h+=((_0xznk0<16)?0:'')+_0xznk0.toString(16);}}return _0xix4h;}async function _0xaol1(_0xbpm2){if(window.crypto&&window.crypto.subtle){try{const _0xcqn3=await window.crypto.subtle.digest('SHA-256',new TextEncoder().encode(_0xbpm2));return Array.from(new Uint8Array(_0xcqn3)).map(_0xdro4=>_0xdro4.toString(16).padStart(2,'0')).join('');}catch(e){return _0xyn4x(_0xbpm2);}}return _0xyn4x(_0xbpm2);}function _0xzesp5(){if(!window.AudioContext&&!window.webkitAudioContext)return'unsupported';try{const _0xftq6=window.AudioContext||window.webkitAudioContext,_0xguv7=new _0xftq6();return`supported-${_0xguv7.sampleRate}-${_0xguv7.destination.maxChannelCount}-${_0xguv7.state}`;}catch(e){return'error';}}function _0xhvx8(){const _0xiw9=['monospace','sans-serif','serif'],_0xjxx0=['Aptos','Arial','Arial Black','Arial Hebrew','Arial Narrow','Arial Rounded MT Bold','Avenir','Avenir Next','Baskerville','Book Antiqua','Bookman Old Style','Bradley Hand ITC','Calibri','Candara','Carlito','Century','Century Gothic','Comic Sans MS','Consolas','Constantia','Corbel','Courier','Courier New','Droid Sans Mono','Franklin Gothic Medium','Futura','Garuda','Geneva','Gentium','Georgia','Gill Sans','Helvetica','Helvetica Neue','Impact','King','Lalit','Lato','Lucida Console','Lucida Grande','Lucida Sans Unicode','Luminari','Menlo','Microsoft Sans Serif','Modena','Monaco','Monotype Corsiva','New York','Noto Sans','Noto Serif','Optima','Palatino','Palatino Linotype','Papyrus','Roboto','Rockwell','San Francisco','Segoe UI','Segoe UI Symbol','Symbol','Tahoma','Times','Times New Roman','Trebuchet MS','Ubuntu','Verdana','Verona','Webdings','Wingdings'],_0xkyy1=document.createElement('canvas'),_0xlzz2=_0xkyy1.getContext('2d'),_0xmaa3='mmmmmmmmmmlli',_0xnbb4='72px';let _0xocc5=0;for(const _0xpdd6 of _0xjxx0){let _0xqee7=false;for(const _0xrff8 of _0xiw9){_0xlzz2.font=`${_0xnbb4} "${_0xpdd6}", ${_0xrff8}`;const _0xsgg9=_0xlzz2.measureText(_0xmaa3).width;_0xlzz2.font=`${_0xnbb4} ${_0xrff8}`;const _0xthh0=_0xlzz2.measureText(_0xmaa3).width;if(_0xsgg9!==_0xthh0){_0xqee7=true;break;}}if(_0xqee7)_0xocc5++;}return _0xocc5;}function _0xuiia(){try{document.cookie="_ctest=1; path=/; SameSite=Lax; max-age=60";if(document.cookie.indexOf('_ctest=1')!==-1){document.cookie="_ctest=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";return true;}}catch(e){}return false;}function _0xvjjb(){if(navigator.webdriver)return true;for(let _0xwkkc in window){if(_0xwkkc.match(/^(cdc_[a-zA-Z0-9]+_|_selenium|__webdriver|__nightmare)/))return true;}if(window.callPhantom||window._phantom||window.__nightmare)return true;const _0xylld=/Chrome/.test(navigator.userAgent)&&/Google Inc/.test(navigator.vendor);if(_0xylld&&!window.chrome)return true;if(navigator.languages!==undefined&&navigator.languages.length===0)return true;return false;}async function _0xzmmf(){const _0xanng=[];_0xanng.push(navigator.userAgent);_0xanng.push(navigator.languages?navigator.languages.join(','):navigator.language);_0xanng.push(navigator.platform);try{_0xanng.push(Intl.DateTimeFormat().resolvedOptions().timeZone);}catch(e){_0xanng.push('tz_unknown');}_0xanng.push(screen.width+'x'+screen.height+'x'+screen.colorDepth);try{const _0xbooh=document.createElement('canvas');_0xbooh.width=200;_0xbooh.height=50;const _0xcppi=_0xbooh.getContext('2d');_0xcppi.textBaseline='top';_0xcppi.font='14px Arial';_0xcppi.fillStyle='#f60';_0xcppi.fillRect(0,0,200,50);_0xcppi.fillStyle='#069';_0xcppi.fillText('Fingerprint',2,15);_0xanng.push(_0xbooh.toDataURL());}catch(e){_0xanng.push('canvas_blocked');}try{const _0xdqqj=document.createElement('canvas').getContext('webgl');if(_0xdqqj){const _0xerrk=_0xdqqj.getExtension('WEBGL_debug_renderer_info');_0xanng.push((_0xerrk?_0xdqqj.getParameter(_0xerrk.UNMASKED_VENDOR_WEBGL):'unk')+'|'+(_0xerrk?_0xdqqj.getParameter(_0xerrk.UNMASKED_RENDERER_WEBGL):'unk'));}else{_0xanng.push('webgl_unsupported');}}catch(e){_0xanng.push('webgl_blocked');}const _0xfssl=_0xanng.join('###');return await _0xaol1(_0xfssl);}if(!_0xuiia()){_0xlv2v(_0xak1l);return;}if(_0xvjjb()){_0xlv2v(_0xbm2m);return;}const _0xgttm={moves:0,distance:0,scrolls:0,scrollDelta:0,keys:0,taps:0,touchMoves:0,wheelAttempts:0,start:Date.now(),elapsed:0};let _0xhuun=null,_0xivvo=null;function _0xjwwp(_0xkxxq){_0xgttm.moves++;if(_0xhuun!==null){_0xgttm.distance+=Math.sqrt(Math.pow(_0xkxxq.clientX-_0xhuun,2)+Math.pow(_0xkxxq.clientY-_0xivvo,2));}_0xhuun=_0xkxxq.clientX;_0xivvo=_0xkxxq.clientY;}function _0xlyyr(){_0xgttm.scrolls++;_0xgttm.scrollDelta+=15;}function _0xmzzs(){_0xgttm.keys++;}function _0xnaat(_0xobbu){_0xgttm.taps++;}function _0xpccv(_0xqddw){_0xgttm.touchMoves++;}function _0xreex(_0xsffy){_0xgttm.wheelAttempts++;}try{const _0xtggz=document.referrer,_0xuhha=window.location.hostname;if(_0xtggz&&new URL(_0xtggz).hostname!==_0xuhha){localStorage.setItem('_h_referer',_0xtggz);}}catch(e){}document.addEventListener('mousemove',_0xjwwp);window.addEventListener('scroll',_0xlyyr);document.addEventListener('keydown',_0xmzzs);document.addEventListener('touchstart',_0xnaat,{passive:true});document.addEventListener('touchmove',_0xpccv,{passive:true});document.addEventListener('wheel',_0xreex,{passive:true});if(_0x7g8h<3){await new Promise(_0xviib=>setTimeout(_0xviib,1500));}else{await new Promise(_0xwjjc=>{function _0xxkkd(){setTimeout(_0xwjjc,1410);}const _0xyllm=['mousemove','click','keydown','touchstart','touchmove','wheel'];_0xyllm.forEach(_0xzmme=>document.addEventListener(_0xzmme,_0xxkkd,{once:true,passive:true}));window.addEventListener('scroll',_0xxkkd,{once:true,passive:true});});}document.removeEventListener('mousemove',_0xjwwp);window.removeEventListener('scroll',_0xlyyr);document.removeEventListener('keydown',_0xmzzs);document.removeEventListener('touchstart',_0xnaat);document.removeEventListener('touchmove',_0xpccv);document.removeEventListener('wheel',_0xreex);if(window.__a_dtc){_0xlv2v(_0xbm2m);return;}_0xgttm.elapsed=Date.now()-_0xgttm.start;const _0xannf=await _0xzmmf(),_0xboog=`${_0xgttm.moves}|${Math.round(_0xgttm.distance)}|${_0xgttm.scrolls}|${_0xgttm.scrollDelta}|${_0xgttm.keys}|${_0xgttm.taps}|${_0xgttm.touchMoves}|${_0xgttm.wheelAttempts}|${_0xgttm.elapsed}|${_0xzesp5()}|${_0xhvx8()}`, _0xcpph=_0xannf+'::'+_0xboog+'::'+_0x3c4d+'::'+_0x1a2b,_0xdqqi=await _0xaol1(_0xcpph),_0xerrj={fp:_0xannf,bh:_0xboog,ts:_0x3c4d},_0xfssk=_0xdqqi+'.'+btoa(JSON.stringify(_0xerrj));document.cookie=`_h_ct=${_0xfssk}; path=/; max-age=${_0x5e6f}; SameSite=Lax`;_0xjt0t(_0x9i0j);setTimeout(()=>{history.go(0);},150);})();
</script>
</body>
</html>
<?php
		$oCore_Response->body(ob_get_clean());
		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));
		return $oCore_Response;
	}
}