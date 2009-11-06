<?php
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
?>
