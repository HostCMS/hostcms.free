<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * dropdownlist entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Dropdownlist extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'options',
		'name',
		'disabled'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
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

			?><a data-toggle="dropdown" style="color: <?php echo $aItemInfo['color'] ?>" href="javascript:void(0);" aria-expanded="false"><?php
				if ($aItemInfo['icon'] != '')
				{
					?><i class="<?php echo $aItemInfo['icon']?>"></i><?php
				}

				echo htmlspecialchars($aItemInfo['value']) . (!$this->disabled ? '<i class="fa fa-angle-down icon-separator-left"></i>' : '')?>
			</a>
			<?php
			if (!$this->disabled)
			{
				?>
				<ul <?php echo implode(' ', $aAttr)?>>
				<?php

				foreach ($aOptions as $key => $value)
				{
					// Получаем информацию о элементе - значение, ico, цвет
					$aItemInfo = $this->_getItemInfo($key);

					$marginLeft = isset($aItemInfo['level']) && $aItemInfo['level']
						? " margin-left: " . ($aItemInfo['level'] * 5) . "px;"
						: '';
					?>
					<li id="<?php echo htmlspecialchars($key)?>"<?php echo $indexValueItem == $key ? ' selected="selected"' : ''?><?php echo $aItemInfo['class'] != '' ? ' class="' . htmlspecialchars($aItemInfo['class']) . '"' : ''?>>
						<a href="javascript:void(0);" style="color: <?php echo htmlspecialchars($aItemInfo['color'])?>;<?php echo $marginLeft?>"><?php
						if ($aItemInfo['icon'] != '')
						{
							?><i class="<?php echo htmlspecialchars($aItemInfo['icon'])?>"></i><?php
						}

						echo htmlspecialchars($aItemInfo['value'])?></a>
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

	/**
	 * Get item info
	 * @param int $itemIndex
	 * @return array|NULL
	 * @ignore
	 */
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
			'value' => NULL,
			'class' => NULL,
			'level' => 0
		);

		return $aItemInfo;
	}
}