<?php

########################################################################
# Extension Manager/Repository config file for ext: "category_pages"
#
# Auto generated 24-08-2008 05:01
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'List Categorized Pages',
	'description' => 'Provides a content element that shows a list of categorized pages and page meta information like abstract, description, images (from attached files) etc. depends on toi_category 0.4.0',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.3.1',
	'dependencies' => 'cms,toi_category',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Till Korten',
	'author_email' => 'webmaster@korten-privat.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'toi_category' => '0.4.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:21:{s:9:"ChangeLog";s:4:"1185";s:10:"README.txt";s:4:"5053";s:12:"ext_icon.gif";s:4:"5e90";s:17:"ext_localconf.php";s:4:"16aa";s:15:"ext_php_api.dat";s:4:"54ba";s:14:"ext_tables.php";s:4:"353f";s:15:"flexform_ds.xml";s:4:"d8b0";s:13:"locallang.xml";s:4:"0cf0";s:16:"locallang_db.xml";s:4:"9b18";s:13:"template.tmpl";s:4:"ad4a";s:14:"doc/manual.sxw";s:4:"f3cc";s:19:"doc/wizard_form.dat";s:4:"ab31";s:20:"doc/wizard_form.html";s:4:"10b8";s:14:"pi1/ce_wiz.gif";s:4:"6bf3";s:34:"pi1/class.tx_categorypages_pi1.php";s:4:"e527";s:42:"pi1/class.tx_categorypages_pi1_wizicon.php";s:4:"526c";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"2a83";s:24:"pi1/static/constants.txt";s:4:"d997";s:24:"pi1/static/editorcfg.txt";s:4:"944a";s:20:"pi1/static/setup.txt";s:4:"f361";}',
	'suggests' => array(
	),
);

?>