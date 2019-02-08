<?php

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
            if (file_exists('content.xml'))
            {
                $xml = simplexml_load_file('content.xml');
                
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
                echo '<a class="menu_item" href="index.php?content_id='.$i.'&lang_id='.$this->idx_current_lang.'"><p>'.$this->menu_items[$this->idx_current_lang][$i].'</p></a>';
            }
        }
        
        function GenerateTitle()
        {
            echo '<p>'.$this->menu_items[$this->idx_current_lang][$this->idx_current_page].'</p>';
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
                if (file_exists($template_file.DIRECTORY_SEPARATOR.'template.html')) $template_file = $template_file.DIRECTORY_SEPARATOR.'template.html';
                else if (file_exists($template_file.DIRECTORY_SEPARATOR.'template.php')) $template_file = $template_file.DIRECTORY_SEPARATOR.'template.php';
                else if (file_exists($template_file.DIRECTORY_SEPARATOR.'index.html')) $template_file = $template_file.DIRECTORY_SEPARATOR.'index.html';
                else if (file_exists($template_file.DIRECTORY_SEPARATOR.'index.php')) $template_file = $template_file.DIRECTORY_SEPARATOR.'index.php';                
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
            if (isset($_GET['content_id'])) $content_id = $_GET['content_id'];

            $lang_id = -1;
            if (isset($_GET['lang_id'])) $lang_id = $_GET['lang_id'];
            
            if ($content_id > -1) $this->idx_current_page = $content_id;
            if ($lang_id > -1) $this->idx_current_lang = $lang_id;

            $this->loadContentDefs();
            $this->setHeader();
            echo $this->loadTemplate();            
        }
        
    }
?>