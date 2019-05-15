<?php

    class TLinkGenerator extends TBaseGenerator
    {
        function render(array $attr = null, $innerText = null)
        {
            $ref = ""; $href = "";
            if (isset($attr) && is_array($attr))
            {
                if (isset($attr['href'])) $href = $attr['href'];
                if (isset($attr['ref'])) $ref = $attr['ref'];
            }
                        
            if ($ref != "")
            {
                $linked = $this->content->findByName($ref);
                if (isset($linked)) $href = 'index.php?content_id='.$linked->id;
            }            
            
            $style = '';
            if (isset($attr['style'])) $style = ' style="'.$attr['style'].'" ';
            
            $target = '';
            if (isset($attr['target'])) $target = ' target="'.$attr['target'].'" ';
            
            $s = '<a href="'.$href.'"'.$target.$style.'>';
            if (isset($innerText)) $s.= $innerText;
            $s.= '</a>';
            return $s;
        }
    }
?>