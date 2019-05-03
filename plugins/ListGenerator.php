<?php

    class TListGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            $def = $cms->getContentById($cms->idx_current_page);
            if ($def == null) $def = $cms->content;
            
            if (!isset($def) || !isset($def['items'])) return '';
            
            $html = '<ol>';
            foreach ($def['items'] as $item)
            {
                if (!isset($item['type']) || $item['type'] != 'content') continue;
                
                $caption = '';
                if (isset($item['default']) && isset($item['default']['caption'])) $caption = $item['default']['caption'];
                    
                $id = '';
                if (isset($item['default']) && isset($item['default']['name'])) $id = $item['default']['id'];
                
                $href = 'index.php?content_id='.$id;
                $s = '<li><a class="menu_item" href="'.$href.'">'.$caption.'</a></li>';
                $html .= $s;
            }
            $html.= '</ol>';
            
            return $html;
        }
    }
    
?>