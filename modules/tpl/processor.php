<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * TPL.
 *
 * <code>
 * $return = Tpl_Processor::instance()
 *	->tpl('TplName')
 *	->process();
 *
 * echo $return;
 * </code>
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Tpl_Processor
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * List of children entities
	 * @var array
	 */
	protected $_entities = array();

	/**
	 * Variables/objects to the TPL-template
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * @var Smarty
	 */
	protected $_smarty = NULL;

	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		require_once(CMS_FOLDER . 'modules/tpl/smarty/Smarty.class.php');

		$this->_smarty = new Smarty();

		$this->_smarty
			->setTemplateDir(CMS_FOLDER . 'hostcmsfiles/tpl')
			->setCompileDir(CMS_FOLDER . TMP_DIR . 'smarty/templates_c')
			->setCacheDir(CMS_FOLDER . TMP_DIR . 'smarty/cache');

		$this->_smarty->registerPlugin('modifier', 'hideOutput', array(__CLASS__, 'hideOutput'));


		$this->_smarty->registerPlugin('function', 'getTemplateVars', array(__CLASS__, 'getTemplateVars'));

		$this->_smarty->loadPlugin('smarty_compiler_switch');
		$this->_smarty->registerFilter('post', 'smarty_postfilter_switch');
		//$this->_smarty->enableSecurity();

		// escape php-tags as entities
		//$this->_smarty->php_handling = Smarty::PHP_QUOTE;
	}

	/**
	 * Set entities
	 *
	 * @param array $entities
	 * @return self
	 */
	public function entities(array $entities)
	{
		$this->_entities = $entities;
		return $this;
	}

	/**
	 * Set vars
	 *
	 * @param array $vars
	 * @return self
	 */
	public function vars(array $vars)
	{
		$this->_vars = $vars;
		return $this;
	}

	/**
	 * Execute processor
	 * @return false|string
     * @hostcms-event Tpl_Processor.onBeforeProcess
	 * @hostcms-event Tpl_Processor.onAfterProcess
	 */
	public function process()
	{
		Core_Event::notify('Tpl_Processor.onBeforeProcess', $this);

		$this->_smarty
			// clears assigned config variables
			->clearConfig()
			// clears the values of all assigned variables
			->clearAllAssign();

		// Config
		$lng = Core::getLng();
		$configPath = $this->_tpl->getLngConfigPath($lng);
		if (Core_File::isFile($configPath))
		{
			// load config variables and assign them
			$this->_smarty->configLoad($configPath);
		}

		// Entities
		foreach ($this->_entities as $oEntity)
		{
			if (isset($oEntity->name) && isset($oEntity->value))
			{
				$this->_smarty->assign($oEntity->name, $oEntity->value);
			}
		}

		// Vars
		foreach ($this->_vars as $key => $value)
		{
			$this->_smarty->assign($key, $value);
		}

		$return = $this->_smarty->fetch($this->_tpl->getTplFilePath());

		Core_Event::notify('Tpl_Processor.onAfterProcess', $this);

		return $return;
	}

	/**
	 * Hide Function Output
	 * e.g. {$object->xyz()|hideOutput}
	 */
	static public function hideOutput($str)
	{
		return '';
	}

	/**
	 * getTemplateVars
	 */
	static public function getTemplateVars($params, $smarty)
	{
		$aVars = $smarty->getTemplateVars();
		if (!isset($aVars['templateVars']))
		{
			$smarty->assign('templateVars', $aVars);
		}
	}

	/**
	 * Clear entire compile directory
	 * @return self
	 */
	public function clearCompiledTemplate()
	{
		$this->_smarty->clearCompiledTemplate();
		return $this;
	}

	/**
	 * XSL
	 * @var Tpl_Model
	 */
	protected $_tpl = NULL;

    /**
     * Set XSL
     * @param Tpl_Model $oTpl
     * @return self
     */
	public function tpl(Tpl_Model $oTpl)
	{
		$this->_tpl = $oTpl;
		return $this;
	}

	/**
	 * Get TPL
	 * @return Tpl_Model
	 */
	public function getTpl()
	{
		return $this->_tpl;
	}

	/**
	 * Clear XSL
	 * @return self
	 */
	public function clear()
	{
		$this->_tpl = $this->_xsl = NULL;
		return $this;
	}

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

	/**
	 * Format TPL variables
	 */
	public function format()
	{
		$aVars = $this->_smarty->getTemplateVars();
		?>
		<ul class="tpl-template-variables">
			<?php
			foreach ($aVars as $key => $variable)
			{
				?>
				<li>
					<span class="bold"><?php echo htmlspecialchars($key)?>: </span>
					<?php
						$type = gettype($variable);
						switch ($type)
						{
							case 'boolean':
								$value = "(boolean) " . ($variable === TRUE ? 'true' : 'false');
							break;
							case 'NULL':
								$value = "NULL";
							break;
							case 'object':
								$value = "(object) " . get_class($variable);
							break;
							case 'array':
								$value = "(array), " . Core::_('Tpl.panel_tpl_array', count($variable));
							break;
							case 'resource':
								$value = "(resource)";
							break;
							default:
								$value = "({$type}), " . $variable;
						}
					?>
					<span><?php echo htmlspecialchars($value)?></span>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
}