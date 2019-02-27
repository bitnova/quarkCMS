<?php
    include 'quarkcms.php';
    
    TQuarkCMS::instance()->dataPath = 'content';
    TQuarkCMS::instance()->run();
    
    echo qcmsPath;
?>


