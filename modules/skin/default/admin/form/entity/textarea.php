<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Textarea extends Admin_Form_Entity
{
	/**
	 * Config
	 * @var array
	 */
	// protected $_init = NULL;

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'divAttr', // array
		'caption',
		'format', // array, массив условий форматирования
		'value', // идет в значение <textarea>
		'template_id', // ID макета для визуального редактора
		'syntaxHighlighter',
		'syntaxHighlighterMode',
		'syntaxHighlighterOptions',
		'wysiwygOptions',
		'wysiwygInline',
		'wysiwygMode',
	);

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'wysiwyg',
		'wysiwygOptions',
		'wysiwygInline',
		'wysiwygMode',
		'syntaxHighlighter',
		'syntaxHighlighterMode',
		'syntaxHighlighterOptions'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Combine
		$this->_skipProperties = array_combine($this->_skipProperties, $this->_skipProperties);

		$oCore_Html_Entity_Textarea = new Core_Html_Entity_Textarea();
		$this->_allowedProperties += $oCore_Html_Entity_Textarea->getAllowedProperties();

		// Свойства, исключаемые для <textarea>, добавляем в список разрешенных объекта
		$this->_allowedProperties += $this->_skipProperties;

		parent::__construct();

		$oCore_Registry = Core_Registry::instance();
		$iAdmin_Form_Count = $oCore_Registry->get('Admin_Form_Count', 0);
		$oCore_Registry->set('Admin_Form_Count', $iAdmin_Form_Count + 1);

		$this->wysiwygInline = FALSE;
		$this->wysiwygMode = 'full';

		$this->syntaxHighlighterMode = 'php';

		$this->id = $this->name = 'field_id_' . $iAdmin_Form_Count;
		$this->style('width: 100%')
			->rows(3);

		$this->class .= ' form-control';
		$this->divAttr = array('class' => 'form-group col-xs-12');
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Textarea.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Textarea.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		($this->wysiwyg || $this->syntaxHighlighter)
			&& $this->id = $windowId . '_' . $this->id;

		$aAttr = $this->getAttrsString();

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		$aDivAttr = array();

		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		?><span class="caption"><?php echo $this->caption?></span><?php

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		/*$this->_init = is_null($this->wysiwygOptions)
			? Core_Config::instance()->get('core_wysiwyg')
			: $this->wysiwygOptions;*/

		$tagName = $this->wysiwygInline
			? 'div'
			: 'textarea';

		$this->_format();

		?><<?php echo $tagName?> <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars((string) $this->value)?></<?php echo $tagName?>><?php

		// $this->_format();

		if (count($this->_children))
		{
			// Могут быть дочерние элементы элементы
			$this->executeChildren();
			?></div><?php
		}

		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}

	protected function _format()
	{
		if ($this->wysiwyg)
		{
			if (!defined('USE_WYSIWYG') || USE_WYSIWYG)
			{
				$oWysiwyg = Core_Entity::factory('Wysiwyg')->getDefault();

				if ($oWysiwyg)
				{
					$oWysiwyg_Handler = Wysiwyg_Handler::instance($oWysiwyg);
					$oWysiwyg_Handler->init($this);
				}
			}
		}
		elseif ($this->syntaxHighlighter)
		{
			if (Core::moduleIsActive('syntaxhighlighter'))
			{
				$oSyntaxhighlighter = Core_Entity::factory('Syntaxhighlighter')->getDefault();

				if ($oSyntaxhighlighter)
				{
					$oSyntaxhighlighter_Handler = Syntaxhighlighter_Handler::instance($oSyntaxhighlighter);
					$oSyntaxhighlighter_Handler->init($this);
				}
			}
		}
		else
		{
			$this->_showFormat();
		}
	}
}