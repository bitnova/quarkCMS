<?php
	include "content_vars.php";
	include "content_funcs.php";
?>

<html>
    <head>
	<title><?php echo $site_title;?></title>
	<meta name="description" content="<?php echo $site_description;?>"/>
	<meta name="keywords" content="<?php echo $site_keywords;?>"/>
	<meta name="author" content="<?php echo $site_author;?>"/>
    </head>
    
    <body>
	
	<?php 
	    $content_id = -1;
	    $lang_id = -1;
	    
	    $content_id = $_GET["content_id"];
	    $lang_id = $_GET["lang_id"];
	    
	    if ($content_id > -1) $idx_current_page = $content_id;
	    if ($lang_id > -1) $idx_current_lang = $lang_id;
	    
	    include "template.html";
	?>
    </body>
</html>
