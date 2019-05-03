<?php

    class TMenuGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();

            $scope = ""; $name = ""; $context = null;
            if (isset($attr) && is_array($attr))
            {
                if (isset($attr['scope'])) $scope = $attr['scope'];
                $context = $cms->getParentContentById($cms->idx_current_page);
                
                if (isset($attr['name'])) $name = $attr['name'];
            }
            
            $def = $cms->getContentByType('menu', $context);
            if (isset($def['items']))
            {
                $result = '';
                foreach ($def['items'] as $item)
                {
                    if (!isset($item['default']) || !isset($item['default']['caption'])) continue;
                    $caption = $item['default']['caption'];
                    
                    $ref = '';
                    if (isset($item['default']) && isset($item['default']['ref'])) $ref = $item['default']['ref'];
                    
                    $href = '';
                    $current = '';
                    if ($ref != '')
                    {
                        $linked = $cms->getContentByName($ref);
                        if ($linked != null) 
                        {
                            $href = 'index.php?content_id='.$linked['id'];
                            if ($linked['id'] == $cms->idx_current_page) $current = 'current';
                        }
                    }
                    else
                    {
                        //  if reference is not specified then maybe there is a direct hyperlink refeerence
                        if (isset($item['default']) && isset($item['default']['href'])) $href = $item['default']['href'];
                    }
                    
                    $s = '<a class="menu_item '.$current.'" href="'.$href.'">'.$caption.'</a>';
                    $result.= $s;
                }
                
                return $result;
            }
            
            return '';            
        }
    }
    
?>