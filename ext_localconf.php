<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['FILE'] = \Bmack\ModernTemplateBuilding\ContentObject\FileContentObject::class;
$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['TEMPLATE'] = \Bmack\ModernTemplateBuilding\ContentObject\TemplateContentObject::class;
