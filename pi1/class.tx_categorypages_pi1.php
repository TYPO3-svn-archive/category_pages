<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Till Korten <webmaster@korten-privat.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('toi_category').'api/class.tx_toicategory_api.php');


/**
 * Plugin 'List categorized pages' for the 'category_pages' extension.
 *
 * @credits parts of this code were inspired by the extension smile_categorization by Smile <typo3@smile.fr> www.smile.fr
 * @author	Till Korten <webmaster@korten-privat.de>
 * @package	TYPO3
 * @subpackage	tx_categorypages
 */
class tx_categorypages_pi1 extends tslib_pibase {
	var $prefixId	  = 'tx_categorypages_pi1';				// Same as class name
	var $scriptRelPath = 'pi1/class.tx_categorypages_pi1.php';		// Path to this script relative to the extension dir.
	var $extKey		= 'category_pages';					// The extension key.
	var $uploadFolder  = 'uploads/tx_categorypages/';
//	var $pi_checkCHash = true;
//	var $displayType;							// List, abstract, complete
//	var $abstractLength;							// Abstract length
	var $conf;
	// The configuration parameters
	var $pageList;
	var $booleanOperator;						// Choose if categories are combined using AND or OR
	var $notCategories;
	var $orderType;								// Contents ordered by date or title
	var $orderAsc = "ASC";								// Ascendent or descendent
	var $displayFields;							// Comma separated field names of
	var $resultsByRandom;
	var $maxPages;
	var $template;
	var $fieldHandlingInc;							// include file which user can edit to create custom field handling. Remark from till: should only be editable via typoscript since this is an admin feature.
	var $table = 'pages';							//is hardcoded into the class for now
	var $recursive;
	var $categories;							// the categories provided by cObj->data['pages']
	var $paginator;
	var $paginatorPage;
	var $paginatorID;
	var $numPages;

	var $subpart = '###BASE_TEMPLATE_ABSTRACT###';	// the Subpart in the template
	var $categoryApi;							// the Api to toi_categories
	var $language_uid;
	var $debug=0;
	
	
	/**
	 * The initialisation of a frontend plugin with flexform
	 *
	 * @param array $conf		$conf: the configuration array from TS
	 */
	function init($conf){
		$this->conf=$conf;
		
		// do the default Stuff
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		// initialize FlexForm
		$this->pi_initPIflexForm();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		// Check if the template is overwritten by the FF
		// if so, we need to place an "upload/" String
		$addupload=false;
		if(strlen(trim($this->pi_getFFvalue($piFlexForm, 'templateFile', 'sDEF')))>0){
			$addupload=true;
		}
		
		// write the settings from the FF into conf-array
		// this works only properly if the names in the FF are the same than in conf-array
		// first we go through the sheets
		
		// we have to check if there is an flexform saved at all for backwards compatibility
		if(is_array($piFlexForm)){
			foreach($piFlexForm['data'] as $sheet=>$data){
				// next the languages
				foreach ($data as $lang=>$value){
					// finally the key/value pairs
					foreach ($value as $key=>$val){
						// check if there is a val in the ff
						if(strlen(trim($this->pi_getFFvalue($piFlexForm,$key,$sheet)))>0){
							// get it and write it into conf
							$this->conf[$key]=trim($this->pi_getFFvalue($piFlexForm,$key,$sheet));
						}
					}
				}
			}
		}
		
		// get configuration from Typoscript parameters
		$this->orderType = $this->conf['orderType'];
		if($this->conf['orderTypeOther']){
			$this->orderType=$this->conf['orderTypeOther'];
		}
		$this->orderAsc = strtoupper($this->conf['orderAsc']);
		$this->booleanOperator = strtolower($this->conf['booleanOperator']);
		$this->notCategories = $this->conf['notCategories'];
		$this->displayFields = $this->conf['displayFields'];
		$this->maxPages = (int)$this->conf['maxPages'];
		$this->resultsByRandom = stristr($this->conf['resultsByRandom'],'true');
		if($addupload==true){
			$this->template = $this->cObj->fileResource($this->uploadFolder.$this->conf['templateFile']);
		}
		else{
			$this->template = $this->cObj->fileResource($this->conf['templateFile']);
		}
		$this->fieldHandlingInc = $GLOBALS['TSFE']->tmpl->getFileName($this->conf['fieldHandlingInc']);
		$this->recursive = $this->cObj->data['recursive'];
		$this->language_uid = $GLOBALS['TSFE']->sys_language_uid;
		
		if (!empty($this->cObj->data['pages'])) {
			$this->categories = explode(',',$this->cObj->data['pages']);
		}

		$this->paginator=(int)$this->conf['paginator'];
		$this->paginatorID = 'paginatorPage'.$this->cObj->data['uid'];		//the paginator gets a unique ID, so that multiple paginators work on one page
		
		
		if($this->piVars!=null && isset($this->piVars[$this->paginatorID])) {
			$this->paginatorPage=$this->piVars[$this->paginatorID];
		}else{
			$this->paginatorPage=0;
		}
		
		// get the Debugging Var from TS
		if($this->conf['debug'] != $this->debug){
			$this->debug = $conf['debug'];
		}
		
		// provide the API to the toi_category plugin
		$this->categoryApi = t3lib_div::makeInstance('tx_toicategory_api');
		
		if($this->debug){
			debug($piFlexForm,'FlexForm', __LINE__, __FILE__, 5);
			debug($this->conf,'Configuration Array');
			//debug($this->cObj->data, 'cObj->data');
			debug($this->categories, 'Categories');
			debug($this->piVars, 'piVars');
			debug($this->maxPages, 'maxPages');
			debug($this->paginator, 'paginator');
		}
	}
	
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	string		The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->init($conf);
		
