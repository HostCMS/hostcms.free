<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Controller extends Admin_Form_Controller
{
	/**
	 * Count of elements on page
	 * @var array
	 */
	protected $_onPage = array (10 => 10, 20 => 20, 30 => 30, 50 => 50, 100 => 100, 500 => 500, 1000 => 1000);

	/**
	 * Is showing filter necessary
	 * @var boolean
	 */
	protected $_showFilter = FALSE;

	/**
	 * Apply form settings
	 * @return self
	 */
	public function formSettings()
	{
		parent::formSettings();

		// Filter: Main Option
		if (!is_null(Core_Array::getPost('changeFilterStatus')))
		{
			if ($this->_oAdmin_Form_Setting)
			{
				$this->_oAdmin_Form_Setting->filter = json_encode(
					array('show' => intval(Core_Array::getPost('show')))
						+ (is_array($this->filterSettings)
							? $this->filterSettings
							: array())
				);
				$this->_oAdmin_Form_Setting->save();

				$aJSON = array('message' => 'OK');
			}
			else
			{
				$aJSON = array('message' => 'Error');
			}

			Core::showJson($aJSON);
		}

		// Filter: Fields
		if (!is_null(Core_Array::getPost('changeFilterField')))
		{
			if ($this->_oAdmin_Form_Setting)
			{
				$tabs = Core_Array::get($this->filterSettings, 'tabs', array());

				// Main Tab should be first
				if (!isset($tabs['main']))
				{
					$tabs['main'] = array();
				}

				$tab = strval(Core_Array::getPost('tab'));
				$field = strval(Core_Array::getPost('field'));
				$show = intval(Core_Array::getPost('show'));

				$tabs[$tab]['fields'][$field]['show'] = $show;

				$this->filterSettings['tabs'] = $tabs;

				$this->_oAdmin_Form_Setting->filter = json_encode($this->filterSettings);
				$this->_oAdmin_Form_Setting->save();

				$aJSON = array('message' => 'OK');
			}
			else
			{
				$aJSON = array('message' => 'Error');
			}

			Core::showJson($aJSON);
		}

		// Filter: Save As
		if (!is_null(Core_Array::getPost('saveFilterAs')))
		{
			if ($this->_oAdmin_Form_Setting)
			{
				$tabs = Core_Array::get($this->filterSettings, 'tabs', array());

				// Main Tab should be first
				if (!isset($tabs['main']))
				{
					$tabs['main'] = array();
				}

				$aNewTab = array(
					'caption' => Core_Array::getPost('filterCaption')
				);

				$bCreated = FALSE;
				$aAdmin_Form_Fields = $this->_Admin_Form->Admin_Form_Fields->findAll();
				foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
				{
					if ($oAdmin_Form_Field->allow_filter || $oAdmin_Form_Field->view == 1)
					{
						$field = $oAdmin_Form_Field->name;

						$value = isset($_POST['topFilter_' . $oAdmin_Form_Field->id])
							? $_POST['topFilter_' . $oAdmin_Form_Field->id]
							: NULL;

						if (strlen($value))
						{
							$bCreated = TRUE;
							$aNewTab['fields'][$field]['show'] = 1;
							$aNewTab['fields'][$field]['value'] = $value;
						}
						else
						{
							$aNewTab['fields'][$field]['show'] = 0;
						}
					}
				}

				if ($bCreated)
				{
					$tabs[] = $aNewTab;

					$this->filterSettings['tabs'] = $tabs;

					$this->_oAdmin_Form_Setting->filter = json_encode($this->filterSettings);
					$this->_oAdmin_Form_Setting->save();

					end($tabs);
					// Change current filter
					$this->filterId(key($tabs));
					/*$aJSON = array('message' => 'OK', 'id' => key($tabs));*/
				}
				else
				{
					//$aJSON = array('message' => 'Error, empty conditions');
				}
			}
			else
			{
				//$aJSON = array('message' => 'Error');
			}

			//Core::showJson($aJSON);
		}

		// Filter: Save
		if (!is_null(Core_Array::getPost('saveFilter')))
		{
			if ($this->_oAdmin_Form_Setting)
			{
				$tabs = Core_Array::get($this->filterSettings, 'tabs', array());

				// _filterId
				$tabName = Core_Array::getPost('filterId');

				$aAdmin_Form_Fields = $this->_Admin_Form->Admin_Form_Fields->findAll();
				foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
				{
					if ($oAdmin_Form_Field->allow_filter || $oAdmin_Form_Field->view == 1)
					{
						$field = $oAdmin_Form_Field->name;

						$value = Core_Array::getPost('topFilter_' . $oAdmin_Form_Field->id);

						if (strlen($value))
						{
							$tabs[$tabName]['fields'][$field]['show'] = 1;
							$tabs[$tabName]['fields'][$field]['value'] = $value;
						}
						else
						{
							$tabs[$tabName]['fields'][$field]['show'] = 0;
						}
					}
				}

				$this->filterSettings['tabs'] = $tabs;

				$this->_oAdmin_Form_Setting->filter = json_encode($this->filterSettings);
				$this->_oAdmin_Form_Setting->save();

				end($tabs);
				$aJSON = array('message' => 'OK');
			}
			else
			{
				$aJSON = array('message' => 'Error');
			}

			Core::showJson($aJSON);
		}

		// Filter: Delete
		if (!is_null(Core_Array::getPost('deleteFilter')))
		{
			if ($this->_oAdmin_Form_Setting)
			{
				$tabs = Core_Array::get($this->filterSettings, 'tabs', array());

				$tabName = Core_Array::getPost('filterId');

				if (isset($tabs[$tabName]))
				{
					unset($tabs[$tabName]);

					$this->filterSettings['tabs'] = $tabs;

					$this->_oAdmin_Form_Setting->filter = json_encode($this->filterSettings);
					$this->_oAdmin_Form_Setting->save();
				}

				$aJSON = array('message' => 'OK');
			}
			else
			{
				$aJSON = array('message' => 'Error');
			}

			Core::showJson($aJSON);
		}

		return $this;
	}

	/**
	 * Show items count selector
	 */
	public function pageSelector()
	{
		$sCurrentValue = $this->limit;

 		$path = Core_Str::escapeJavascriptVariable($this->getPath());
 		$view = Core_Str::escapeJavascriptVariable($this->view);
		$windowId = Core_Str::escapeJavascriptVariable($this->getWindowId());
		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $this->additionalParams)
		);

		?><label><?php

		Core::factory('Core_Html_Entity_Select')
			->class('form-control input-sm')
			->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', limit: this.options[this.selectedIndex].value, view: '{$view}', windowId : '{$windowId}'}); return false")
			->options($this->_onPage)
			->value($sCurrentValue)
			->execute();

		?></label><?php
	}

	/**
	 * Show children elements
	 * @return self
	 */
	public function showFormMenus()
	{
		// Связанные с формой элементы (меню, строка навигации и т.д.)
		foreach ($this->_children as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
			{
				$oAdmin_Form_Entity->execute();
			}
		}

		return $this;
	}

	/**
	 * Show Change View buttonset
	 * @return self
	 */
	public function showChangeViews()
	{
		if (count($this->viewList) > 1)
		{
			?><div class="btn-group btn-view-selector pull-left"><?php
			foreach ($this->viewList as $viewName => $className)
			{
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, NULL, NULL, NULL, $viewName);

				?><a id="<?php echo htmlspecialchars($viewName)?>" onclick="<?php echo $onclick?>" class="btn btn-default <?php if ($viewName == $this->view) { echo 'active'; }?>" data-view="<?php echo htmlspecialchars($viewName)?>"><?php echo Core::_('Admin_Form.' . $viewName)?></a><?php
			}
			?></div><?php

			/*if (1==0)
			{
			?><div class="view-selector pull-left"><?php
			foreach ($this->viewList as $viewName => $className)
			{
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, NULL, NULL, NULL, $viewName);

				?><input type="radio" onclick="<?php echo $onclick?>" id="<?php echo htmlspecialchars($viewName)?>" data-view="<?php echo htmlspecialchars($viewName)?>" name="selector" <?php if ($viewName == $this->view) { echo 'checked="checked"'; }?> /><label for="<?php echo htmlspecialchars($viewName)?>"><?php echo Core::_('Admin_Form.' . $viewName)?></label><?php
			}
			?></div><?php
			}*/
		}

		return $this;
	}

	protected $_pageNavigationDelta = 2;

	/**
	 * Показ строки ссылок
	 * @return self
	 */
	public function pageNavigation()
	{
		$total_count = $this->getTotalCount();
		$total_page = $total_count / $this->limit;

		// Округляем в большую сторону
		if ($total_count % $this->limit != 0)
		{
			$total_page = intval($total_page) + 1;
		}

		// Отображаем строку ссылок, если общее число страниц больше 1.
		if ($total_page > 1)
		{
			$this->current > $total_page && $this->current = $total_page;

			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
				->class('dataTables_paginate paging_bootstrap');

			$oCore_Html_Entity_Ul = Core::factory('Core_Html_Entity_Ul')
				->class('pagination');

			$oCore_Html_Entity_Div->add($oCore_Html_Entity_Ul);

			// Ссылка на предыдущую страницу
			$page = $this->current - 1 ? $this->current - 1 : 1;

			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');
			$oCore_Html_Entity_Li
				->class('prev' . ($this->current == 1 ? ' disabled' : ''))
				->add(
					$oCore_Html_Entity_A
						->id('id_prev')
						->add(Admin_Form_Entity::factory('Code')
							->html('<i class="fa fa-angle-left"></i>')
						)
				);

			if ($this->current != 1)
			{
				$oCore_Html_Entity_A
					->href($this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $page))
					->onclick($this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $page));
			}

			// Определяем номер ссылки, с которой начинается строка ссылок.
			$link_num_begin = ($this->current - $this->_pageNavigationDelta < 1)
				? 1
				: $this->current - $this->_pageNavigationDelta;

			// Определяем номер ссылки, которой заканчивается строка ссылок.
			$link_num_end = $this->current + $this->_pageNavigationDelta;
			$link_num_end > $total_page && $link_num_end = $total_page;

			// Определяем число ссылок выводимых на страницу.
			$count_link = $link_num_end - $link_num_begin + 1;

			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');
			$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);

			if ($this->current == 1)
			{
				$oCore_Html_Entity_Li->class('active');

				$oCore_Html_Entity_A
					->class('current')
					->value($link_num_begin);
			}
			else
			{
				$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, 1);
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, 1);

				$oCore_Html_Entity_A
					->href($href)
					->onclick($onclick)
					//->class('page_link')
					->value(1);

				// Выведем … со ссылкой на 2-ю страницу, если показываем с 3-й
				if ($link_num_begin > 1)
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, 2);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, 2);

					$oCore_Html_Entity_A
						->href($href)
						->onclick($onclick)
						//->class('page_link')
						->value('…');
				}
			}

			// Страница не является первой и не является последней.
			for ($i = 1; $i < $count_link - 1; $i++)
			{
				$link_number = $link_num_begin + $i;

				$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
				$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);


				$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A')
					->value($link_number);
				$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);

				if ($link_number == $this->current)
				{
					// Страница является текущей
					$oCore_Html_Entity_Li->class('active');
				}
				else
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $link_number);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $link_number);

					$oCore_Html_Entity_A
						->href($href)
						->onclick($onclick);
				}
			}

			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			// Если последняя страница является текущей
			if ($this->current == $total_page)
			{
				$oCore_Html_Entity_Li->class('active');

				$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A')
					->value($total_page);

				$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
			}
			else
			{
				// Выведем … со ссылкой на предпоследнюю страницу
				if ($link_num_end < $total_page)
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $total_page - 1);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $total_page - 1);

					$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A')
						->href($href)
						->onclick($onclick)
						->value('…');

					$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
				}

				$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $total_page);
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $total_page);

				$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');

				// Последняя страница не является текущей
				$oCore_Html_Entity_A
					->href($href)
					->onclick($onclick)
					->value($total_page);

				$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
				$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);
				$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
			}

			// Формируем скрытые ссылки навигации для перехода по Ctrl + стрелка
			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');

			// Ссылка на следующую страницу
			$page = $this->current + 1 ? $this->current + 1 : 1;
			$oCore_Html_Entity_Li
				->class('next' . ($this->current == $total_page ? ' disabled' : ''))
				->add(
					$oCore_Html_Entity_A
						->id('id_next')
						->add(Admin_Form_Entity::factory('Code')
								->html('<i class="fa fa-angle-right"></i>')
							)
				);

			if ($this->current != $total_page)
			{
				$oCore_Html_Entity_A
					->href($this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $page))
					->onclick($this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $page));
			}

			$oCore_Html_Entity_Div->execute();
		}

		return $this;
	}

	public function getTitleEditIcon($href, $onclick, $class = 'fa fa-pencil-square-o h5-edit-icon palegreen')
	{
		// .attr("onclick", "' . $onclick . '")
		return Admin_Form_Entity::factory('Code')
			->html('
				<script>
					$(\'h5.row-title\').append(
						$("<a>")
							.attr("href", "' . $href . '")
							.attr("onclick", "' . Core_Str::escapeJavascriptVariable($onclick) . '")
							.append(\'<i class="' . htmlspecialchars($class) . '"></i>\')
						);
				</script>
		');
	}

	public function getTitlePathIcon($href, $class = 'fa fa-external-link h5-edit-icon azure')
	{
		return Admin_Form_Entity::factory('Code')
			->html('
				<script>
					$(\'h5.row-title\').append(
						$("<a>")
							.attr("href", "' . $href . '")
							.attr("target", "_blank")
							.append(\'<i class="' . htmlspecialchars($class) . '"></i>\')
						);
				</script>
		');
	}
}