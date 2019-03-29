<?php

    class TLogoGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            return '<img style="'.$cms->site_logoStyle.'" src="'.$cms->site_logo.'" />';
        }
    }

?>