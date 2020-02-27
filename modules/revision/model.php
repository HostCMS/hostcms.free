<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision_Model
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Revision_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $name = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'entity_id';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Rollback Revision
	 * @return self
	 */
	public function rollback()
	{
		$oModel = Core_Entity::factory($this->model, $this->entity_id);
		$oModel->rollbackRevision($this->id);
		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function userBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oUser = $this->User;

		return '<span class="badge badge-hostcms badge-square">' . htmlspecialchars(
				!is_null($oUser->id) ? $oUser->login : 'Unknown User'
			) . '</span>';
	}

	protected function _printJson($value)
	{
		if (is_array($value))
		{
			foreach($value as $key => $tmp)
			{
				?><div class="row"><div class="col-xs-4 semi-bold"><?php
				echo htmlspecialchars($key) . ': ';
				?></div><div class="col-xs-8 small"><?php
				$this->_printJson($tmp);
				?></div></div><?php
			}
		}
		elseif (is_string($value) || is_numeric($value))
		{
			echo '<span class="pre-wrap">' . htmlspecialchars($value) . '</span>';
		}
		elseif (is_object($value))
		{
			echo $value;
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oRevision = Core_Entity::factory('Revision', $this->id);

		$aValue = json_decode($oRevision->value, TRUE);

		?><div id="revision<?php echo $this->id?>" class="hidden"><?php
		$this->_printJson($aValue);
		?></div><?php

		?>
		<script>
		$(function() {
			$('a#revision<?php echo $this->id?>').on('click', function (){
				var dialog = bootbox.dialog({
					title: '<?php echo $this->name?> <?php echo $this->datetime?>',
					message: $('#revision<?php echo $this->id?>').html(),
					backdrop: true,
					size: 'large'
				});
				dialog.modal('show');
			});
		});
		</script>
		<?php

		return '<a id="revision' . $this->id . '" href="javascript:void(0);">' . $this->name . '</a>';
	}

	/**
	 * Get entity description
	 * @return string
	 */
	public function getTrashDescription()
	{
		return htmlspecialchars(
			Core_Str::cut($this->model, 255)
		);
	}
}