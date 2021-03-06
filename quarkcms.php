<?php

    //-------------------------------------------------------------------------------------------------------
    //  determine working directory
    $qcmsPath = __DIR__;
    $s = dirname($_SERVER['SCRIPT_FILENAME']);
    if ($s != '\\' && $s != '/') $s .= DIRECTORY_SEPARATOR;
    if (strpos($qcmsPath, $s) >= 0) $qcmsPath = substr($qcmsPath, strlen($s));
    define('qcmsPath', $qcmsPath);
    
    //-------------------------------------------------------------------------------------------------------
    //  register a shutdown function to be called on script termination
    register_shutdown_function('quarkCMS_shutdown');
    function quarkCMS_shutdown()
    {
        //  do error checking
        
        //  measure script execution time and write it in the browser window object
        $t = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 3); //  available since PHP 5.4.0

        //  this should be executed immediately instead of using onload event
        $js =   '<script type="text/javascript">window.serverExecutionTime = '.(string)$t.';</script>';
        echo $js;
    }
    
    require_once 'BaseGenerator.php';
    require_once 'ContentNode.php';
    //require_once 'ContentDefs.php';
    
    //  autoloader function
    function quarkCMS_autoloader($class)
    {
        $filename = $class.'.php';
        if ($filename[0] = 'T') $filename = substr($filename, 1);
        
        //  search in plugins folder
        $filename = 'plugins'.DIRECTORY_SEPARATOR.$filename;
        if (qcmsPath != '') $filename = qcmsPath.DIRECTORY_SEPARATOR.$filename;
        
        if (file_exists($filename)) include $filename;        
    }
    
    spl_autoload_register('quarkCMS_autoloader');
    
    class TQuarkCMS //extends TRPCService
    {
        static private $Finstance = null;
        static function instance()
        {
            if (self::$Finstance != null) return self::$Finstance;
            
            self::$Finstance = new TQuarkCMS();
            return self::$Finstance;
        }
        
        private function __construct()
        {
            
        }
        
        var $dataPath = '';
        
        //  Declare title, description, keywords and author tag vars
        
        //  Declare language references
        var $lang_idxs = array();
        var $lang_hrefs = array();
        
        //  Declare menu items
        //var $menu_items = array();
        //var $menu_hrefs = array();
        
        var $content = null;
        var $context = null;
        
        //  Current namespace
        var $namespace = 'default';
        
        //  Current content page -- set default here
        var $idx_current_page = 0;
        
        //  Current language index -- set default here
        var $idx_current_lang = 0;
        
        //  Load content definitions from xml
        function loadContentNode(SimpleXMLElement $xml, array $namespaces, &$id)
        {
            $type = $xml->getName();
            $id++;
            $node = new TContentNode($type, '', $id, $this);
            
            foreach ($namespaces as $prefix => $ns)
            {
                $domain = $prefix;
                if ($ns == null) 
                {
                    $prefix = null;
                    $domain = 'default';
                }
                
                foreach ($xml->attributes($prefix, true) as $attr)
                {
                    $name = trim(strtolower($attr->getName()));
                    $value = $attr->__toString();
                    
                    if ($name == 'name' && $prefix == null) $node->name = $value; 
                    else $node->attributes[$domain][$name] = $value;
                }                
                
                //  iterate through child items
                foreach ($xml->children($prefix, true) as $xml_node)
                {
                    $name = trim(strtolower($xml_node->getName()));
                    
                    /*if ($name == 'meta')
                    {
                        foreach ($xml_node->attributes($prefix, true) as $attr)
                        {
                            $meta_name = trim(strtolower($attr->getName()));
                            $meta_value = $attr->__toString();
                            
                            if (in_array($meta_name, array('owner', 'mode', 'created', 'modified')))
                            {
                                $meta_name = 'meta_'.$meta_name;
                                $node->$meta_name = $meta_value;
                            }
                            else $node->meta[$domain][$meta_name] = $meta_value;
                        }
                        
                        continue;
                    }*/
                    
                    if (in_array($name, array('id', 'name', 'url', 'ref', 'href', 'caption', 'text')))
                    {
                        $value = $xml_node->__toString();
                        if ($name == 'name' && $prefix == null) $node->name = $value; 
                        else $node->attributes[$domain][$name] = $value;
                        
                        continue;
                    }
                    
                    if ($name == 'meta')
                    {
                        //  here we do a dirty trick in order to avoid writing code to go through all namespaces for meta fields
                        $k = $id; //  so we don't increment the id for this one
                        $meta_node = $this->loadContentNode($xml_node, $namespaces, $k);
                        
                        foreach ($meta_node->attributes as $domain => $values)
                        {
                            foreach ($values as $key => $value)
                            {
                                if ($domain == 'default' && in_array($key, array('owner', 'mode', 'created', 'modified')))
                                {
                                    $meta_name = 'meta_'.$key;
                                    $node->$meta_name = $value;
                                }
                                else
                                {
                                    if (!isset($node->meta[$domain])) $node->meta[$domain] = array();
                                    $node->meta[$domain][$key] = $value;
                                }
                            }
                        }
                    }
                    else 
                    {
                        $kid = $this->loadContentNode($xml_node, $namespaces, $id);
                        if ($kid != null)
                        {
                            $kid->parent = $node;
                            $node->items[] = $kid;
                        }
                    }
                }
            }
            
            return $node;
        }
        
        
        function loadContentDefs()
        {
            $path = trim($this->dataPath);
            if (strlen($path) > 0) $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $path .= 'content.xml';
            
            if (file_exists($path))
            {
                $text = file_get_contents($path);
                $xml = simplexml_load_string($text);
                if ($xml === false) die("Content definitions file contains format errors.");
                
                $namespaces = $xml->getNamespaces(true);
                if (!isset($namespaces) || empty($namespaces)) $namespaces = array();
                $namespaces[] = null; //  add a null entry to iterate through default attributes
                $id = 0;
                $this->content = $this->loadContentNode($xml, $namespaces, $id);
                $this->context = $this->content;
            }
            else die ("Cannot find content definitions file.");
        }
        
        function GenerateLangIcons()
        {
            $result = '';
            for ($i = 0; $i < count($this->lang_idxs); $i++)
            {
                $result.= '<a href="index.php?content_id='.$this->idx_current_page.'&lang_id='.$i.'"><img class="lang" src="'.$this->lang_idxs[$i].'"/></a>';
            }
            return $result;
        }

        function setHeader()
        {
            echo '<title>'.$this->site_title.'</title>';
            echo '<meta name="description" content="'.$this->site_description.'"/>';
            echo '<meta name="keywords" content="'.$this->site_keywords.'"/>';
            echo '<meta name="author" content="'.$this->site_author.'"/>';           
        }
        
        function fasterTag($s)
        {
            $result = array();
            $spaces = array(' ', "\t");
            $len = strlen($s) - 1; $i = 0; $char = $s[$i];
            
            //  parse tag name
            $key = '';
            while (in_array($char, $spaces) && $i < $len) { $i++; $char = $s[$i]; } // skip any space in the beginning
            while (!in_array($char, $spaces) && $i < $len) { $key.= $char; $i++; $char = $s[$i]; };
            if ($i == $len) $key.= $char;
            $result['tag'] = $key;
            
            //  parse attributes
            while ($i < $len)
            {
                $key = '';
                while (in_array($char, $spaces) && $i < $len) { $i++; $char = $s[$i]; } // skip any space after the element and before attributes
                while (!in_array($char, $spaces) && $char != '=' && $i < $len) { $key.= $char; $i++; $char = $s[$i]; }
                if ($i == $len) $key.= $char;
                if ($char != '=')
                    while (in_array($char, $spaces) && $i < $len) { $i++; $char = $s[$i]; } // skip any space after the element and before attributes
                    
                if ($char = '=' && $i < $len)
                {
                    $value = ''; $i++; $char = $s[$i];
                    while (in_array($char, $spaces) && $i < $len) { $i++; $char = $s[$i]; } // skip any space after the element and before attributes
                    
                    $marker = '';
                    if (($char == '"' || $char == "'") && $i < $len)
                    {
                        $marker = $char; $i++; $char = $s[$i];
                        while ($char != $marker && $i < $len) { $value.= $char; $i++; $char = $s[$i]; }
                        if ($i < $len) { $i++; $char = $s[$i]; }
                    }
                    else
                        while (!in_array($char, $spaces) && $i < $len) { $i++; $char = $s[$i]; }
                    
                    $result[$key] = $value;
                }
                else $result[$key] = '';
            }
            
            return $result;
        }
        
        function parse(string $text, string $tagstart = '<q:', string $tagendopened = '>', string $tagendclosed = '/>', string $tagstartclose = '</q:')
        {
            //  parse the text for quark tags and collect them into an array alongside
            //  their start and end position
            $tags = array();
            
            $idx_start = strpos($text, $tagstart/*'<q:'*/); //  search for the first occurence of a quark tag
            while ($idx_start !== false)
            {
                //  assume some properties of the found tag
                $selfclosed = true;
                $malformed = false;
                
                //  locate tag and determine if it is malformed, selfclosed or not
                $idxs = $idx_start + strlen($tagstart)/*3*/; //  avoids a few adds in the next lines
                $idx_next = strpos($text, $tagstart[0]/*'<'*/, $idxs);  //  take the pos of the next tag opening to check for format errors
                $idx_stop = strpos($text, $tagendclosed/*'/>'*/, $idxs); //  locate the end of the tag as if it is an autoclosing one
                if ($idx_stop === false || ($idx_next !== false && $idx_next < $idx_stop))
                {
                    //  self closing marker not found, it might be an error or there could be a separate closing tag
                    $idx_stop = strpos($text, $tagendopened/*'>'*/, $idxs);
                    
                    if ($idx_stop === false || $idx_next < $idx_stop)
                    {
                        //  a new tag is opened before closing the current one or we simply get to EOF
                        $malformed = true;
                        
                        //  we'll attempt to decode the unclosed tag
                        if ($idx_stop === false) $idx_stop = strlen($string) - 1;
                        else $idx_stop = $idx_stop - 1;
                    }
                    else $selfclosed = false; //  we'll have to search for the closing tag
                }
                else $idx_stop++;
                
                //  extract tag info
                $len = $idx_stop - $idx_start + 1 - strlen($tagstart); // it's actually + 1 - 3
                $str_tag = substr($text, $idxs, $len); //  skip the <q: part and avoid a second substr
                $str_tag = trim($str_tag, " \t/".$tagendopened/*">"*/); //  cut any space, tab, slash or greater signs
                
                $parts = $this->fasterTag($str_tag);
                if (sizeof($parts) >= 1)
                {
                    $attr = array();
                    foreach ($parts as $key => $value)
                    {
                        if ($key == 'tag') $str_tag = $value;
                        else $attr[$key] = $value;
                    }
                    
                    $str_inner = '';
                    if (!$selfclosed)
                    {
                        $search = $tagstartclose/*'</q:'*/.$str_tag.$tagendopened/*'>'*/;
                        $idx = strpos($text, $search, $idx_stop);
                        if ($idx !== false)
                        {
                            $str_inner = substr($text, $idx_stop + 1, $idx - $idx_stop - 1);
                            $idx_stop = $idx + strlen($search) - 1;
                        }
                    }
                    
                    //  save gathered information
                    $tag_rec = array('tag' => $str_tag, 'start' => $idx_start, 'stop' => $idx_stop);
                    if (sizeof($attr) > 0) $tag_rec['attr'] = $attr;
                    if (!empty($str_inner)) $tag_rec['inner'] = $str_inner;
                    $tags[] = $tag_rec;
                }
                
                //  get the position of the next quark tag and continue searching
                $idx_start = strpos($text, $tagstart/*'<q:'*/, $idx_stop);
            }
            
            return $tags;
        }
        
        function filltemplate(string $text, array $vars)
        {
            $result = '';
            $tags = $this->parse($text, '{', '}', '/}', '{/');
            
            $offset = 0;
            foreach ($tags as $tag)
            {
                $result.= substr($text, $offset, $tag['start'] - $offset);
                
                switch ($tag['tag'])
                {
                    case 'foreach':
                        $innerText = ''; 
                        if (isset($tag['inner'])) $innerText = $tag['inner'];
                        
                        $attr = null; if (isset($tag['attr'])) $attr = $tag['attr'];
                        if (is_array($attr) && count($attr) > 0)
                        {
                            $varName = array_keys($attr)[0];
                            if ($varName[0] == '$')
                            {
                                $varName = substr($varName, 1);
                                if (isset($vars[$varName]) && is_array($vars[$varName]))
                                {
                                    foreach ($vars[$varName] as $var)
                                    {
                                        $result.= $this->filltemplate($innerText, $var);                                    
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        $varName = $tag['tag'];
                        if ($varName[0] == '$')
                        {
                            $varName = substr($varName, 1);
                            if (isset($vars[$varName])) $result.= $vars[$varName];
                        }
                        break;
                }
                
                $offset = $tag['stop'] + 1;
            }
            $result.= substr($text, $offset); //  copy the rest of the output buffer
            
            return $result;
        }
        
        function process(string $text)
        {
            $result = '';
            $tags = $this->parse($text, '<q:', '>', '/>', '</q:');
            
            //  rebuild the output by processing each content placeholder in the array
            //  and copying the in between bits directly from the input text
            $offset = 0;
            foreach ($tags as $tag)
            {
                $result.= substr($text, $offset, $tag['start'] - $offset);
                
                //  search for a content generator based on the tag name
                $GeneratorName = 'T'.ucfirst($tag['tag']).'Generator';
                if (class_exists($GeneratorName, $autoload = true)) 
                {
                    $Generator = new $GeneratorName($this);
                    $attr = null; if (isset($tag['attr'])) $attr = $tag['attr'];
                    $innerText = null; if (isset($tag['inner'])) $innerText = $tag['inner'];
                    $result.= $Generator->generate($attr, $innerText);
                }
                
                $offset = $tag['stop'] + 1;                
            }
            $result.= substr($text, $offset); //  copy the rest of the output buffer
            
            return $result;
        }
        
        function run()
        {
            $content_id = -1;
            if (isset($_REQUEST['content_id'])) $content_id = $_REQUEST['content_id'];

            $lang_id = -1;
            if (isset($_REQUEST['lang_id'])) $lang_id = $_REQUEST['lang_id'];
            
            if ($content_id > -1) $this->idx_current_page = $content_id;
            if ($lang_id > -1) $this->idx_current_lang = $lang_id;

            $this->loadContentDefs();
            if ($content_id > 0) $this->context = $this->content->findById($content_id);

            //$this->setHeader();
            echo $this->process('<q:template />');
            //echo qcmsPath;
        }
        
    }
?>
