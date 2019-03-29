<?php

    class TMenuGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            
            $result = '';
            for ($i = 0; $i < count($cms->menu_items[$cms->idx_current_lang]); $i++)
            {
                $s = '<a class="menu_item';
                if ($i == $cms->idx_current_page) $s.= ' current';
                
                $s.= '" href="index.php?content_id='.$i.'&lang_id='.$cms->idx_current_lang.'">'.$cms->menu_items[$cms->idx_current_lang][$i].'</a>';
                
                $result.= $s;
            }
            
            return $result;
        }
    }
    
?>