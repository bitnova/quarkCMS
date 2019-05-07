<?php 

    class TBaseGenerator
    {
        protected $cms = null;
        protected $content = null;
        protected $context = null;
        
        function __construct(TQuarkCMS $cmsInstance = null)        
        {
            if (isset($cmsInstance)) $this->cms = $cmsInstance;
            else $this->cms = TQuarkCMS::instance();    
            
            $this->content = $this->cms->content;
            $this->context = $this->cms->context;
        }
        
        function q(TContentNode $node, array $attr)
        {
            
        }
        
        
        function render(array $attr = null, $innerText = null)
        {
            return '';    
        }
        
        function generate(array $attr = null, $innerText = null)
        {
            ob_start();            
            $buffer = $this->render($attr, $innerText);
            if (empty($buffer)) $buffer = ob_get_contents();
            ob_end_clean();
            
            return TQuarkCMS::instance()->process($buffer);
        }
    }

?>