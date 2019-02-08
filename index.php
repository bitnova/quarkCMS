<?php
    $t0 = microtime(true);
    
    include 'quarkcms.php';
    
    TQuarkCMS::instance()->run();
    
    $t = microtime(true);
    $delta = ($t - $t0) * 1000;
    //echo "executed in $delta ms";
?>


