<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Update. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Update_Module extends Update_Module
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			1 => array('title' => Core::_('Update.menu'))
		);
	}

	public function widget()
	{
		try
		{
			$aUpdates = Update_Controller::instance()->parseUpdates();

			$error = $aUpdates['error'];

			?><!-- Update -->
			<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
				<div class="databox radius-bordered databox-shadowed hostcms-widget-databox">
					<div class="databox-left bg-themethirdcolor">
						<div class="databox-piechart">
							<a href="/admin/update/index.php" onclick="$.adminLoad({path: '/admin/update/index.php'}); return false"><i class="fa fa-refresh fa-3x"></i></a>
						</div>
					</div>
					<div class="databox-right">
						<span class="databox-number themethirdcolor">
							<?php
							$iUpdateCounts = count($aUpdates['entities']);
							if (!$error && $iUpdateCounts)
							{
								echo $iUpdateCounts;
							}
							?>
						</span>
						<div class="databox-text <?php echo $error ? 'databox-small' : ''?>"><?php
							if ($error > 0)
							{
								echo Core_Str::cutSentences(
									Core::_('Update.server_error_respond_' . $error), 120
								);
							}
							elseif ($iUpdateCounts == 0)
							{
								echo Core::_('Update.isLastUpdate');
							}
							else
							{
								echo Core_Inflection::getPlural('Обновление', $iUpdateCounts, 'ru');
							}
						?></div>
						<div class="databox-stat themethirdcolor radius-bordered">
							<i class="stat-icon icon-lg fa fa-refresh"></i>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		catch (Exception $e) {}
	}
}