		global $TYPO3_DB;
		
		// get the selected category links etc
		
		//if no categories exist, return $content
		if(!is_array($this->categories)){
			return $this->pi_wrapInBaseClass($content);
		}
		
		// get data according to categories selected in this plugin		
		// if we have NOT-Categories:
		if(strlen(trim($this->notCategories))>0){
			$notCatsArray=explode(',',$this->notCategories);
			$notWhereClause='';
			foreach($notCatsArray as $notCat){
				$notWhereClause.='`tx_toicategory_toi_category` NOT regexp \'[[:<:]]'.$notCat.'[[:>:]]\' AND ';
			}
			$notWhereClause=substr($notWhereClause, 0, -5); 
		}
		
		if($this->booleanOperator=="and" && count($this->categories)>1){
			$andWhereClause='';
			if ($this->recursive == 0) {
				foreach ($this->categories as $andCat) {
					$andWhereClause.= '`tx_toicategory_toi_category` LIKE \'%'.$andCat.'%\' AND ';
				}
				$andWhereClause=substr($andWhereClause, 0, -5);
			} else {
				foreach ($this->categories as $cat) {
					$andWhereClause.= '`tx_toicategory_toi_category` LIKE \'%\' AND (`tx_toicategory_toi_category` LIKE \'%'.$cat.'%\' OR ';
					$andCatTree = $this->categoryApi->get_category_tree($cat,$this->recursive);
					foreach ($andCatTree as $andCat) {
						$andWhereClause.= '`tx_toicategory_toi_category` LIKE \'%'.$andCat['uid'].'%\' OR ';
					}
					$andWhereClause=substr($andWhereClause, 0, -4).') AND ';
				}
				$andWhereClause=substr($andWhereClause, 0, -5);
			}
		}
		$dummyWhereClause = (isset($notWhereClause, $andWhereClause)) ? $notWhereClause.' AND '.$andWhereClause : $notWhereClause.$andWhereClause ;
		// SELECT uid FROM database where `tx_toicategory_toi_category` NOT regexp '[[:<:]]363[[:>:]]' AND `tx_toicategory_toi_category` NOT regexp '[[:<:]]362[[:>:]]';
		if($this->debug){
			debug($dummyWhereClause,'notWhereClause');
			debug($andCatTree,'and category tree');
		}

		foreach ($this->categories as $cat) {
			$dummyList[] = $this->categoryApi->get_data_from_categorys($cat,$this->table,$dummyWhereClause,$this->recursive);
		}
		
		
		//$pageList = $this->getPageIDs($dummyList,'recuid',$pageList);
		$this->pageList = $this->getPageIDs($dummyList,'recuid', null);
		
		if($this->debug){
			debug($dummyList,'dummylist');
			debug($this->pageList,'pageList with doubles');
		}
		
		//if no page matches the categories return $content
		if(!is_array($this->pageList)){
			return $this->pi_wrapInBaseClass($content);
		}

		// we now have a List, maybe with double Entries
		// we wish to: clear the list of the doubles
		$this->pageList=$this->removeDoubles($this->pageList);

