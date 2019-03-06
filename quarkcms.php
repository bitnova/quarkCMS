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
        //global $quark_widgets_dir;
        //global $WidgetCollection;
        
        $filename = $class.'.php';
        if ($filename[0] = 'T') $filename = substr($filename, 1);
        include $filename;
        
/*        if (isWidget($class))
        {
            $WidgetCollection[] = $class;
            TQuark::instance()->updateWidgetThemes($class);
        }*/
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
        
        function loadTemplate()
        {
            if (!file_exists($this->template_path)) return 'template not found';
            
            $template_file = $this->template_path;            
            if (is_dir($template_file))
            {
                //  attempt to find an actual code file
                $template_file .= DIRECTORY_SEPARATOR; //  reduce string concatenations during next checks
                if (file_exists($template_file.'template.html')) $template_file .= 'template.html';
                else if (file_exists($template_file.'template.php')) $template_file .= 'template.php';
                else if (file_exists($template_file.'index.html')) $template_file .= 'index.html';
                else if (file_exists($template_file.'index.php')) $template_file .= 'index.php';
                else return 'template not found';
            }
            
            ob_start();
            include $template_file;
            $buffer = ob_get_contents();
            ob_end_clean();
            
            //  parse the output buffer for quark tags and collect them
            $result = '';
            $tags = array();
            
            $idx_start = strpos($buffer, '<q:');
            while ($idx_start !== false)
            {
                $idx_stop = strpos($buffer, '/>', $idx_start);
                if ($idx_stop === false) $idx_stop = strpos($buffer, '<', $idx_start + 1); else $idx_stop++; 
                if ($idx_stop === false) $idx_stop = strlen($buffer) - 1;
                $len = $idx_stop - $idx_start + 1;
                
                $str_tag = substr($buffer, $idx_start + 3, $len - 3); //  skip the <q: part and avoid a second substr
                $str_tag = strtolower(trim($str_tag, " \t/>")); //  cut any space, tab, slash or greater signs
                
                $tag_rec = array('tag' => $str_tag, 'start' => $idx_start, 'stop' => $idx_stop);
                $tags[] = $tag_rec;
                
                $idx_start = strpos($buffer, '<q:', $idx_stop); //  get the position of the next quark tag
            }

            //  rebuild the output while processing each content placeholder
            $offset = 0;
            foreach ($tags as $tag)
            {
                $result.= substr($buffer, $offset, $tag['start'] - $offset);
                
                //  search for a content generator based on the tag name
                $GeneratorName = 'T'.ucfirst($tag['tag']).'Generator';
                if (class_exists($GeneratorName, $autoload = true)) 
                {
                    $Generator = new $GeneratorName();
                    $result.= $Generator->render();
                }
                
                /*switch ($tag['tag'])
                {
                    case 'logo':
                        $result.= $this->GenerateLogo();
                        break;
                    case 'title':
                        $result.= $this->GenerateTitle();
                        break;
                    case 'menu':
                        $result.= $this->GenerateMenu();
                        break;
                    case 'content':
                        $result.= $this->GenerateContent();
                        break;
                }*/
                
                $offset = $tag['stop'] + 1;                
            }
            $result.= substr($buffer, $offset); //  copy the rest of the output buffer
            
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
            echo $this->loadTemplate();
            echo qcmsPath;
        }
        
    }
?>
