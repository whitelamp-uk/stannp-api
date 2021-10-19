<?php

require '/home/blotto/blotto2/scripts/functions.php';
require '/home/blotto/config/dbh.cfg.php';
require BLOTTO_STANNP;

// ANLs and winner letters will be selected based on a where-sent_already=0-type-thingummy


// For ANLs
// ========

$group      = BLOTTO_STANNP_PREFIX.' Winners 2021-10-19';
$template   = BLOTTO_STANNP_TEMPLATE_ANL;
$anls = [
    [
        "tickets_issued" => '2021-10-19',
        "ccc" => 'BB',
        "ClientRef" => 'BB4321_123456',
        "projected_first_play" => '2021-12-19',
        "ticket_numbers" => '123456, 654321',
        "superdraw_tickets" => '',
        "title" => 'Ms',
        "name_first" => 'Edna',
        "name_last" => 'Graffic',
        "email" => 'ednagraffic@gee.mail',
        "mobile" => '07070 707070',
        "telephone" => '01232 101010',
        "address_1" => 'The Hollyhocks, 123 The Place',
        "address_2" => 'Scratchpiston',
        "address_3" => 'Corblimey',
        "town" => 'Trumpton',
        "county" => '',
        "postcode" => 'TP3 3PT',
        "Mandate_Provider" => 'RSM',
        "Mandate_Ref" => '123456789012',
        "Account_Name" => 'E Graffic',
        "Account_Sortcode" => '01-02-03',
        "Account_Number" => '04050607',
        "Freq" => 'Monthly',
        "Amount" => 8.68,
        "Created" => '2021-10-19',
        "StartDate" => '2021-11-08'
    ]
];
try {
    stannp_fields_merge ($anls,'ClientRef',$crefs);
    \Whitelamp\Stannp::campaign_create ($group,$template,$anls);
    echo "That seemed to work nicely\n";
}
catch (\Exception $e) {
    fwrite (STDERR,"Doh! ".$e->getMessage()."\n");
    exit (101);
}

// BLOTTO_MAKE_DB.blotto_player will be updated to sent_already=1 type-thingummy
// "where ClientRef in ('".implode("','",$crefs)."')";

exit (0);





// For winner letters
// ==================

$group      = BLOTTO_STANNP_PREFIX.' Winners 2021-10-19';
$template   = BLOTTO_STANNP_TEMPLATE_ANL;
$wins = [
    [
        'entry_id' => 54321,
        'draw_closed' => '2021-10-15',
        'winnings' => 10,
        'ticket_number' => '102938',
        'superdraw' => '',
        'prize' => '£10 3 of 6',
        'client_ref' => 'BB1020_304050',
        'Sortcode' => '*123',
        'Account' => '*456',
        'created' => '2019-08-18',
        'cancelled' => '',
        'ccc' => 'BB',
        'canvas_ref' => '304050',
        'supporter_id' => 4321,
        'title' => 'Mr',
        'name_first' => 'Idaho',
        'name_last' => 'De Hoe-Down',
        'email' => 'idahodehoedown@cripes.com',
        'mobile' => '07707 077070',
        'telephone' => '02132 132321',
        'address_1' => '1324 Forever Avenue',
        'address_2' => '',
        'address_3' => '',
        'town' => 'Hugeleigh',
        'county' => '',
        'postcode' => 'HU2 2WU',
        'latest_payment_collected' => '2021-10-03',
        'active' => 'ACTIVE',
        'status' => 'LIVE',
        'fail_reason' => '',
        'current_mandate_frequency' => 'Monthly',
        'latest_mandate_amount' => 4.34
    ]
];
try {
    stannp_fields_merge ($wins,'entry_id',$entries) {
    \Whitelamp\Stannp::campaign_create ($group,$template,$anls);
    echo "That seemed to work nicely\n";
}
catch (\Exception $e) {
    fwrite (STDERR,"Doh! ".$e->getMessage()."\n");
    exit (101);
}

// BLOTTO_MAKE_DB.blotto_winner will be updated to sent_already=1 type-thingummy
// "where entry_id in (".implode(",",$entries).")";

