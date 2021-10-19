<?php 
require '../Stannp.php';


use Whitelamp\Stannp;
$s= new Stannp;

$un = STANNP_DB_USER;
$pw = STANNP_DB_PWD;
$db = STANNP_DB_NAME;

$dbconn = new mysqli ('localhost',$un,$pw,$db);

//$res = $s->whoami();
//print_r($res);

$c_list = $s->campaigns_get ();
foreach ($c_list as $c) { 
	$ci = "INSERT IGNORE INTO campaigns SET ".sqltransform($c);
	dbq($ci);

	$groupid = $c['recipients_group'];
	$r_list = $s->get_recipients_group ($groupid);
	foreach ($r_list as $rcp) {
		$rcp['campaign_id'] = $c['id'];
		$rcp['group_id'] = $groupid;
		$ri = "INSERT IGNORE INTO recipients SET ".sqltransform($rcp);
		dbq($ri);
	}
}

function dbq ($q) {
	global $dbconn;
	$r = $dbconn->query($q);
	if (!$r) {
	    printf("Error message: %s\n", $dbconn->error);
	    echo "\n".$q."\n";
	    exit;
	}
}

function sqltransform ($in) {
	global $dbconn;
	$out = array();
	foreach ($in as $k =>$v) {
		if ($k == 'id') {
			$k = 'stannp_id';
		}
		$out[] = "`$k`='".$dbconn->real_escape_string($v)."'";
	}
	return implode(', ', $out);
}
