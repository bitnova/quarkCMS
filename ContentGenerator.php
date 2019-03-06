<?php

    class TContentGenerator extends TBaseGenerator
    {
        function render()
        {
            $cms = TQuarkCMS::instance();
            
            $lang_path = '';
            if (isset($cms->lang_hrefs[$cms->idx_current_lang])) $lang_path = $cms->lang_hrefs[$cms->idx_current_lang];
            $filename = $lang_path.$cms->menu_hrefs[$cms->idx_current_page];
            
            if (file_exists($filename))
            {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $s = file_get_contents($filename);
                
                switch ($ext)
                {
                    case 'txt':
                        return '<pre style="white-space: pre-wrap;">'.filter_var($s,FILTER_SANITIZE_SPECIAL_CHARS).'</pre>';
                        break;
                    default:
                        return $s;
                        break;
                }
            }
        }
    }
    
?>