<?php

    function GenerateLangIcons()
    {
	global $lang_idxs;
	global $idx_current_page;
	
	for ($i = 0; $i < count($lang_idxs); $i++)
	{
	    echo '<a href="index.php?content_id='.$idx_current_page.'&lang_id='.$i.'"><img class="lang" src="'.$lang_idxs[$i].'"/></a>';
	}
    }
    
    function GenerateMenu()
    {
	global $menu_items;
	global $idx_current_lang;
	
	for ($i = 0; $i < count($menu_items[$idx_current_lang]); $i++)
	{
	    echo '<a class="menu_item" href="index.php?content_id='.$i.'&lang_id='.$idx_current_lang.'"><p>'.$menu_items[$idx_current_lang][$i].'</p></a>';
	}
    }
    
    function GenerateTitle()
    {
	global $menu_items;
	
	global $idx_current_page;
	global $idx_current_lang;
	
	echo '<p>'.$menu_items[$idx_current_lang][$idx_current_page].'</p>';
    }
    
    function GenerateContent()
    {
	global $menu_hrefs;
	global $lang_hrefs;
	
	global $idx_current_page;
	global $idx_current_lang;
	
	include $lang_hrefs[$idx_current_lang].$menu_hrefs[$idx_current_page];
    }

?>