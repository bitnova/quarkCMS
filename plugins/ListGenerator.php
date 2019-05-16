<?php

    class TListGenerator extends TBaseGenerator
    {
        static $defaultViewTemplate = '
<ol>
    {foreach $values}
    <li><a href="{$href/}">{$caption/}</a> (created by {$owner/} on {$created/}, last changed on {$modified/})</li>
    {/foreach}
</ol>
';
        
        function render(array $attr = null, $innerText = null)
        {
            //  select content node
            $def = null;
            if (isset($attr['ref'])) $def = $this->content->findByName($attr['ref']);
            if (!isset($def)) $def = $this->context;
            if (!isset($def)) $def = $this->content; 
            if (!isset($def)) return '';
            
            
            //  view template
            $tpl = $this::$defaultViewTemplate; //  assume there is no custom template defined
            if (isset($innerText)) $tpl = $innerText; //  inner text is given, then this should be the new template
            else if ($attr != null && isset($attr['template'])) 
            {
                //  there is a template file specified as attribute
                $viewTemplate = $attr['template'];
                $filename = get_class($this).'-'.$viewTemplate.'html';
                if ($filename[0] == 'T') $filename = substr($filename, 1);
                
                $def_template = $this->content->findByType('template');
                if (isset($def_template->url))
                {
                    $template_file = $def_template->url;
                    if (!is_file($template_file)) $template_file = dirname($template_file);
                    $filename = rtrim($template_file, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;
                    if (file_exists($filename)) $tpl = file_get_contents($filename);
                }
            }
            
            //  extract orderby condition
            $orderby = ''; if (isset($attr['orderby'])) $orderby = trim(strtolower($attr['orderby']));
            
            //  set recurrent to be true only if we are counting the resulted elements
            $recurrent = false; if (isset($attr['count'])) $recurrent = true;
            
            //  see if we need to exclude some branches
            $exclude = ''; if (isset($attr['exclude'])) $exclude = trim(strtolower($attr['exclude']));
            
            //  search for items
            $items = $def->findAllByType('content', $orderby, $recurrent, $exclude);
            
            //  prepare the values array which will be passed as atgument to the view template
            $values = array();
            $count = count($items); if (isset($attr['count'])) $count = min($count, $attr['count']);
            $i = 0;
            foreach ($items as $item)
            {
                $row_values = array('id' => $item->id, 'name' => $item->name, 'caption' => $item->caption, 'href' => 'index.php?content_id='.$id, 'meta.created' => $item->meta_created, 'meta.modified' => $item->meta_modified);

                $meta_description = ''; if (isset($item->meta['default']['description'])) $meta_description = $item->meta['default']['description'];
                $row_values['meta.description'] = $meta_description;
                
                $values[] = $row_values;
                
                $i++;
                if ($i == $count) break;
            }
            
            return $this->cms->filltemplate($tpl, array('values' => $values));            
        }
    }
    
?>