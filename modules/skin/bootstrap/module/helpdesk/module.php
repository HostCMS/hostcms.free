<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Helpdesk. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Helpdesk_Module extends Helpdesk_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Helpdesk_Ticket.new_incidents'))
		);
	}

	public function widget()
	{
		?><!-- Helpdesk -->
		<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
			<div class="databox radius-bordered databox-shadowed hostcms-widget-databox">
				<div class="databox-left bg-azure">
					<div class="databox-piechart">
						<a href="/admin/helpdesk/index.php" onclick="$.adminLoad({path: '/admin/helpdesk/index.php'}); return false"><i class="fa fa-life-ring fa-3x"></i></a>
					</div>
				</div>
				<?php
				$oCore_QueryBuilder_Select = Core_QueryBuilder::select()
					->select(array(Core_QueryBuilder::expression('SUM(`messages_count` - `processed_messages_count`)'), 'count'))
					->from('helpdesk_tickets')
					->join('helpdesks', 'helpdesk_tickets.helpdesk_id', '=', 'helpdesks.id')
					->where('helpdesk_tickets.deleted', '=', 0)
					->where('helpdesks.deleted', '=', 0)
					->where('helpdesks.site_id', '=', CURRENT_SITE);

				$row = $oCore_QueryBuilder_Select->execute()->asAssoc()->result();
				?>
				<div class="databox-right">
					<span class="databox-number azure"><?php echo intval($row[0]['count']) ?></span>
					<div class="databox-text"><?php echo Core::_('Helpdesk_Ticket.new_incidents')?></div>
					<div class="databox-stat azure radius-bordered">
						<i class="stat-icon icon-lg fa fa-life-ring"></i>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}