<?php
    class TMetaGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            $def = $cms->getContentByType('meta');
            if (!isset($def['default']) || !isset($def['default']['name'])) return '';
            
            $name = '';
            if (isset($def['default']['name'])) $name = $def['default']['name'];
            
            $content = '';
            if (isset($def['default']['text'])) $content = $def['default']['text'];
            
            $s = '<meta name="%s" content="%s" />';
            $s = sprintf($s, $name, $content);
            
            return $s;
        }
    }
?>