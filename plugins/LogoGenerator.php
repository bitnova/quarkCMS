<?php

    class TLogoGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            $def = $cms->getContentByType('logo');
            
            $filename = '';
            $style = '';
            if (isset($def['default']))
            {
                if (isset($def['default']['url'])) $filename = $def['default']['url'];
                if (isset($def['default']['style'])) $style = $def['default']['style'];
            }
            
            return '<img style="'.$style.'" src="'.$filename.'" />';
        }
    }

?>