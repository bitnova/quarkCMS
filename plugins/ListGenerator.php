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
            $def = $this->context;
            if (!isset($def)) $def = $this->content; if (!isset($def)) return '';
            
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
            
            
            $values = array();
            $orderby = ''; if (isset($attr) && isset($attr['orderby'])) $orderby = trim(strtolower($attr['orderby']));
            foreach ($def->findAllByType('content', $orderby) as $item)
            {
                $id = $item->id;
                $caption = $item->caption;
                $href = 'index.php?content_id='.$id;
                $meta_created = $item->meta_created;
                $meta_modified = $item->meta_modified;
                $meta_description = ''; if (isset($item->meta['description'])) $meta_description = $item->meta['description'];
                
                $row = array('id' => $id, 'caption' => $caption, 'href' => $href, 'meta.description'=> $meta_description, 'meta.created' => $meta_created, 'meta.modified' => $meta_modified);
                $values[] = $row;
            }
            
            return $this->cms->filltemplate($tpl, array('values' => $values));            
        }
    }
    
?>