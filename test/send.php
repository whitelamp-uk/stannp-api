<?php

require '/home/mark/blotto/blotto2/scripts/functions.php';
require '/home/blotto/config/dbh.cfg.php';
//require BLOTTO_STANNP_CLASS;
require __DIR__.'/../Stannp.php';


// Create a campaign
$name = 'Z-TEST-MARK-0017';
$template_id = 128426;
$recipients = [
    [
        'name_first' => 'Edna',
        'name_last' => 'Graffic',
        'address_1' => 'The Hollyhocks, 123 The Stroop',
        'address_2' => '',
        'address_3' => '',
        'town' => 'Hugely',
        'postcode' => 'HG3 3EG',
        'user_var_1' => 'bcdegp',
        'user_var_2' => 'ajkflm'
    ]
];
$stannp = new \Whitelamp\Stannp ();
try {
    $campaign = $stannp->campaign_create ($name,$template_id,$recipients);
}
catch (\Exception $e) {
    echo $e->getMessage()."\n";
}

/*

// Fetch all Stannp groups
$stannp = new \Whitelamp\Stannp ();
print_r ($stannp->groups());

// Fetch Stannp recipients by group name
$stannp = new \Whitelamp\Stannp ();
print_r ($stannp->recipients('Z-TEST-MARK-0014'));

// Fetch all Stannp campaigns
$stannp = new \Whitelamp\Stannp ();
print_r ($stannp->campaigns());

// Fetch Stannp campaign by name
$stannp = new \Whitelamp\Stannp ();
print_r ($stannp->campaign('Z-TEST-MARK-0001'));

$stannp = new \Whitelamp\Stannp ();
print_r ($stannp->mailpieces('Z-TEST-MARK-0013'));

*/


