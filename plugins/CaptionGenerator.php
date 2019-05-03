<?php

    class TCaptionGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            $def = $cms->getContentById($cms->idx_current_page);
            
            if (!isset($def)) return '';
            
            $caption = '';
            if (isset($def['default']) && isset($def['default']['caption'])) $caption = $def['default']['caption'];
            
            return '<h2>'.$caption.'</h2>';
        }
    }
    
?>