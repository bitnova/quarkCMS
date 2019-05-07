<?php

    class TListingGenerator extends TBaseGenerator
    {
        static private $Finstance = null;
        static function instance()
        {
            if (self::$Finstance != null) return self::$Finstance;
            
            self::$Finstance = new self();
            return self::$Finstance;
        }
        
        
        private $echoed = false;
        function echoStyle()
        {
            $style = '
<style>
    pre.listing code
    {
        font-family: monospace;
        counter-reset: codeline;
    }
                
    pre.listing code span
    {
        display: block;
        line-height: 1.5em;
    }
                
    pre.listing code span:before
    {
        counter-increment: codeline;
        content: counter(codeline);
        display: inline-block;
        width: 2em;
        text-align: right;
        padding: 0 1em;
        border-right: 1px solid #ddd;
        margin: 0em;
        margin-right: 0.5em;
        color: #888;
    }
</style>
';
            $js = '
<script type="text/javascript">
    function highlight(id)
    {
        var code = document.getElementById(id);
        var str_in = code.innerText;
        var str_out = "";
        var lines = str_in.split(/\r?\n/);
        for (var i = 0; i < lines.length; i++)
        {
            if (i == 0 && lines[i] == "") continue;
            str_out += "<span>" + lines[i] + "</span>";
        }
        code.innerHTML = str_out;
    }                
</script>
';   
            if (!$this->echoed)
            {
                echo $style."\n";
                echo $js."\n";
                $this->echoed = true;
            }
        }
        
        private $count = 0;
        private function generateId()
        {
            $this->count++;
            return 'listing_'.$this->count;
        }
        
        function render(array $attr = null, $innerText = null)
        {
            self::instance()->echoStyle();
            $id = self::$Finstance->generateId();
            echo '<pre class="listing" style="max-height: 40em; overflow: auto;"><code id="'.$id.'">'.$innerText.'</code></pre>'."\n";
            echo '<script>highlight("'.$id.'");</script>';            
        }
    }

?>
