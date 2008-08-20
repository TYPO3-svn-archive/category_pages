<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

## WOP:[pi][1][addType]
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


## WOP:[pi][1][addType]
t3lib_extMgm::addPlugin(array('LLL:EXT:category_pages/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

// Enable Flexforms
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:category_pages/flexform_ds.xml');

## WOP:[pi][1][plus_wiz]:
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_categorypages_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_categorypages_pi1_wizicon.php';

## WOP:[ts][1]
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/', 'List Category Pages');
?>