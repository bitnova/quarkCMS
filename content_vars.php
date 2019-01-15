<?php

	//  Define title, description, keywords and author tag vars
	$site_title = "";
	$site_description = "";
	$site_keywords = "";
	$site_author = "";
    
	//  Define laguage references
	$lang_idxs = array();
	$lang_hrefs = array();
    
   	//  Define menu items
   	$menu_items = array();
	$menu_hrefs = array();
    
	//  Current content page -- set default here
	$idx_current_page = 0;
    
	//  Current language index -- set default here
	$idx_current_lang = 0;

	//  Load content definitions from xml
	if (file_exists('content.xml'))
	{
	$xml = simplexml_load_file('content.xml');
	
	//  load site descriptor tags
	$site_title = $xml->title;
	$site_description = $xml->description;
	$site_keywords = $xml->keywords;
	$site_author = $xml->author;

	for ($i = 0; $i < sizeof($xml->lang); $i++)
	{
	    $lang_idxs[$i] = $xml->lang[$i]->idx;
	    $lang_hrefs[$i] = $xml->lang[$i]->href;
	}

	for ($i = 0; $i < sizeof($xml->item); $i++)
	{
	    for ($j = 0; $j < sizeof($xml->item[$i]->menu_item); $j++)
	    {
		$menu_items[$j][$i] = $xml->item[$i]->menu_item[$j];
	    }
	    
	    $menu_hrefs[$i] = $xml->item[$i]->href;
	}
	}
	else die ("Cannot load content definitions.");

?>
