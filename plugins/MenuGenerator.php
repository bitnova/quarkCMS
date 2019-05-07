<?php

    class TMenuGenerator extends TBaseGenerator
    {
        function render(array $attr = null, $innerText = null)
        {
            $scope = ''; $name = ''; $autogenerate = 'false';
            if (isset($attr))
            {
                if (isset($attr['scope'])) $scope = $attr['scope'];
                if (isset($attr['name'])) $name = $attr['name'];
            }
            
            $def = null;
            switch ($scope)
            {
                case 'context':
                    $def = $this->context->parent;
                    break;
                default:
                    $def = $this->content;
                    break;
            }
            
            if (!isset($def)) return '';
            $def = $def->findByType('menu'); if (!isset($def)) return '';
            
            if (isset($def->autogenerate)) $autogenerate = $def->autogenerate;
            if (isset($attr['autogenerate'])) $autogenerate = $attr['autogenerate'];
            $autogenerate = trim(strtolower($autogenerate));            

            $result = '';
            if ($autogenerate == 'true' && isset($def->parent))
            {
                foreach ($def->parent->items as $item)
                {
                    if ($item->type != 'content') continue;
                    
                    $caption = $item->caption; if (!isset($caption)) $caption = '';                    
                    $href = 'index.php?content_id='.$item->id;
                    $current = ''; if ($item->id == $this->cms->idx_current_page) $current = 'current';
                    
                    $s = '<a class="menu_item '.$current.'" href="'.$href.'">'.$caption.'</a>';
                    $result.= $s;
                }
            }
            
            foreach ($def->items as $item)
            {
                $caption = $item->caption; if (!isset($caption)) continue;                    
                $ref = $item->ref;
                                    
                $href = '';
                $current = '';
                if (isset($ref))
                {
                    $linked = $this->content->findByName($ref);
                    //$linked = $this->cms->getContentByName($ref);
                    if (isset($linked)) 
                    {
                        $href = 'index.php?content_id='.$linked->id;
                        if ($linked->id == $this->cms->idx_current_page) $current = 'current';
                    }
                }
                else 
                    if (isset($item->href)) $href = $item->href;

                $s = '<a class="menu_item '.$current.'" href="'.$href.'">'.$caption.'</a>';
                $result.= $s;
            }
            
            return $result;            
        }
    }
    
?>