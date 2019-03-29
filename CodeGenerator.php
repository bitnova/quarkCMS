<?php

    class TCodeGenerator extends TBaseGenerator
    {
        function render($attr = null, $innerText = null)
        {
            $style = '
<style>
    pre
    {
        max-height: 40em;
        overflow: scroll;
    }

    pre.syntax code
    {
        font-family: monospace;
        counter-reset: codeline;
    }

    pre.syntax code span
    {
        display: block;
        line-height: 1.5em;
    }

    pre.syntax code span:before
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
     
    highlight("code");   
</script>
';
            
            echo $style."\n";
            echo '<pre class="syntax"><code id="code">'.$innerText.'</code></pre>'."\n";
            echo $js;            
        }
    }

?>