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
                $str = file_get_contents($filename);
                
                switch ($ext)
                {
                    case 'txt':
                        $str = str_replace('[<', '::start::', $str);
                        $str = str_replace('>]', '::stop::', $str);
                        $str = htmlspecialchars($str, ENT_NOQUOTES);
                        $str = '<pre style="white-space: pre-wrap;">'.$str.'</pre>';
                        $str = str_replace('::start::', '<', $str);
                        $str = str_replace('::stop::', '>', $str);
                        return $str;
                    default:
                        return $str;
                        break;
                }
            }
            else return 'Content source not found'.' '.$filename;
        }
    }
    
?>