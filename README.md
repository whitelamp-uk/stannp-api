# stannp-api

A PHP class for sending postal mail via Stannp


Deploy like this:

require /my/path/to/stannp-api/Stannp.php
$data = get_letters ();
try {
    \Whitelamp\Stannp::post ($data);
    echo "That seemed to work nicely\n";
}
catch (\Exception $e) {
    fwrite (STDERR,"Doh! ".$e->getMessage()."\n");
    exit (101);
}

