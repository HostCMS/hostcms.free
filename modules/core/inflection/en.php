<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * English inflection.
 *
 * @package HostCMS
 * @subpackage Core\Inflection
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Inflection_En extends Core_Inflection
{
	/**
	 * Array of irregular form singular => plural
	 * @var array
	 */
	static public $pluralIrregular = array (
		'bus' => 'busses',
		'child' => 'children',
		'calf' => 'calves',
		'elf' => 'elves',
		'foot' => 'feet',
		'goose' => 'geese',
		'half' => 'halves',
		'hoof' => 'hooves',
		'is' => 'are',
		'knife' => 'knives',
		'leaf' => 'leaves',
		'life' => 'lives',
		'loaf' => 'loaves',
		'louse' => 'lice',
		'man' => 'men',
		'mouse' => 'mice',
		'ox' => 'oxen',
		'person' => 'people',
		'quiz' => 'quizzes',
		'scarf' => 'scarves',
		'self' => 'selves',
		'sheaf' => 'sheaves',
		'shelf' => 'shelves',
		'size' => 'sizes',
		'thief' => 'thieves',
		'tooth' => 'teeth',
		'was' => 'were',
		'wife' => 'wives',
		'woman' => 'women',
		'wolf' => 'wolves',

		// Plural ends in -i:
		'alumnus' => 'alumni',
		'bacillus' => 'bacilli',
		'cactus' => 'cacti',
		'focus' => 'foci',
		'stimulus' => 'stimuli',
		'focus' => 'foci',
		'octopus' => 'octopi',
		'radius' => 'radii',
		'stimulus' => 'stimuli',
		'terminus' => 'termini',

		// Plural ends in -ices:
		'appendix' => 'appendices',
		'index' => 'indeces',
		'matrix' => 'matrices',
		'vertex' => 'vertices',
		'vortex' => 'vortices',
		'apex' => 'apices',
		'cervix' => 'cervices',
		'axis' => 'axes',
		'testis' => 'testes',

		// Plural ends in -a
		'criterion' => 'criteria',
		'phenomenon' => 'phenomena',
		'automaton' => 'automata',

		// Plural ends in -ae
		'alga' => 'algae',
		'amoeba' => 'amoebae',
		'larva' => 'larvae',
		'formula' => 'formulae',
		'antenna' => 'antannae',
		'nebula' => 'nebulae',
		'vertebra' => 'vertebrae',
		'vita' => 'vitae',

		// Plural ends in -a:
		'corpus' => 'corpora',
		'genus' => 'genera',

		// Plural ends in -eaux:
		'bureau' => 'bureaux',
		'beau' => 'beaux',
		'portmanteau' => 'portmanteaux',
		'tableau' => 'tableaux',

		// Italian
		'libretto' => 'libretti',
		'tempo' => 'tempi',
		'virtuoso' => 'virtuosi',

		// Hebrew
		'cherub' => 'cherubim',
		'seraph' => 'seraphim',

		// Greek
		'schema' => 'schemata',

		// always plural
		'pants' => 'pants',
		'clothes' => 'clothes',
		'binoculars' => 'binoculars',
		'jeans' => 'jeans',
		'forceps' => 'forceps',
		'trousers' => 'trousers',
		'tongs' => 'tongs',
		'shorts' => 'shorts',
		'tweezers' => 'tweezers',
		'people' => 'people',
		'pajamas' => 'pajamas',
		'police' => 'police',
		'shorts' => 'shorts',
		'glasses' => 'glasses',
		'scissors' => 'scissors',
		'mathematics' => 'mathematics',
		'money' => 'money',
		'moose' => 'moose',
		'rice' => 'rice',

		// Aggregate Nouns
		'accomodations' => 'accomodations',
		'bread' => 'bread',
		'amends' => 'amends',
		'tea' => 'tea',
		'archives' => 'archives',
		'cheese' => 'cheese',
		'bowels' => 'bowels',
		'jam' => 'jam',
		'communications' => 'communications',
		'soup' => 'soup',
		'congratulations' => 'congratulations',
		'soap' => 'soap',
		'contents' => 'contents',
		'snow' => 'snow',
		'stairs' => 'stairs',
		'cotton' => 'cotton',
		'wood' => 'wood',
		'thanks' => 'thanks',
		'water' => 'water',
		'goods' => 'goods',
		'information' => 'information',
		'advice' => 'advice',
		'knowledge' => 'knowledge',
		'furniture' => 'furniture',
		'news' => 'news',
		'means' => 'means',
		'series' => 'series',
		'species' => 'species',
		'barracks' => 'barracks',
		'crossroads' => 'crossroads',
		'gallows' => 'gallows',
		'headquarters' => 'headquarters',

		// Nouns with the same form
		'salmon' => 'salmon',
		'trout' => 'trout',
		'deer' => 'deer',
		'sheep' => 'sheep',
		'swine' => 'swine',
		'offspring' => 'offspring',

		'my' => 'my'
	);

	/**
	 * Array of irregular form plural => singular
	 * @var array
	 */
	static public $singularIrregular = array(
		'people' => 'person'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		self::$singularIrregular = array_flip(self::$pluralIrregular);
	}

	/**
	 * Rules for convert singular to plural form
	 * @var array
	 */
	static public $pluralRules = array (
		'/sis$/i' => 'ses', // analysis -> analyses
		'/(ch|ss|sh|x|z|s)$/i' => '\1es', // box -> boxes
		'/([^aeiou])o$/i' => '\1oes', // echo -> echoes
		'/([^aeiou])y$/i' => '\1ies', // story -> stories
		'/(t|i)um$/i' => '\1a', // bacterium -> bacteria
		// last condition
		'/([a-rt-z])$/i' => '\1s' // horse -> horses
	);

	/**
	 * Get plural form by singular
	 * @param string $word word
	 * @param int $count
	 * @return string
	 */
	protected function _getPlural($word, $count = NULL)
	{
		// Irregular words
		if (isset(self::$pluralIrregular[$word]))
		{
			return self::$pluralIrregular[$word];
		}

		foreach (self::$pluralRules as $pattern => $replacement)
		{
			$word = preg_replace($pattern, $replacement, $word, 1, $replaceCount);

			if ($replaceCount)
			{
				return $word;
			}
		}

		return $word;
	}

	/**
	 * Rules for convert plural to singular form
	 * @var array
	 */
	static public $singularRules = array(
		'/(^analy)ses$/i' => '\1sis', // analyses -> analysis, but ipaddresses -> ipaddress
		//'/(ch|ss|sh|x|z|[^o][a-z]s)es$/i' => '\1', // boxes -> box, responses -> (resp(o)nse)s, (wareh(o)use)s -> warehouse
		'/(ch|ss|sh|[aieuo]x|z|[^o][ieu]s)es$/i' => '\1', // boxes -> box, responses -> (resp(o)nse)s, (wareh(o)use)s -> warehouse
		'/([^aeiou])oes$/i' => '\1o', // echoes -> echo
		'/([^aeiou])ies$/i' => '\1y', // stories -> story
		'/(t|i)a$/i' => '\1um', // bacteria -> bacterium
		'/(la|a)ses$/i' => '\1s', // aliases -> aliase
		// last condition
		'/([a-rt-z])s$/i' => '\1' // horses -> horse
	);

	/**
	 * Get singular form by plural
	 * @param string $word word
	 * @param int $count
	 * @return string
	 */
	protected function _getSingular($word, $count = NULL)
	{
		// Irregular words
		if (isset(self::$singularIrregular[$word]))
		{
			return self::$singularIrregular[$word];
		}

		foreach (self::$singularRules as $pattern => $replacement)
		{
			$word = preg_replace($pattern, $replacement, $word, 1, $replaceCount);

			if ($replaceCount)
			{
				return $word;
			}
		}

		return $word;
	}
	
	/**
	 * Number to str
	 * @param float $float
	 */
	protected function _num2str($float)
	{
		return '_num2str is undefined';
	}
}