		if($this->debug){
			debug($this->pageList,'pageList without doubles');
		}
		
		// now we have a great pageList
		// we can shuffle and cut if we like
		if($this->resultsByRandom && $this->maxPages < count($this->pageList) && $this->maxPages > 0){
			shuffle($this->pageList);
			$this->pageList=array_slice($this->pageList,0,$this->maxPages);
		}
		if($this->debug){
			debug($this->resultsByRandom,'$this->resultsByRandom');
		}
		
		if ((is_array($this->pageList)) && (implode('',$this->pageList)!='')) {
			// assemble the SQL-Statement
			$sqlUids = "".implode(",",$this->pageList)."";
			$orderBy = $this->table.'.'.$this->orderType.' '.$this->orderAsc;
			$orderBy = $GLOBALS['TYPO3_DB']->quoteStr($orderBy, $this->table);

			$limit='';
			
			if($this->maxPages>0){
				$limit=$this->maxPages;
			}
			if($this->paginator){
				if($this->maxPages>0){
					$limit=($this->paginator*($this->paginatorPage+1)>$this->maxPages) ? $this->paginator*$this->paginatorPage.','.($this->maxPages-$this->paginator*$this->paginatorPage) : $this->paginator*$this->paginatorPage.','.$this->paginator;
				} else {
					$limit=($this->paginator*$this->paginatorPage).','.$this->paginator;
				}
			}
			
			//$this->displayFields = $GLOBALS['TYPO3_DB']->quoteStr($this->displayFields, $this->table);
			if ($this->language_uid==0) {
				$sqlSelect = 'uid, '.$this->displayFields;
				$sqlFrom = $this->table;
				$sqlWhere = 'uid in ('.$sqlUids.') ';
				$sqlWhere = $GLOBALS['TYPO3_DB']->quoteStr($sqlWhere, $this->table).$GLOBALS['TSFE']->sys_page->enableFields($this->table);
			}
			else {
				$langTable = $this->table.'_language_overlay';
				
				// since language_overlay does not necessarily have all fields, we need to check if we might have to get some fields from the original table for ordering
				$query = 'SHOW COLUMNS FROM '.$langTable.' ;';
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);
				if ($res) {
					while ($defRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$avail_columns[] = $defRow['Field'];
					}
				}
				$TYPO3_DB->sql_free_result($res);

				$languageOverlayFields = implode(',', $avail_columns);

				// also the sorting field in the pages_language_overlay does not reflect the page tree, so we will fetch that one from the pages table
				if (!stristr($languageOverlayFields,$this->orderType) || strcasecmp($this->orderType,'sorting') == 0 ) {
					$langDisplayFieldsArray = explode(',', $this->displayFields);
					foreach ($langDisplayFieldsArray as $s) {						
						$langDisplayFields.=$langTable.'.'.trim($s).', ';
					}
					$langDisplayFields=trim($langDisplayFields,', ');
					$sqlSelect = $langTable.'.pid, '.$langDisplayFields;
					$sqlFrom = $this->table.', '.$langTable;
					$orderBy = $this->table.'.'.$this->orderType.' '.$this->orderAsc;
					$sqlWhere = $this->table.'.uid='.$langTable.'.pid AND '.$langTable.'.pid in ('.$sqlUids.') ';
					$sqlWhere = $GLOBALS['TYPO3_DB']->quoteStr($sqlWhere, $langTable).$GLOBALS['TSFE']->sys_page->enableFields($langTable);
				} else {
					$sqlSelect = 'pid, '.$this->displayFields;
					$sqlFrom = $langTable;
					$orderBy = $langTable.'.'.$this->orderType.' '.$this->orderAsc;
					$sqlWhere = $langTable.'.pid in ('.$sqlUids.') ';
					$sqlWhere = $GLOBALS['TYPO3_DB']->quoteStr($sqlWhere, $langTable).$GLOBALS['TSFE']->sys_page->enableFields($langTable);
				}
				if($this->debug){
					debug($languageOverlayFields,'Language Overlay Fields');
					debug($this->orderType, 'OrderType');
					debug($orderTable, '$orderTable');
					debug($this->table, 'table');
				}
			}

			//SQL injection safety ($sqlWhere is quoted above):
			$sqlSelect = $GLOBALS['TYPO3_DB']->quoteStr($sqlSelect, $this->table);
			$sqlFrom = $GLOBALS['TYPO3_DB']->quoteStr($sqlFrom, $this->table);
			$orderBy = $GLOBALS['TYPO3_DB']->quoteStr($orderBy, $this->table);

