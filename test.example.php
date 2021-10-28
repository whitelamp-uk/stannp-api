<?php

// Copy this to test.php (which is not under version control)


// Copy config.example.php to config.php (which is not under version control)
// config.php is included by Stannp.php directly


// These are typically used within an app config
define ( 'STANNP_ERROR_LOG',        false   );
// Postal country code
define ( 'BLOTTO_STANNP_COUNTRY',   'GB'    );
// POST request timeout seconds
define ( 'STANNP_TIMEOUT',          60      );
// Minimum length of left-match to campaign names
define ( 'STANNP_REDACT_SCOPE_LEN', 4       );

require __DIR__.'/Stannp.php';



try {

    $stannp = new \Whitelamp\Stannp ();
    // Paste examples from below into here and call this script from the command line



}
catch (\Exception $e) {
    echo $e->getMessage()."\n";
    exit ($e->getCode());
}

/*

// Create a group
print_r ($stannp->group_create('TEST-GRP-01'));

// Fetch group by name
print_r ($stannp->group('TEST-GRP-01'));

// Fetch all groups
print_r ($stannp->groups());

// Fetch recipients by group name
print_r ($stannp->recipients('TEST-GRP-01'));

// Fetch groups by recipient ID
print_r ($stannp->memberships('TEST-GRP-01'));

// Fetch all campaigns
print_r ($stannp->campaigns());

// Fetch campaign by name
print_r ($stannp->campaign('TEST-CPGN-01'));

// Fetch mailpieces by campaign name
print_r ($stannp->mailpieces('TEST-CPGN-01'));

// Create a campaign
$name = 'TEST-CPGN-01';
$template_id = 128426;
$recipients = [
    [
        'full_name' => 'Mx Edna Graffic',
        'job_title' => '',
        'company' => '',
        'address1' => 'The Hollyhocks, 123 The Stroop',
        'address2' => '',
        'address3' => '',
        'city' => 'Hugely',
        'postcode' => 'HG3 3EG',
        'ref_id' => 'BB1919_191919',
        'user_var_1' => 'bcdegp',
        'user_var_2' => 'ajkflm'
    ]
];
try {
    // Campaigns are created using a recipient list.
    // A group is created with the same name as the campaign.
    // [ No other methods make any assumptions about group name. ]
    print_r ($stannp->campaign_create($name,$template_id,$recipients));
}
catch (\Exception $e) {
    echo $e->getMessage()."\n";
}

// Do GDPR redaction
$stannp = new \Whitelamp\Stannp ();
try {
    print_r ($stannp->redact());
}
catch (\Exception $e) {
    echo $e->getMessage()."\n";
}


*/


