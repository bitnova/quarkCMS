<?php 

    class TBaseGenerator
    {
        function render()
        {
            return '';    
        }
        
        function generate()
        {
            ob_start();            
            $buffer = $this->render();
            if (empty($buffer)) $buffer = ob_get_contents();
            ob_end_clean();
            
            return TQuarkCMS::instance()->process($buffer);
        }
    }

?>