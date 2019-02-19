<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information system.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Controller
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

	static public function showGroupButton()
	{
		$html = '
			<script>
				var lastFocusedGroup;

				$(function(){

					$("textarea[name^=\'seo_group_\']").on("focus", function() {

						lastFocusedGroup = $(document.activeElement);
					});
				})

			</script>
			<div class="btn-group pull-right">
				<a class="btn btn-sm btn-default"><i class="fa fa-plus"></i></a>
				<a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-default" role="menu">
					<li class="disabled">
						<a class="bold">' . Core::_("Informationsystem.seo_template_informationsystem") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{informationsystem.name\}\')">' . Core::_("Informationsystem.seo_template_informationsystem_name") . '</a>
					</li>
					<li class="divider"></li>
					<li class="disabled">
						<a class="bold">' . Core::_("Informationsystem.seo_template_group") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.name\}\')">' . Core::_("Informationsystem.seo_template_group_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.description\}\')">' . Core::_("Informationsystem.seo_template_group_description") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.propertyValue ID\}\')">' . Core::_("Informationsystem.seo_template_property_value") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.groupPathWithSeparator \x22 → \x22 1\}\')">' . Core::_("Informationsystem.seo_template_group_path") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{this.pageNumber \x22, ' . Core::_("Informationsystem.seo_template_group_page") . ' %d\x22\}\')">' . Core::_("Informationsystem.seo_template_group_page_number") . '</a>
					</li>
				</ul>
			</div>
		';

		return $html;
	}

	static public function showItemButton()
	{
		$html = '
			<script>
				var lastFocusedItem;

				$(function(){

					$("textarea[name^=\'seo_item_\']").on("focus", function() {
					  lastFocusedItem = $(document.activeElement);
					});
				});

			</script>
			<div class="btn-group pull-right">
				<a class="btn btn-sm btn-default"><i class="fa fa-plus"></i></a>
				<a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-default" role="menu">
					<li class="disabled">
						<a class="bold">' . Core::_("Informationsystem.seo_template_informationsystem") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{informationsystem.name\}\')">' . Core::_("Informationsystem.seo_template_informationsystem_name") . '</a>
					</li>
					<li class="divider"></li>
					<li class="disabled">
						<a class="bold">' . Core::_("Informationsystem.seo_template_group") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.name\}\')">' . Core::_("Informationsystem.seo_template_group_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.description\}\')">' . Core::_("Informationsystem.seo_template_group_description") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.propertyValue ID\}\')">' . Core::_("Informationsystem.seo_template_property_value") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.groupPathWithSeparator \x22 → \x22 1\}\')">' . Core::_("Informationsystem.seo_template_group_path") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{this.pageNumber \x22, ' . Core::_("Informationsystem.seo_template_group_page") . ' %d\x22\}\')">' . Core::_("Informationsystem.seo_template_group_page_number") . '</a>
					</li>
					<li class="divider"></li>
					<li class="disabled">
						<a class="bold">' . Core::_("Informationsystem.seo_template_item") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.name\}\')">' . Core::_("Informationsystem.seo_template_item_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.description\}\')">' . Core::_("Informationsystem.seo_template_item_description") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.text\}\')">' . Core::_("Informationsystem.seo_template_item_text") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.propertyValue ID\}\')">' . Core::_("Informationsystem.seo_template_property_value") . '</a>
					</li>
				</ul>
			</div>
		';

		return $html;
	}
}