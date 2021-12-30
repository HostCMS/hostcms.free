<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Typograph.
 *
 * @package HostCMS
 * @subpackage Typograph
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Typograph_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getConfig()
	{
		return (array)Core::$config->get('typograph_config', array()) + array(
			'typograph' => TRUE,
			'trailing_punctuation' => TRUE
		);
	}

	protected $_left_span = NULL;
	protected $_right_span = NULL;

	/**
	 * Метод для удаления тегов предыдущего оптического выравнивания
	 *
	 * @param string $str исходная строка
	 * <code>
	 * <?php
	 * $str = '<span style="margin-right: 0.3em"> </span> <span style="margin-left: -0.3em">«Типограф</span>» — удобный инструмент для автоматического типографирования в соответствии с правилами, принятыми для экранной типографики. Может применяться как для обычного текста, так и HTML-кода.';
	 *
	 * echo Typograph_Controller::instance()->eraseOpticalAlignment($str);
	 *
	 * ?>
	 * </code>
	 * @return string строка с вырезанными тегами отического выравнивания
	 */
	public function eraseOpticalAlignment($str)
	{
		$str = strval($str);

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		if (!empty($oSite->css_left))
		{
			$str = str_replace(
				array('<span class="' . $oSite->css_left . '"> </span>', '<span class="' . $oSite->css_left.'"></span>'), '', $str
			);
			$str = preg_replace('/(<span class="' . $oSite->css_left . '">)(\S*)(<\/span>)/iu', '\\2', $str);

			// Broken text
			$str = str_replace('<span class="' . $oSite->css_left . '">', " ", $str);
		}

		if (!empty($oSite->css_right))
		{
			$str = str_replace(
				array('<span class="' . $oSite->css_right . '"> </span>', '<span class="' . $oSite->css_right . '"></span>'), '', $str
			);
		}

		// Удаляем теги от предыдущего оптического выравнивания
		$str = str_replace(
			array(
				'<span style="margin-right: 0.3em"> </span>', '<span style="margin-right: 0.3em"></span>',
				'<span style="margin-right: 0.3em;"> </span>', '<span style="margin-right: 0.3em;"></span>'
			),
			"", $str
		);
		$str = preg_replace('/(<span style="margin-left: -0.3em[;]?">)(\S*)(<\/span>)/iu', '\\2', $str);

		// Opera bug fix
		$str = str_replace(
			array('<span STYLE="margin-right: 0.30em"> </span>', '<span STYLE="margin-right: 0.30em"></span>'), '', $str
		);
		$str = preg_replace('/(<span STYLE="margin-left: -0.30em[;]?">)(\S*)(<\/span>)/iu', '\\2', $str);

		// Broken text
		$str = str_replace(
			array(
				'<span style="margin-right: 0.30em">', '<span style="margin-left: -0.30em">',
				'<span style="margin-right: 0.30em;">', '<span style="margin-left: -0.30em;">'
			), ' ', $str
		);

		return $str;
	}

	protected function _quotesTagCallback($matches)
	{
		return "<" . str_replace("\"", "¬", $matches[1]) . ">";
	}

	protected function _quotesSquareBracketsCallback($matches)
	{
		return "[" . str_replace("\"", "¬", $matches[1]) . "]";
	}

	protected function _quotesBracketsCallback($matches)
	{
		return "<" . str_replace("(", chr(0x01), $matches[1]) . ">";
	}

	protected function _herringboneQuotesCallback($matches)
	{
		return "<" . str_replace("«", chr(0x02), $matches[1]) . ">";
	}

	protected function _germanQuotesCallback($matches)
	{
		return "<" . str_replace("„", chr(0x03), $matches[1]) . ">";
	}

	/**
	 * Execute the typograph
	 * @param string $str source text
	 * @param boolean $bTrailingPunctuation use trailing punctuation
	 * @return string
	 */
	public function process($str, $bTrailingPunctuation = FALSE)
	{
		$aEntries = array(
			"TAG1" => "H3ew2Qwdw1",
			"TAG2" => "H3ew2Qwdw2",
			"LAQUO" => "H3ew2Qwdw3",
			"RAQUO" => "H3ew2Qwdw4",
			"LDQUO" => "H3ew2Qwdw5",
			"RDQUO" => "H3ew2Qwdw6",
		);

		// Удаляем теги от предыдущей типографики.
		$str = $this->eraseOpticalAlignment($str);

		// Кавычки необходимо заменять до замены скобок, т.к.
		// выражение преобразования добавляет слэши перед кавычками.

		// кавычки в html-тегах на символ '¬'
		//$str = preg_replace("/<([^>]*)>/esu", "'<'.str_replace('\\\"', '¬','\\1').'>'", $str);
		$str = preg_replace_callback("/<([^>]*)>/su", array($this, '_quotesTagCallback'), $str);

		// кавычки в квадратных скобках [] на символ '¬'
		$str = preg_replace_callback("/\[([^>]*)\]/su", array($this, '_quotesSquareBracketsCallback'), $str);

		// Расстановка заков в скобках перед добавлением висячей пунктуации
		$str = str_replace(
			array("(R)",	"(r)",	"(TM)",	"(tm)",	"(C)",	"(c)"),
			array("®",		"®",	"™",	"™",	"©",	"©"),
		$str);

		// Расстановка квадратных и кубических метров м2 м3.
		$str = str_replace(
			array("кв.м.", "кв.м", "кв м.", "кв м", "м2"),
			"м²",
		$str);

		$str = str_replace(
			array("куб.м.", "куб.м", "куб м.", "куб м", "м3"),
			"м³",
		$str);

		// ... заменяем до работы с кавычками
		$str = str_replace(array('&hellip;', '…'), '...', $str);

		/**
		* Кавычки всегда прилегают к словам!
		* Открывающиеся кавычки могут встречаться:
		* в начале строки, после скобок "([{", дефиса, пробелов и ещё одной кавычки
		*/
		// Заменяем сущности на простые кавычки
		$str = str_replace(
			array(
				'&quot;', '&laquo;', '&raquo;', '&rdquo;', '&bdquo;', '&ldquo;',
				'«', '»', '”', '„', '“'
			),
			'"', $str
		);

		// "Ответ на известную арию из "Русалки" ".
		$str = str_replace('" "','""', $str);

		// «ТекстA
		$str = preg_replace('{^"}u', $aEntries['LAQUO'], $str);
		$prequote = '[\s\(\[\{";->]';

		/* В случае, если после открывающей круглой скобки следует "?:", захват строки не происходит,
		 * и текущая подмаска не нумеруется.
		 *
		 * Утверждения касательно предшествующего текста начинаются с (?<= для положительных утверждений
		 * и (?<! для отрицающих. Например, (?<!foo)bar не найдёт вхождения "bar", которым не предшествует
		 * "foo". Сами утверждения 'назад' ограничены так, чтобы все подстроки, которым они соответствуют,
		 * имели фиксированную длину.
		 */
		// В диапазоне <a-zA-Zа-яА-ЯёЁ0-9 добавлено начало тега "<", т.к. перед текстом может быть ссылка
		$str = preg_replace('/(?<='.$prequote.')"(?=[<a-zA-Zа-яА-ЯёЁ0-9]|\.{3,5})/u', $aEntries['LAQUO'], $str);
		//$str = preg_replace('/(?:[a-zA-Zа-яА-ЯёЁ0-9_])"/u', $aEntries['LAQUO'], $str);

		// Определяем закрывающиеся кавычки
		$str = preg_replace("/(<\/[^\/][^>]*>)".$aEntries['LAQUO']."/u", '\1"', $str);

		$str = preg_replace('{^((?:' . $aEntries['TAG1'] . '\d+' . $aEntries['TAG2'] . ')+)"}u', '\1' . $aEntries['LAQUO'], $str);
		$str = preg_replace('{(?<=' . $prequote . ')((?:' . $aEntries['TAG1'] . '\d+' . $aEntries['TAG2'] . ')+)"}u', '\1' . $aEntries['LAQUO'], $str);

		// Закрывающиеся кавычки - все остальные
		$str = str_replace('"', $aEntries['RAQUO'], $str);

		// исправляем ошибки в расстановке кавычек типа ""... и ...""
		$str = preg_replace('/' . $aEntries['LAQUO'] . $aEntries['RAQUO'] . $aEntries['RAQUO'] . '/u', $aEntries['LAQUO'], $str);

		// Вложенные кавычки
		// «Текст», „Текст“
		//$regex = '/'.$aEntries['LAQUO'].'(.*?)'.$aEntries['LAQUO'].'(.*?)'.$aEntries['RAQUO'].'/u'; // без s
		// «Текст», «Текст», но «Текст, „Текст“»
		$regex = '/' . $aEntries['LAQUO'] . '([^' . $aEntries['RAQUO'] . ']*?)' . $aEntries['LAQUO'] . '(.*?)' . $aEntries['RAQUO'] . '/u'; // без s
		$replace = $aEntries['LAQUO'] . '\\1' . $aEntries['LDQUO'] . '\\2' . $aEntries['RDQUO'];

		$i = 0; // защита от зацикливания при неправильно расставленных кавычках
		while (($i++ < 10) && ($str = preg_replace($regex, $replace, $str, -1, $count)) && $count){}

		// заменяем коды символов на HTML-entities.
		$str = str_replace(
		array($aEntries['LAQUO'], $aEntries['RAQUO'], $aEntries['LDQUO'], $aEntries['RDQUO']),
		array('«', '»', '„', '“'), $str);
		// / расстановка кавычек

		// Все на неразрывный пробел (0x00A0)
		$str = str_replace(array('&nbsp;', '&thinsp;', '&#160;'), ' ', $str);

		// (!) в замене и источниках ипользованы неразрывные пробелы " "
		$str = str_replace(array(' - ', ' - ', ' - ', ' -- ', ' -- ', ' — '), ' — ', $str);
		//$str = str_replace('- ','— ', $str); // заменяет <!-- на <!-—

		// Множественные знаки !!!!, ????, ....
		$str = preg_replace('/([\.\!\?]){4,}/', '\1\1\1', $str);

		// Удаляем множекственные запятые и точки с запятой
		$str = preg_replace('/([,]){2,}/', '\1', $str);
		$str = preg_replace('/([;]){2,}/', '\1', $str);

		// Многоточие
		$str = str_replace('...', '…', $str);

		// МИНУС-ПЛЮС.
		$str = str_replace(array('+-', '-+'), '±', $str);

		// Стрелки с равно <==, ==>, <==>
		$str = str_replace('&lt;==&gt;', '⇔', $str);
		$str = str_replace('&lt;==', '⇐', $str);
		$str = str_replace('==&gt;', '⇒', $str);

		// меньше или равно
		$str = str_replace('&lt;=', '&le;', $str);

		// больше или равно
		$str = str_replace('=&gt;', '&ge;', $str);

		// Стрелки с минусом.
		$str = str_replace('--&gt;', '→', $str);
		$str = str_replace('&lt;--', '←', $str);

		$str = str_replace(array('&lt;-&gt;', '&harr;'), '↔', $str);

		// Дроби.
		$str = str_replace(array(' 1/4 ', ' &#188; '), ' ¼ ', $str);
		$str = str_replace(array(' 1/2 ', ' &#189; '), ' ½ ', $str);
		$str = str_replace(array(' 3/4 ', ' &#190; '), ' ¾ ', $str);
		$str = str_replace(array(' 2/3 ', ' &#8532; '), ' ⅔ ', $str);
		$str = str_replace(array(' 1/8 ', ' &#8539; '), ' ⅛ ', $str);
		$str = str_replace(array(' 3/8 ', ' &#8540; '), ' ⅜ ', $str);
		$str = str_replace(array(' 5/8 ', ' &#8541; '), ' ⅝ ', $str);
		$str = str_replace(array(' 7/8 ', ' &#8542; '), ' ⅞ ', $str);

		// Все замены с пробелами перед знаками препинания пишем до удаления двойного пробела
		$str = str_replace(' ,', ', ', $str);
		$str = str_replace(' .', '. ', $str);
		$str = str_replace(' !', '! ', $str);
		$str = str_replace(' ?', '? ', $str);
		$str = str_replace(' )', ') ', $str);
		$str = str_replace('( ', ' (', $str);

		// Убираем пробелы внутри кавычек.
		$str = str_replace('« ', ' «', $str);
		$str = str_replace(' »', '» ', $str);

		// Двойной пробел (убираем второй раз).
		// $str = str_replace('  ', ' ', $str);

		// Добавление от 13-08-08
		// Меняем разделитель 'Запятая' на разделитель 'Запятая' + 'Пробел'
		// нельзя, т.к. в '0,5 кг.' получается лишний пробел.
		//$str = str_replace(',', ', ', $str);

		/*$str = str_replace(' изза ', ' из-за ', $str);
		$str = str_replace(' из за ', ' из-за ', $str);
		$str = str_replace('изза ', 'из-за ', $str);
		$str = str_replace('из за ', 'из-за ', $str);
		$str = str_replace(' изпод ', ' из-под ', $str);
		$str = str_replace(' из под ', ' из-под ', $str);
		$str = str_replace('изпод ', 'из-под ', $str);
		$str = str_replace('из под ', 'из-под ', $str);*/
		$str = str_replace('P.S.', '<nobr>P.S.</nobr>', $str);
		$str = str_replace('P. S.', '<nobr>P.S.</nobr>', $str);
		$str = str_replace('P. S. ', '<nobr>P.S.</nobr>', $str);
		$str = str_replace(' .', '. ', $str);
		$str = str_replace(' . ', '. ', $str);
		$str = str_replace('. </nobr>', '.</nobr>', $str);
		$str = str_replace(' dpi', ' dpi', $str);
		$str = str_replace(' lpi', ' lpi', $str);

		// Заменяет + в адресах URL вместо пробела
		//$str = str_replace("+", " + ", $str);
		//$str = str_replace("-", " - ", $str);

		$str = preg_replace("/(\d) %/u", "\\1%", $str);

		// Убираем лишние пробелы
		while (mb_strpos($str, '  ') !== FALSE)
		{
			$str = str_replace('  ', ' ', $str);
		}

		// Убираем лишние неразрывные пробелы
		while (mb_strpos($str, '  ') !== FALSE)
		{
			$str = str_replace('  ', ' ', $str);
		}

		// Обработка ФИО: Иванов А.А. -> Иванов А. А.
		$str = preg_replace("/([А-ЯA-Z][а-яa-z]+)\s+([А-ЯA-Z]\.)\s*([А-ЯA-Z]\.)/su", "\\1 \\2 \\3", $str);

		// 10 x 12 -> 10&times;12
		// [xх] - английская и русская Хэ - "х"
		// (<[^>]*>)*(.*) - исключаем замены внутри тегов
		// <a href="http://aaa/tovar_13x18/">asdsadsa</a>
		/*$str2 = preg_replace("/(<[^\/][^>]*>.*)?(\d)\s*[xх]\s*(\d)/su", "\\1\\2&times;\\3", $str);
		// Регулярное выражение на вернуло пустоту
		if (mb_strlen($str2) > 0)
		{
			$str = $str2;
		}*/

		// s/(\d{4})\s+(г.|год)/$1 $2/gms
		// 2000 г -> 2000 г
		// + всяческие другие комбинации
		// ^\w чтобы не было проблем с <h1 class="first">   </h1>
		$str = preg_replace("/(^\w\d)\s+(\w*)/su", "\\1 \\2", $str);

		// $str = str_replace(" %", "%", $str);
		// $str = preg_replace("/(\d) %/u", "\\1%", $str);
		// $str = str_replace(" ,", ",", $str);
		$str = str_replace(" )", ")", $str);
		$str = str_replace("( ", "(", $str);
		/*$str = str_replace(")  ", ") ", $str);
		 $str = str_replace("  (", " (", $str);*/
		$str = str_replace(" : ", ": ", $str);

		$str = str_replace(" :", ": ", $str);

		$str = str_replace("что-то", "<nobr>что-то</nobr> ", $str);
		$str = str_replace("чтото", "<nobr>что-то</nobr> ", $str);
		$str = str_replace("что то ", "<nobr>что-то</nobr> ", $str);
		$str = str_replace("где-то", "<nobr>где-то</nobr> ", $str);
		$str = str_replace("гдето", "<nobr>где-то</nobr> ", $str);
		$str = str_replace("где то ", "<nobr>где-то</nobr> ", $str);
		$str = str_replace("кем-то", "<nobr>кем-то</nobr> ", $str);
		$str = str_replace("кемто ", "<nobr>кем-то</nobr> ", $str);
		$str = str_replace("кем то ", "<nobr>кем-то</nobr> ", $str);
		$str = str_replace("кому-то", "<nobr>кому-то</nobr> ", $str);
		$str = str_replace("комуто", "<nobr>кому-то</nobr> ", $str);
		$str = str_replace("кому то ", "<nobr>кому-то</nobr> ", $str);
		$str = str_replace("как-то", "<nobr>как-то</nobr> ", $str);
		$str = str_replace("както", "<nobr>как-то</nobr> ", $str);
		$str = str_replace("как то ", "<nobr>как-то</nobr> ", $str);
		$str = str_replace("когда-то", "<nobr>когда-то</nobr> ", $str);
		$str = str_replace("когдато", "<nobr>когда-то</nobr> ", $str);
		$str = str_replace("когда то ", "<nobr>когда-то</nobr> ", $str);
		$str = str_replace("чем-то", "<nobr>чем-то</nobr> ", $str);
		$str = str_replace("чемто", "<nobr>чем-то</nobr> ", $str);
		$str = str_replace("чем то ", "<nobr>чем-то</nobr> ", $str);
		$str = str_replace("кто-то", "<nobr>кто-то</nobr> ", $str);
		$str = str_replace("ктото", "<nobr>кто-то</nobr> ", $str);
		$str = str_replace("кто то ", "<nobr>кто-то</nobr> ", $str);
		$str = str_replace("чего-то", "<nobr>чего-то</nobr> ", $str);
		$str = str_replace("чегото", "<nobr>чего-то</nobr> ", $str);
		$str = str_replace("чего то ", "<nobr>чего-то</nobr> ", $str);
		$str = str_replace("что-либо", "<nobr>что-либо</nobr> ", $str);
		$str = str_replace("чтолибо", "<nobr>что-либо</nobr> ", $str);
		$str = str_replace("что либо", "<nobr>что-либо</nobr> ", $str);
		$str = str_replace("где-либо", "<nobr>где-либо</nobr> ", $str);
		$str = str_replace("гделибо", "<nobr>где-либо</nobr> ", $str);
		$str = str_replace("где либо", "<nobr>где-либо</nobr> ", $str);
		$str = str_replace("кем-либо", "<nobr>кем-либо</nobr> ", $str);
		$str = str_replace("кемлибо", "<nobr>кем-либо</nobr> ", $str);
		$str = str_replace("кем либо", "<nobr>кем-либо</nobr> ", $str);
		$str = str_replace("кому-либо", "<nobr>кому-либо</nobr> ", $str);
		$str = str_replace("комулибо", "<nobr>кому-либо</nobr> ", $str);
		$str = str_replace("кому либо", "<nobr>кому-либо</nobr> ", $str);
		$str = str_replace("как-либо", "<nobr>как-либо</nobr> ", $str);
		$str = str_replace("каклибо", "<nobr>как-либо</nobr> ", $str);
		$str = str_replace("как либо", "<nobr>как-либо</nobr> ", $str);
		$str = str_replace("когда-либо", "<nobr>когда-либо</nobr> ", $str);
		$str = str_replace("когдалибо", "<nobr>когда-либо</nobr> ", $str);
		$str = str_replace("когда либо", "<nobr>когда-либо</nobr> ", $str);
		$str = str_replace("чем-либо", "<nobr>чем-либо</nobr> ", $str);
		$str = str_replace("чемлибо", "<nobr>чем-либо</nobr> ", $str);
		$str = str_replace("чем либо", "<nobr>чем-либо</nobr> ", $str);
		$str = str_replace("кто-либо", "<nobr>кто-либо</nobr> ", $str);
		$str = str_replace("ктолибо", "<nobr>кто-либо</nobr> ", $str);
		$str = str_replace("кто либо", "<nobr>кто-либо</nobr> ", $str);
		$str = str_replace("чего-либо", "<nobr>чего-либо</nobr> ", $str);
		$str = str_replace("чеголибо", "<nobr>чего-либо</nobr> ", $str);
		$str = str_replace("чего либо", "<nobr>чего-либо</nobr> ", $str);
		$str = str_replace("что-нибудь", "<nobr>что-нибудь</nobr> ", $str);
		$str = str_replace("чтонибудь", "<nobr>что-нибудь</nobr> ", $str);
		$str = str_replace("что нибудь", "<nobr>что-нибудь</nobr> ", $str);
		$str = str_replace("где-нибудь", "<nobr>где-нибудь</nobr> ", $str);
		$str = str_replace("гденибудь", "<nobr>где-нибудь</nobr> ", $str);
		$str = str_replace("где нибудь", "<nobr>где-нибудь</nobr> ", $str);
		$str = str_replace("кем-нибудь", "<nobr>кем-нибудь</nobr> ", $str);
		$str = str_replace("кемнибудь", "<nobr>кем-нибудь</nobr> ", $str);
		$str = str_replace("кем нибудь", "<nobr>кем-нибудь</nobr> ", $str);
		$str = str_replace("кому-нибудь", "<nobr>кому-нибудь</nobr> ", $str);
		$str = str_replace("комунибудь", "<nobr>кому-нибудь</nobr> ", $str);
		$str = str_replace("кому нибудь", "<nobr>кому-нибудь</nobr> ", $str);
		$str = str_replace("как-нибудь", "<nobr>как-нибудь</nobr> ", $str);
		$str = str_replace("какнибудь", "<nobr>как-нибудь</nobr> ", $str);
		$str = str_replace("как нибудь", "<nobr>как-нибудь</nobr> ", $str);
		$str = str_replace("когда-нибудь", "<nobr>когда-нибудь</nobr> ", $str);
		$str = str_replace("когданибудь", "<nobr>когда-нибудь</nobr> ", $str);
		$str = str_replace("когда нибудь", "<nobr>когда-нибудь</nobr> ", $str);
		$str = str_replace("чем-нибудь", "<nobr>чем-нибудь</nobr> ", $str);
		$str = str_replace("чемнибудь", "<nobr>чем-нибудь</nobr> ", $str);
		$str = str_replace("чем нибудь", "<nobr>чем-нибудь</nobr> ", $str);
		$str = str_replace("кто-нибудь", "<nobr>кто-нибудь</nobr> ", $str);
		$str = str_replace("ктонибудь", "<nobr>кто-нибудь</nobr> ", $str);
		$str = str_replace("кто нибудь", "<nobr>кто-нибудь</nobr> ", $str);
		$str = str_replace("чего-нибудь", "<nobr>чего-нибудь</nobr> ", $str);
		$str = str_replace("чегонибудь", "<nobr>чего-нибудь</nobr> ", $str);
		$str = str_replace("чего нибудь", "<nobr>чего-нибудь</nobr> ", $str);

		$str = str_replace(" кое ", " кое-", $str);
		$str = str_replace(" кой ", " кой-", $str);
		$str = str_replace(" ка ", "-ка ", $str);
		$str = str_replace(" ка,", "-ка,", $str);
		$str = str_replace(" де ", "-де ", $str);
		$str = str_replace(" де,", "-де,", $str);
		$str = str_replace(" кась ", "-кась ", $str);
		$str = str_replace(" кась,", "-кась,", $str);

		// (' &nbsp;', '&nbsp; ') => '&nbsp;'
		$str = str_replace(array('  ', '  '), ' ', $str);

		// тире в начале строки (диалоги)
		$str = preg_replace('/([>|\s])- /u',"\\1— ", $str);

		// Оптическое выравнивание
		if ($bTrailingPunctuation)
		{
			// Заменяем СКОБКИ В ТЕГАХ на непечатные символы
			//$str = preg_replace("/<([^>]*)>/esu", "'<'.str_replace('(', chr(0x01),'\\1').'>'", $str);
			$str = preg_replace_callback("/<([^>]*)>/su", array($this, '_quotesBracketsCallback'), $str);

			$oSite = Core_Entity::factory('Site', CURRENT_SITE);

			$this->_left_span = !empty($oSite->css_left)
				? '<span class=¬'.$oSite->css_left.'¬>'
				: '<span style=¬margin-left: -0.3em¬>';

			$this->_right_span = !empty($oSite->css_right)
				? '<span class=¬' . $oSite->css_right . '¬>'
				: '<span style=¬margin-right: 0.3em¬>';

			// Добавляем висячие скобки
			//$str = preg_replace("/(\s)?(<[^\/][^>]*>)?(\s)?(\(\w*)/iseu", "'{$this->_right_span} </span> \\2{$this->_left_span}'.str_replace('(', chr(0x01), '\\4').'</span>'", $str);
			// исключено (\s)?(<[^\/][^>]*>)?
			// т.к. тогда span вылезает слева за <p>
			//$str = preg_replace("/(\s)?(\(\w*)/iseu", "'{$this->_right_span} </span> {$this->_left_span}'.str_replace('(', chr(0x01), '\\2').'</span>'", $str);
			$str = preg_replace_callback("/(\s)?(\(\w*)/isu", array($this, '_trailingPunctuationBrackets'), $str);

			// Восстанавливаем скобки в тегах.
			$str = str_replace(chr(0x01), '(', $str);

			// Заменяем ЁЛОЧКИ В ТЕГАХ на непечатные символы.
			//$str = preg_replace("/<([^>]*)>/esu", "'<'.str_replace('«', chr(0x02),'\\1').'>'", $str);
			$str = preg_replace_callback("/<([^>]*)>/su", array($this, '_herringboneQuotesCallback'), $str);

			// Добавляем висячие елочки.
			// возможно проблема вылезания за <p>, пример изменения см. выше
			//$str = preg_replace("/(\s)?(<[^\/][^>]*>)?(\s)?(\«\w*)/iseu", "'{$this->_right_span} </span> \\2{$this->_left_span}'.str_replace('«', chr(0x02), '\\4').'</span>'", $str);
			$str = preg_replace_callback("/(\s)?(<[^\/][^>]*>)?(\s)?(\«\w*)/isu", array($this, '_trailingPunctuationFrenchQuotes'), $str);

			// Восстанавливаем елочки в тегах.
			$str = str_replace(chr(0x02), '«', $str);

			// Заменяем ЛАПКИ В ТЕГАХ на непечатные символы.
			//$str = preg_replace("/<([^>]*)>/esu", "'<'.str_replace('„', chr(0x03),'\\1').'>'", $str);
			$str = preg_replace_callback("/<([^>]*)>/su", array($this, '_germanQuotesCallback'), $str);

			// Восстанавливаем лапки в тегах.
			$str = str_replace(chr(0x03), '„', $str);
		}

		// меняем "¬" обратно на кавычки
		$str = str_replace('¬', '"', $str);

		$replace = array(
			// неразрывный пробел перед словами
			"/(\s+)(ж|же|ли|либо|или)(?=\s)/iu" => ' $2', //2

			// Неразрывный пробел после РУССКИХ слов, длиной 3 и менее символов.
			"/(?<=\s)([а-яА-ЯёЁ]{1,3}|если|однако)(\s+)/iu" => '$1 '
		);

		/* Предлоги вместе со словом (слово не переносится на другую строку
		 отдельно от предлога)*/
		$str = preg_replace(array_keys($replace), array_values($replace), $str);

		return $str;
	}

	protected function _trailingPunctuationBrackets($matches)
	{
		return "{$this->_right_span} </span> {$this->_left_span}" . str_replace('(', chr(0x01), $matches[2]) . '</span>';
	}

	protected function _trailingPunctuationFrenchQuotes($matches)
	{
		return "{$this->_right_span} </span> " . $matches[2] . $this->_left_span . str_replace('«', chr(0x02), $matches[4]) . '</span>';
	}
}