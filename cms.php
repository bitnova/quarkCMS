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

        
        //  Define title, description, keywords and author tag vars
        var $site_title = "";
        var $site_description = "";
        var $site_keywords = "";
        var $site_author = "";
        
        //  Define laguage references
        var $lang_idxs = array();
        var $lang_hrefs = array();
        
        //  Define menu items
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
            include 'template.html';
        }
        
        function run()
        {
            $content_id = -1;
            $lang_id = -1;
            
            $content_id = $_GET["content_id"];
            $lang_id = $_GET["lang_id"];
            
            if ($content_id > -1) $this->idx_current_page = $content_id;
            if ($lang_id > -1) $this->idx_current_lang = $lang_id;

            $this->loadContentDefs();
            $this->setHeader();
            $this->loadTemplate();
            
        }
        
    }

?>