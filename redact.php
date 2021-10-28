<?php

// Redaction script

// Left-matching of campaign name is used to limit the scope of the
// redaction. Mostly for safety; to limit the damage caused by unknown
// problems with the redaction logic.



define ( 'STANNP_ERROR_LOG',        false   );

define ( 'BLOTTO_STANNP_COUNTRY',   'GB'    );

define ( 'STANNP_TIMEOUT',          60      );

define ( 'STANNP_REDACT_SCOPE_LEN', 4       );


require __DIR__.'/Stannp.php';


if (!array_key_exists(1,$argv)) {
    fwrite (STDERR,"Usage:\n        {$argv[0]} campaign_names_starting_with\n");
    exit (101);
}
if (strlen($argv[1])<STANNP_REDACT_SCOPE_LEN) {
    fwrite (STDERR,"Usage:\n        campaign_names_starting_with must be at least ".STANNP_REDACT_SCOPE_LEN." characters\n");
    exit (102);
}
$campaign_names_starting_with = $argv[1];


try {
    $stannp = new \Whitelamp\Stannp ();
    echo "Recipients ID for redaction found in campaigns starting with $campaign_names_starting_with\n";
    $stannp->recipients_redact ($campaign_names_starting_with,true);
}
catch (\Exception $e) {
    fwrite (STDERR,$e->getCode()." ".$e->getMessage()."\n");
    exit ($e->getCode());
}

