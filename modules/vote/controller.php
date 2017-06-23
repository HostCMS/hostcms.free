<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Votes.
 *
 * @package HostCMS
 * @subpackage Vote
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Vote_Controller
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

	/**
	 * List of allowed object for comment
	 * @var array
	 */
	protected $_allowedTypes = array(
		'comment',
		'informationsystem_item',
		'shop_item'
	);

	/**
	 * Getting voted object by type
	 * @param string $type type name of voted object, e.g. 'comment'
	 * @param int $id object ID
	 * @return NULL|Core_Entity
	 */
	public function getVotedObject($type, $id)
	{
		$type = strtolower($type);
		if (in_array($type, $this->_allowedTypes))
		{
			$object = Core_Entity::factory($type)->find($id);

			return !is_null($object->id)
				? $object
				: NULL;
		}

		return NULL;
	}

	/**
	 * Getting rate by type and object ID
	 * @param string $type type name of voted object, e.g. 'comment'
	 * @param int $id object ID
	 * @return array 'likes', 'dislikes', 'rate'
	 */
	public function getRate($type, $id)
	{
		$aVotingStatistic = array('likes' => 0, 'dislikes' => 0, 'rate' => 0);

		$oObject = $this->getVotedObject($type, $id);

		if (is_null($oObject))
		{
			return $aVotingStatistic;
		}

		return $this->getRateByObject($oObject);
	}

	/**
	 * Getting rate by object
	 * @param Core_Entity $oObject
	 * @return array 'likes', 'dislikes', 'rate'
	 */
	public function getRateByObject(Core_Entity $oObject)
	{
		$aVotingStatistic = array('likes' => 0, 'dislikes' => 0, 'rate' => 0);

		$aVotes = $oObject->Votes->findAll();
		foreach($aVotes as $oVote)
		{
			$aVotingStatistic[$oVote->value < 0 ? 'dislikes' : 'likes'] += $oVote->value;
		}

		$aVotingStatistic['rate'] = $aVotingStatistic['likes'] + $aVotingStatistic['dislikes'];
		$aVotingStatistic['dislikes'] *= -1;

		return $aVotingStatistic;
	}
}