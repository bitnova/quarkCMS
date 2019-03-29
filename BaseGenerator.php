<?php 

    class TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            return '';    
        }
        
        function generate($attr = null, $innerText = null)
        {
            ob_start();            
            $buffer = $this->render($attr, $innerText);
            if (empty($buffer)) $buffer = ob_get_contents();
            ob_end_clean();
            
            return TQuarkCMS::instance()->process($buffer);
        }
    }

?>