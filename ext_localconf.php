<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

## WOP:[pi][1][addType] / [pi][1][tag_name]
  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_categorypages_pi1 = < plugin.tx_categorypages_pi1.CSS_editor
',43);


## WOP:[pi][1][addType]
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_categorypages_pi1.php','_pi1','list_type',0);
?>