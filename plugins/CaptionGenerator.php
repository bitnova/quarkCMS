<?php

    class TCaptionGenerator extends TBaseGenerator
    {
        function render(array $attr = null, $innerText = null)
        {
            if (!isset($this->context)) return '';
            //$def = $this->cms->content->findById($this->cms->idx_current_page);
            //$def = $this->cms->getContentById($this->cms->idx_current_page);            
            //if (!isset($def)) return '';
            
            $caption = $this->context->caption;            
            return '<h2>'.$caption.'</h2>';
        }
    }
    
?>