			if($this->debug){
				debug($sqlSelect,'sql select');
				debug($sqlFrom, 'sql from');
				debug($orderBy, 'order by');
				debug($sqlWhere, 'sql where');
				debug($limit, 'limit');
			}
			
			//the total number of pages
			if ($this->language_uid==0) {
				$this->numPages = count($this->pageList);
			} else {
				//for other languages wee need to perform a sql query to get the number of pages that actually have an alternate language
				$res = $TYPO3_DB->exec_SELECTquery('COUNT(*)',$sqlFrom,$sqlWhere);
				$row = $TYPO3_DB->sql_fetch_assoc($res);
				$this->numPages = (int)$row['COUNT(*)'];
				$TYPO3_DB->sql_free_result($res);
			}
			$this->numPages = ($this->numPages>$this->maxPages && $this->maxPages>0) ? $this->maxPages : $this->numPages;
			// get the Results
			$res = $TYPO3_DB->exec_SELECTquery($sqlSelect,$sqlFrom,$sqlWhere,'',$orderBy,$limit);
			while($row = $TYPO3_DB->sql_fetch_assoc($res)) {
				$pageData[] = $row;
			}
			$TYPO3_DB->sql_free_result($res);
			
			$fields = explode(',',str_replace(' ','',$this->displayFields));
			$fields[] = 'uid';
			$content = $this->assembleContent($fields,$pageData);
		} else {
			$content = '';
		}
		
		// return the content for rendering
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
 	* extract values with the targetKey from a complicated multidimensional array
	*
	* @param	array			$categories: complicated array
	* @param	mixed 			$targetKey: string or int name of the key that contains the desired data
	* @param	array			$pageList: empty array necessary for the recursion
	* @return	array			retrieved data without duplicates
	*/
	function getPageIDs($categories, $targetKey, $pageList) {
		if (is_array($categories)) {  	
			$last = array_pop($categories);
			// we found the targetkey
			if (isset($last[$targetKey]) && is_array($last)) {
				$pageList[] = $last[$targetKey];
			}
			// we found an array, but it is not the one we want
			// lets look inside recursively
			elseif (is_array($last)) {
				$pageList = $this->getPageIDs($last, $targetKey, $pageList);
			}
			
			// we are finished with this array,
			// we go to the next
			if (sizeof($categories)<1) {
			}
			else {
				$pageList = $this->getPageIDs($categories, $targetKey, $pageList);
			}
		}
		return $pageList;
	}
	
	/**
	* remove duplicate values from an array
	* 
	* @param	array			$pageList: array containing duplicate entries
	* @return	array			retrieved data without duplicates
	*/
	function removeDoubles($array) {
		foreach($array as $k => $v) {
			if (is_array($v)) {
				$ret = removeDoubles(array_merge($ret, $v));
			} else {
				$ret[$k] = $v;
			}
		}
		return array_unique($ret);
	}
	
	/**
	 * This function checks if any value in the given array is doubled for $count times
	 * and returns an array with entries for which it is the case
	 *
	 * @param int $count
	 * @param Array $myArray
	 * @return mixed $returnarray
	 */
	function getArrayValueForCount($count, $myArray){
		//myArray needs to be an array or the function won't work
		if(!is_array($myArray)){
			return;
		}
		// create new Array with "value"=>"counts"
		$temparray = array_count_values($myArray);
		// if there are no doubles in $myarray
		if(count($temparray)==count($myArray)){
			return false;
		}
		$returnarray=array();
		foreach($temparray as $i => $value){
			if($value == $count){
				$returnarray[]=$i;
			}
		}
		// if none of the values equals the value we want
		if(count($returnarray)==0){
			return false;
		}
		return $returnarray;
	}

	function makePaginator(){
		$subpart['PAGINATOR']=$this->cObj->getSubpart($this->template,'###PAGINATOR###');
		$subpart['PAGINATIONPAGES']=$this->cObj->getSubpart($subpart['PAGINATOR'],'###PAGINATIONPAGES###');
		$pageItem='';
		for($i=0;$i<$this->getNumPaginatorPages();$i++){
			if($i==$this->paginatorPage){
				$marker_array_pages['###PAGINATIONPAGE###']=($this->pi_getLL('page').' '.($i+1));
			}else{
				$marker_array_pages['###PAGINATIONPAGE###']=$this->pi_linkTP_keepPIvars(($this->pi_getLL('page').' '.($i+1)),$overrulePIvars=array($this->paginatorID=>$i));
			}
			$pageItem.=$this->cObj->substituteMarkerArray($subpart['PAGINATIONPAGES'],$marker_array_pages);
		}
		//$content.= $this->cObj->substituteSubpart($subpart['PAGINATOR'], '###PAGINATIONPAGES###',$pageItem);
		$subpart['PAGINATIONPAGES']=$pageItem;
		$marker_array['###FIRST###']=$this->pi_linkTP_keepPIvars($this->pi_getLL('first'),$overrulePIvars=array($this->paginatorID=>0));
		$marker_array['###LAST###']=$this->pi_linkTP_keepPIvars($this->pi_getLL('last'),$overrulePIvars=array($this->paginatorID=>$this->getNumPaginatorPages()-1));
		if($this->debug){
			debug($pageItem,'Page item');
			debug($subpart,'SUBPARTS');
			debug($marker_array,'MARKERARRAY');
		}
		if($this->numPages<=(int)$this->paginator){
			$marker_array='';
			$subpart='';
		}else{
			$content.= $this->cObj->substituteMarkerArrayCached($subpart['PAGINATOR'], $marker_array,$subpart);
		}
		return $content;
	}
	
	function getNumPaginatorPages(){
		return (ceil($this->numPages/(int)$this->paginator));
	}
	
	/**
	 * Generate the HTML content using the given template file.
	 *
	 * @param	array		$fields: names of the fields of the pages table to be used to assemble the content
	 * @param	array		$pageData: The content from the fields to be put into the ###fieldname### tags of the template.
	 * @return	string		the finished HTML output.
	 */
	function assembleContent($fields,$pageData) {
		$content = '';
		if (is_array($pageData)){ 
			if($this->paginator){
				$content.=$this->makePaginator();
			}
			foreach ($pageData as $page){
				if (is_array($fields)){
					foreach ($fields as $field) {
						if ($this->fieldHandlingInc) {
						include $this->fieldHandlingInc;
						} else {
						if (!strcmp($field,'media')) {
								if(eregi('jpg|gif|png', $page[$field])) {
									$this->conf['image.']['file'] = 'uploads/media/'.($page[$field]); //The image field name
								$theImgCode=$this->cObj->IMAGE($this->conf['image.']);
								$marker_array['###'.$field.'###'] = $theImgCode;
								} else {
								$marker_array['###'.$field.'###'] = '';
							}
						} elseif (!strcmp($field,'uid')) {																														  
														if ($this->language_uid==0) {																													   
															$marker_array['###typolink###'] = $GLOBALS['TSFE']->cObj->getTypoLink_URL($page[$field]);													   
														} else {																																			
															$marker_array['###typolink###'] = $GLOBALS['TSFE']->cObj->getTypoLink_URL($page['pid']);														
														}
							} else {
								$marker_array['###'.$field.'###'] = $page[$field];
						}
						}
					}
				}
				$content .= $this->template2html($marker_array);
			}
		}
		if($this->paginator){
			$content.=$this->makePaginator();
		}
		return $content;
	}
	

	/**
	* Replace an HTML template with data
	*
	* @param	string		path to the html template file
	* @param	string		zone to substitute
	* @param	array		marker array
	* @return	string		html code
	*/
	function template2html($values) {
		$mySubpart = $this->cObj->getSubpart($this->template, $this->subpart);
		return $this->cObj->substituteMarkerArray($mySubpart, $values);
	}

}

	// This function is never used, but might be used later...
	/**
	 * strip a string of unallowed characters using a whitelist.
	 *
	 * @param	string		$string String to be cleaned 
	 * @param	string		$legalChars optional: list of allowed chars default: '[a-z][A-Z][0-9] _'.
	 * @return	string		the cleaned string.
	 */
	/*function clean_string($string,$legalChars='[a-z][A-Z][0-9] _') {
		$clean ='';
		$string = str_split($string);
		foreach ($string as $char) {
		if (!ereg($char, $legalChars)) $char=''; // bogus char? Make it empty
			}
		$clean = implode('',$string);
		return $clean;
	}*/


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/category_pages/pi1/class.tx_categorypages_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/category_pages/pi1/class.tx_categorypages_pi1.php']);
}

?>
