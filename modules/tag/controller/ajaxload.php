<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 *
 * Контроллер загрузки списка тегов(меток), соответствующих фильтру
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Controller_Ajaxload extends Admin_Form_Action_Controller
{
	/**
	 * Tag filter
	 * @var string
	 */
	protected $_tagFilter = null;
	/**
	 * Set tag filter
	 * @param string $tagFilter tag filter
	 * @return self
	 */
	public function query($tagFilter = '')
	{
		$this->_tagFilter = $tagFilter;
		return $this;
	}
	/**
	 * Execute business logic
	 * @param string $operation operation
	 */
	public function execute($operation = NULL)
	{
		$aJSON = array();

		if (strlen($this->_tagFilter))
		{
			$oTags = Core_Entity::factory('Tag');
			$oTags->queryBuilder()
				->where('tags.name', 'LIKE', '%' . $this->_tagFilter . '%')
				->limit(10)
				->clearOrderBy()
				->orderBy('tags.name', 'ASC');

			$aTags = $oTags->findAll(FALSE);

			foreach ($aTags as $oTag)
			{
				$sParents = $oTag->Tag_Dir->dirPathWithSeparator();

				$postfix = strlen($sParents) ? ' [' . $sParents . ']' : '';

				$aJSON[] = array(
					'id' => $oTag->name,
					'text' => $oTag->name . $postfix,
				);
			}
		}

		Core::showJson($aJSON);
	}
}