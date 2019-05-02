<?php

    class TLinkGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $ref = ""; $href = "";
            if (isset($attr) && is_array($attr))
            {
                if (isset($attr['href'])) $href = $attr['href'];
                if (isset($attr['ref'])) $ref = $attr['ref'];
            }
                        
            if ($ref != "")
            {
                $cms = TQuarkCMS::instance();
            
                $linked = $cms->getContentByName($ref);
                if ($linked != null) $href = 'index.php?content_id='.$linked['id'];
            }            
            
            $s = '<a href="'.$href.'">';
            if (isset($innerText)) $s.= $innerText;
            $s.= '</a>';
            return $s;
        }
    }
?>