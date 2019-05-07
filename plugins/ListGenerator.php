<?php

    class TListGenerator extends TBaseGenerator
    {
        function render(array $attr = null, $innerText = null)
        {
            $def = $this->context;
            if (!isset($def)) $def = $this->content; if (!isset($def)) return '';
            
            /*$def = $this->cms->getContentById($this->cms->idx_current_page);
            if ($def == null) $def = $this->cms->content; if (!isset($def)) return '';*/
            
            $html = '<ol>';
            foreach ($def->findAllByType('content') as $item)
            {
                $caption = $item->caption;
                $id = $item->id;
                
                $href = 'index.php?content_id='.$id;
                $s = '<li><a class="menu_item" href="'.$href.'">'.$caption.'</a></li>';
                $html .= $s;
            }
            $html.= '</ol>';
            
            return $html;
        }
    }
    
?>