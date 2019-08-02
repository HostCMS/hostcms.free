<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * dropdownlist entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Dropdownlist extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'disabled'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'options', // array
		'value'
	);

	/**
	 * Object has unlimited number of properties
	 * @var boolean
	 */
	protected $_unlimitedProperties = TRUE;

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		echo PHP_EOL;

		?>
		<div class="btn-group">
		<?php
		$aOptions = $this->options;

		if (is_array($aOptions) && count($aOptions))
		{
			$this->class = 'dropdown-menu form-element ' . $this->class;

			$aAttr = $this->getAttrsString();

			// Индекс элемента массива, который показываем как выбранное значение
			$indexValueItem = isset($aOptions[$this->value])
				? $this->value
				: key($aOptions); // Get First Key

			// Получаем информацию о элементе - значение, ико, цвет
			$aItemInfo = $this->_getItemInfo($indexValueItem);

			?>
			<a data-toggle="dropdown" style="color: <?php echo $aItemInfo['color'] ?>" href="javascript:void(0);" aria-expanded="false">
				<i class="<?php echo $aItemInfo['icon']?>"></i><?php echo htmlspecialchars($aItemInfo['value']) . (!$this->disabled ? '<i class="fa fa-angle-down icon-separator-left"></i>' : '')?>
			</a>
			<?php
			if (!$this->disabled)
			{
				?>
				<ul <?php echo implode(' ', $aAttr)?>>
				<?php

				foreach ($aOptions as $key => $value)
				{
					// Получаем информацию о элементе - значение, ико, цвет
					$aItemInfo = $this->_getItemInfo($key);
					?>
					<li id="<?php echo htmlspecialchars($key)?>" <?php echo $indexValueItem == $key ? 'selected="selected"' : ''?>>
						<a href="javascript:void(0);" style="color: <?php echo htmlspecialchars($aItemInfo['color'])?>"><i class="<?php echo htmlspecialchars($aItemInfo['icon'])?>"></i><?php echo htmlspecialchars($aItemInfo['value'])?></a>
					</li>
					<?php
				}
				?>
				</ul>
			<?php
			}
		}
		if (!is_null($this->name))
		{
			?>
			<input type="hidden" name="<?php echo $this->name?>" value="<?php echo htmlspecialchars($indexValueItem)?>">
			<?php
		}
		?>
		</div>
		<?php
	}

	protected function _getItemInfo($itemIndex)
	{
		$aOptions = $this->options;
		if (!isset($aOptions[$itemIndex]))
		{
			return NULL;
		}

		$aItemInfo = is_array($aOptions[$itemIndex])
			? $aOptions[$itemIndex]
			: array('value' => $aOptions[$itemIndex]);

		$aItemInfo += array(
			'icon' => 'fa fa-circle fa-dropdownlist',
			'color' => '#aebec4',
			'value' => NULL
		);

		return $aItemInfo;
	}
}