<?php

    class TLogoGenerator extends TBaseGenerator
    {
        function render(array $attr = null, $innerText = null)
        {
            $def = $this->content->findByType('logo'); if (!isset($def)) return '';
            //$def = $this->cms->getContentByType('logo');
            
            $filename = $def->url;
            $style = $def->style;
            
            return '<img style="'.$style.'" src="'.$filename.'" />';
        }
    }

?>