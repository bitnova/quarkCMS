<?php

    class TTemplateGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $cms = TQuarkCMS::instance();
            $def = $cms->getContentByType('template');
            
            $template_file = '';
            if (isset($def['default']) && isset($def['default']['url'])) $template_file = $def['default']['url'];
            if (!file_exists($template_file)) return 'template not found';
            
            if (is_dir($template_file))
            {
                //  attempt to find an actual code file
                $template_file .= DIRECTORY_SEPARATOR; //  reduce string concatenations during next checks
                if (file_exists($template_file.'template.html')) $template_file .= 'template.html';
                else if (file_exists($template_file.'template.php')) $template_file .= 'template.php';
                else if (file_exists($template_file.'index.html')) $template_file .= 'index.html';
                else if (file_exists($template_file.'index.php')) $template_file .= 'index.php';
                else return 'template not found';
            }
            
            ob_start();
            include $template_file;
            $buffer = ob_get_contents();
            ob_end_clean();
            
            return $buffer;
        }
    }
    
?>