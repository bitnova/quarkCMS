<?php

    class TContentNode
    {
        var $type;
        var $name;
        var $id;
        var $attributes = array();
        var $items = array();
        var $parent = null;
        
        private $cms = null;
        
        //  meta information
        var $meta_owner = '';
        var $meta_mode = '';
        var $meta_created = '';
        var $meta_modified = '';
        var $meta = array();
        
        function __construct(string $type, string $name, int $id, TQuarkCMS $cmsInstance = null)
        {
            $this->type = $type;
            $this->name = $name;
            $this->id = $id;
        
            if (isset($cmsInstance)) $this->cms = $cmsInstance;
            else $this->cms = TQuarkCMS::instance();
        }
        
        function find(string $key, $value)
        {
            //  breadth first
            foreach ($this->items as $item)
                if ($item instanceof TContentNode && $item->$key == $value) return $item;
            
            //  repeat the search in each item
            foreach ($this->items as $item)
            {
                $result = $item->find($key, $value);
                if ($result != null) return $result;
            }
            
            return null;
        }
        
        function findByType(string $type) { return $this->find('type', $type); }
        function findById(int $id) { return $this->find('id', $id); }
        function findByName(string $name) { return $this->find('name', $name); }
        
        function findAll(string $key, $value, string $orderby = '', bool $recurrent = false, string $exclude = '')
        {
            $result = array();
            
            foreach ($this->items as $item)
                if ($item instanceof TContentNode && $item->$key == $value && $item->name != $exclude) $result[] = $item;
            
            if ($recurrent)
                foreach ($this->items as $item)
                    if ($item->name != $exclude)
                        $result = array_merge($result, $item->findAll($key, $value, '', true, $exclude));
            
            if ($orderby != '')
            {
                $field = null; $direction = null;
                $parts = explode(' ', $orderby);
                if (count($parts) > 0) { $field = $parts[0]; if (strpos($field, 'meta.') === 0) $field = str_replace('meta.', 'meta_', $field); }
                if (count($parts) > 1) $direction = $parts[1];
                if ($direction == null) $direction = 'asc';
                if (isset($field))
                {
                    uasort($result, 
                        function($a, $b) use ($field, $direction)
                        {
                            if ($a->$field == $b->$field) return 0;
                            if ($direction == 'desc') return ($a->$field < $b->$field) ? 1 : -1;
                            else return ($a->$field < $b->$field) ? -1 : 1;
                        }
                    );
                }
            }
            
            return $result;            
        }
        
        function findAllByType(string $type, string $orderby = '', bool $recurrent = false, string $exclude = '') { return $this->findAll('type', $type, $orderby, $recurrent, $exclude); }
        function findAllbyId(int $id, string $orderby = '', bool $recurrent = false, string $exclude = '') { return $this->findAll('id', $id, $orderby, $recurrent, $exclude); }
        function findAllByName(string $name, string $orderby = '', bool $recurrent = false, string $exclude = '') { return $this->findAll('name', $name, $orderby, $recurrent, $exclude); }
        
        function Where($f, bool $recurrent = false)
        {
            $result = array();
            
            foreach ($this->items as $item)
                if ($f($item)) $result[] = $item;
            
            if ($recurrent)
            {
                foreach ($this->items as $item)
                    $result = array_merge($result, $item->Where($f, true));
            }
        }
        
        /*function OrderBy($f)
        {
            
        }*/
        
        function getFullURL()
        {
            $url = $this->url;
            if ($url === null) return null;
            
            $k = strpos($url, '/');
            if ($k !== 0 && $this->parent != null)
            {
                $prefix = $this->parent->getFullURL();
                if (isset($prefix))
                {
                    //  check if $prefix refers to a file
                    $path = pathinfo($prefix);
                    if (isset($path['extension'])) $prefix = $path['dirname'];
                    
                    $url = rtrim($prefix, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$url;
                }
            }

            //  in case we have a root node, we make it relative to the content folder
            if ($this->parent == null || $k === 0) 
            {
                $root = rtrim($this->cms->dataPath, DIRECTORY_SEPARATOR);
                $url = ($k === 0 ? $root.$url : $root.DIRECTORY_SEPARATOR.$url);
            }
            
            return $url;
        }
        
        public function __isset($key)
        {
            $ns = 'default';
            if (isset($this->cms)) $ns = $this->cms->namespace;
            if ($ns == '') $ns = 'default';
            
            if (isset($this->attributes[$ns]) && isset($this->attributes[$ns][$key])) return true;
            else if ($ns != 'default' && isset($this->attributes['default']) && isset($this->attributes['default'][$key])) return true;
            
            return false;
        }
        
        public function __get($key)
        {
            $ns = 'default';
            if (isset($this->cms)) $ns = $this->cms->namespace;
            if ($ns == '') $ns = 'default';
            
            $result = null;
            if (isset($this->attributes[$ns]) && isset($this->attributes[$ns][$key])) $result = $this->attributes[$ns][$key];
            else 
                if ($ns != 'default' && isset($this->attributes['default']) && isset($this->attributes['default'][$key]))
                    $result = $this->attributes['default'][$key];
                
            return $result;
        }
        
        public function __set($key, $value)
        {
            $ns = 'default';
            if (isset($this->cms)) $ns = $this->cms->namespace;
            if ($ns == '') $ns = 'default';
            
            if (!isset($this->attributes[$ns])) $this->attributes[$ns] = array();
            if (!isset($this->attributes[$ns][$key])) $this->attributes[$ns][$key] = $value;
        }
        
        public function __unset($name)
        {
            $ns = 'default';
            if (isset($this->cms)) $ns = $this->cms->namespace;
            if ($ns == '') $ns = 'default';
            
            if (isset($this->attributes[$ns]) && isset($this->attributes[$ns][$key])) unset($this->attributes[$ns][$key]);
        }
    }
    
?>