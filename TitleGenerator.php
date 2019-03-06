<?php

    class TTitleGenerator extends TBaseGenerator
    {
        function render()
        {
            $cms = TQuarkCMS::instance();
            return '<h2>'.$cms->menu_items[$cms->idx_current_lang][$cms->idx_current_page].'</h2>';
        }
    }
    
?>