<?php

// Page doesn't accept subpages, 404 error
$oCore_Page = Core_Page::instance();
if ($oCore_Page->structure->getPath() != Core::$url['path'])
{
	$oCore_Page->error404();
}