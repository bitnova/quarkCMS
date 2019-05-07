<?php
    class TMetaGenerator extends TBaseGenerator
    {
        function render(array $attr = null, $innerText = null)
        {
            $result = "";
            foreach ($this->content->findAllByType('meta') as $item)
            {
                if (isset($item->name)) $result.= sprintf('<meta name="%s" content="%s" />'."\n", $item->name, $item->text);                 
            }

            return $result;
        }
    }
?>