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
        
        function findAll(string $key, $value)
        {
            $result = array();
            
            foreach ($this->items as $item)
                if ($item instanceof TContentNode && $item->$key == $value) $result[] = $item; 
            
            return $result;
        }
        
        function findAllByType(string $type) { return $this->findAll('type', $type); }
        function findAllbyId(int $id) { return $this->findAll('id', $id); }
        function findAllByName(string $name) { return $this->findAll('name', $name); }
        
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