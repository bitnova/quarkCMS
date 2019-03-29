<?php

    class TLinkGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            
            $s = '<a href="#">';
            if (isset($innerText)) $s.= $innerText;
            $s.= '</a>';
            return $s;
        }
    }
?>