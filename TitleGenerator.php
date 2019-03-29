<?php

    class TTitleGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            return '<h2>'.$cms->menu_items[$cms->idx_current_lang][$cms->idx_current_page].'</h2>';
        }
    }
    
?>