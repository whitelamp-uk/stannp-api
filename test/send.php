<?php

require '/home/mark/blotto/blotto2/scripts/functions.php';
require '/home/blotto/config/dbh.cfg.php';
//require BLOTTO_STANNP_CLASS;
require __DIR__.'/../Stannp.php';


// ANLs
try {
    // stannp_mail() is in blotto2/scripts/functions.php
    $c = stannp_mail ('anl');
    print_r ($c);
}
catch (\Exception $e) {
    fwrite (STDERR,$e->getMessage()."\n");
    exit (101);
}

// Winners
try {
    // stannp_mail() is in blotto2/scripts/functions.php
    $c = stannp_mail ('win');
    print_r ($c);
}
catch (\Exception $e) {
    fwrite (STDERR,$e->getMessage()."\n");
    exit (102);
}

exit (0);

