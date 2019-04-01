<?php

    class TContentGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
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
                        $s = str_replace('[<', '::start::', $s);
                        $s = str_replace('>]', '::stop::', $s);
                        $s = '<pre style="white-space: pre-wrap;">'.filter_var($s, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES).'</pre>';
                        $s = str_replace('::start::', '<', $s);
                        $s = str_replace('::stop::', '>', $s);
                        return $s;
                    default:
                        return $s;
                        break;
                }
            }
            else return 'Content source not found'.' '.$filename;
        }
    }
    
?>