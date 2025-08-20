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
class Skin_Bootstrap_Admin_Form_Controller extends Admin_Form_Controller
{
	/**
	 * Count of elements on page
	 * @var array
	 */
	protected $_onPage = array(10 => 10, 20 => 20, 30 => 30, 50 => 50, 100 => 100, 500 => 500, 1000 => 1000);

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
		Core_Html_Entity::factory('Select')
			->class('form-control input-sm admin-page-selector')
			->onchange("mainFormLocker.unlock(); $.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', limit: this.options[this.selectedIndex].value, view: '{$view}', windowId : '{$windowId}'}); return false")
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
	 * Show children elements
	 * @return self
	 */
	public function showFormBreadcrumbs()
	{
		// Связанные с формой элементы (меню, строка навигации и т.д.)
		foreach ($this->_children as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs)
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
		$viewList = $this->viewList;
		if (count($viewList) > 1)
		{
			($this->view == '' || !isset($viewList[$this->view]))
				&& $this->view = key($viewList);

			?><div class="btn-group btn-view-selector pull-left"><?php
			foreach ($viewList as $viewName => $className)
			{
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, NULL, NULL, NULL, $viewName);

				?><a id="<?php echo htmlspecialchars($viewName)?>" onclick="<?php echo $onclick?>" class="btn btn-default <?php if ($viewName == $this->view) { echo 'active'; }?>" data-view="<?php echo htmlspecialchars($viewName)?>"><?php
					switch ($viewName)
					{
						case 'list':
							?><i class="fa fa-bars"></i><?php
						break;
						case 'kanban':
							?><i class="fa fa-align-left fa-rotate-90"></i><?php
						break;
					}
				?><span class="hidden-xxs hidden-xs"><?php echo Core::_('Admin_Form.' . $viewName)?></span></a><?php
			}
			?></div><?php
		}

		return $this;
	}

	/**
	 * Page navigation delta
	 * @var integer
	 */
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

			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->class('dataTables_paginate paging_bootstrap');

			$oCore_Html_Entity_Ul = Core_Html_Entity::factory('Ul')
				->class('pagination pull-left');

			$oCore_Html_Entity_Div->add($oCore_Html_Entity_Ul);

			// Ссылка на предыдущую страницу
			$page = $this->current - 1 ? $this->current - 1 : 1;

			$oCore_Html_Entity_Li = Core_Html_Entity::factory('Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core_Html_Entity::factory('A');
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

			$oCore_Html_Entity_Li = Core_Html_Entity::factory('Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core_Html_Entity::factory('A');

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
					// Добавляем "1"
					$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);

					// Заменяем "1" на "..."
					$oCore_Html_Entity_A = Core_Html_Entity::factory('A');
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, 2);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, 2);

					$oCore_Html_Entity_A
						->href($href)
						->onclick($onclick)
						//->class('page_link')
						->value('…');
				}
			}

			$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);

			// Страница не является первой и не является последней.
			for ($i = 1; $i < $count_link - 1; $i++)
			{
				$link_number = $link_num_begin + $i;

				$oCore_Html_Entity_Li = Core_Html_Entity::factory('Li');
				$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);


				$oCore_Html_Entity_A = Core_Html_Entity::factory('A')
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

			$oCore_Html_Entity_Li = Core_Html_Entity::factory('Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			// Если последняя страница является текущей
			if ($this->current == $total_page)
			{
				$oCore_Html_Entity_Li->class('active');

				$oCore_Html_Entity_A = Core_Html_Entity::factory('A')
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

					$oCore_Html_Entity_A = Core_Html_Entity::factory('A')
						->href($href)
						->onclick($onclick)
						->value('…');

					$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
				}

				$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $total_page);
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $total_page);

				$oCore_Html_Entity_A = Core_Html_Entity::factory('A');

				// Последняя страница не является текущей
				$oCore_Html_Entity_A
					->href($href)
					->onclick($onclick)
					->value($total_page);

				$oCore_Html_Entity_Li = Core_Html_Entity::factory('Li');
				$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);
				$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
			}

			// Формируем скрытые ссылки навигации для перехода по Ctrl + стрелка
			$oCore_Html_Entity_Li = Core_Html_Entity::factory('Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core_Html_Entity::factory('A');

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

			$sHref = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, '');
			$sOnclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, '');

			$oCore_Html_Entity_Li = Core_Html_Entity::factory('Li')
				->class('page-selector-wrap')
				->add(
					Admin_Form_Entity::factory('Code')
						->html('<span class="page-selector-show-button">№</span>
								<div class="page-selector input-group input-group-xs hide">
									<input type="text" class="form-control input-xs">
									<span class="input-group-btn">
										<a href="' . $sHref . '" onclick="' . $sOnclick . '" class="btn btn-xs btn-default icon-only"><i class="fa fa-caret-right success circular"></i></a>
									</span>
								</div>
						')
				);
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_Div->execute();
		}

		return $this;
	}

	/**
	 * Get title edit icon
	 * @param string $href
	 * @param string $onclick
	 * @param string $class
	 * @return Admin_Form_Entity
	 */
	public function getTitleEditIcon($href, $onclick, $class = 'fa fa-pencil-square-o h5-edit-icon palegreen', $selector = 'h5.row-title', $target = "")
	{
		// .attr("onclick", "' . $onclick . '")
		return Admin_Form_Entity::factory('Code')
			->html('
				<script>
					$("' . $selector . ' > a").remove();
					$("' . $selector . '").append(
						$("<a>")
							.attr("target", "' . $target . '")
							.attr("href", "' . $href . '")
							.attr("onclick", "' . Core_Str::escapeJavascriptVariable($onclick) . '")
							.append(\'<i class="' . htmlspecialchars($class) . '"></i>\')
						);
				</script>
		');
	}

	/**
	 * Get title path icon
	 * @param string $href
	 * @param string $class
	 * @return Admin_Form_Entity
	 */
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