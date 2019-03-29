<?php

    $quarkCMSTimeOrigin = microtime(true);


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
        global $quarkCMSTimeOrigin;
        $t = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 3); //  available since PHP 5.4.0
        /*$js =   '<script type="text/javascript">'."\n".
                '   window.addEventListener("load", quarkCMSLoad);'."\n".
                '   function quarkCMSLoad()'."\n".
                '   {'."\n".
                '      window.serverExecutionTime = '.(string)$t.';'."\n".
                '   }'."\n".
                '</script>';*/
        //  this should be executed immediately instead of using onload event
        $js =   '<script type="text/javascript">window.serverExecutionTime = '.(string)$t.';</script>';
        echo $js;
    }
    
    require_once 'BaseGenerator.php';
    
    //  autoloader function
    function quarkCMS_autoloader($class)
    {
        $filename = $class.'.php';
        if ($filename[0] = 'T') $filename = substr($filename, 1);
        include $filename;        
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
        var $site_title = "";
        var $site_description = "";
        var $site_keywords = "";
        var $site_author = "";
        
        //  Declare location of logo image
        var $site_logo = "";
        var $site_logoStyle = "";
        
        //  Declare template path
        var $template_path = "";
        
        //  Declare language references
        var $lang_idxs = array();
        var $lang_hrefs = array();
        
        //  Declare menu items
        var $menu_items = array();
        var $menu_hrefs = array();
        
        //  Current content page -- set default here
        var $idx_current_page = 0;
        
        //  Current language index -- set default here
        var $idx_current_lang = 0;
        
        //  Load content definitions from xml
        function loadContentDefs()
        {
            $path = trim($this->dataPath);
            if (strlen($path) > 0) $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $path .= 'content.xml';
            
            if (file_exists($path))
            {
                $xml = simplexml_load_file($path);
                if ($xml === false) die("Content definitions file contains format errors.");
                
                //  load site descriptor tags
                $this->site_title = $xml->title;
                $this->site_description = $xml->description;
                $this->site_keywords = $xml->keywords;
                $this->site_author = $xml->author;
                
                if (isset($xml->logo)) 
                {
                    if (isset($xml->logo->url)) $this->site_logo = $xml->logo->url;
                    if (isset($xml->logo->style)) $this->site_logoStyle = $xml->logo->style;
                }
                
                if (isset($xml->template)) $this->template_path = $xml->template;
                
                for ($i = 0; $i < sizeof($xml->lang); $i++)
                {
                    $this->lang_idxs[$i] = $xml->lang[$i]->idx;
                    $this->lang_hrefs[$i] = $xml->lang[$i]->href;
                }
                
                for ($i = 0; $i < sizeof($xml->item); $i++)
                {
                    for ($j = 0; $j < sizeof($xml->item[$i]->menu_item); $j++)
                    {
                        $this->menu_items[$j][$i] = $xml->item[$i]->menu_item[$j];
                    }
                    
                    $this->menu_hrefs[$i] = $xml->item[$i]->href;
                }
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
        
        function process(string $text)
        {
            //  parse the text for quark tags and collect them into an array alongside
            //  their start and end position
            $result = '';
            $tags = array();
            
            $idx_start = strpos($text, '<q:'); //  search for the first occurence of a quark tag
            while ($idx_start !== false)
            {
                //  assume some properties of the found tag
                $selfclosed = true;
                $malformed = false;
                
                //  locate tag and determine if it is malformed, selfclosed or not
                $idxs = $idx_start + 3; //  avoids a few adds in the next lines
                $idx_next = strpos($text, '<', $idxs);  //  take the pos of the next tag opening to check for format errors
                $idx_stop = strpos($text, '/>', $idxs); //  locate the end of the tag as if it is an autoclosing one
                if ($idx_stop === false || $idx_next < $idx_stop)
                {
                    //  self closing marker not found, it might be an error or there could be a separate closing tag
                    $idx_stop = strpos($text, '>', $idxs);
                    
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
                $len = $idx_stop - $idx_start - 2; // it's actually + 1 - 3
                $str_tag = substr($text, $idxs, $len); //  skip the <q: part and avoid a second substr
                $str_tag = trim($str_tag, " \t/>"); //  cut any space, tab, slash or greater signs
                
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
                        $search = '</q:'.$str_tag.'>';
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
                $idx_start = strpos($text, '<q:', $idx_stop);
            }
            
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
                    $Generator = new $GeneratorName();
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
            $this->setHeader();
            echo $this->process('<q:template />');
            //echo qcmsPath;
        }
        
    }
?>
