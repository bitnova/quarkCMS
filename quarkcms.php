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
                
                //  load site descriptor tags
                $this->site_title = $xml->title;
                $this->site_description = $xml->description;
                $this->site_keywords = $xml->keywords;
                $this->site_author = $xml->author;
                
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
            else die ("Cannot load content definitions.");
        }
        
        function GenerateLangIcons()
        {
            for ($i = 0; $i < count($this->lang_idxs); $i++)
            {
                echo '<a href="index.php?content_id='.$this->idx_current_page.'&lang_id='.$i.'"><img class="lang" src="'.$this->lang_idxs[$i].'"/></a>';
            }
        }
        
        function GenerateMenu()
        {
            for ($i = 0; $i < count($this->menu_items[$this->idx_current_lang]); $i++)
            {
                $s = '<a class="menu_item';
                if ($i == $this->idx_current_page) $s.= ' current';
                
                $s.= '" href="index.php?content_id='.$i.'&lang_id='.$this->idx_current_lang.'">'.$this->menu_items[$this->idx_current_lang][$i].'</a>';
                
                echo $s;
            }
        }
        
        function GenerateTitle()
        {
            echo '<h2>'.$this->menu_items[$this->idx_current_lang][$this->idx_current_page].'</h2>';
        }
        
        function GenerateContent()
        {
            include $this->lang_hrefs[$this->idx_current_lang].$this->menu_hrefs[$this->idx_current_page];
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
            $s = ob_get_contents();
            ob_end_clean();
            
            return $s;
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