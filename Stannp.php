<?php

namespace Whitelamp;

require __DIR__.'/config.php';

class Stannp {

    protected $timeout;
    protected $url;
    protected $key;
    protected $email_admin;
    protected $email_from;
    protected $campaign_list;
    protected $group_list;
    protected $verbose;

    public function __construct ( ) {
        if (defined('STANNP_TIMEOUT')) {
            $this->timeout      = STANNP_TIMEOUT;
        }
        else {
            $this->timeout      = 60;
        }
        $this->url              = STANNP_API_URL;
        $this->key              = STANNP_API_KEY;
        $this->email_admin      = STANNP_EMAIL_ADMIN;
        $this->email_from       = STANNP_EMAIL_FROM;
    }

    public function campaign ($name) {
        foreach ($this->campaigns() as $c) {
            if ($c['name']==$name) {
                if ($c['recipients_group']) {
                    $c['group_id'] = $c['recipients_group'];
                    $group = $this->group_by_id ($c['group_id']);
                    if ($group) {
                        $response = $this->curl_get ('recipients/list/'.$group['id']);
                        if (!$response['success']) {
                            $this->exception (101,"Failed to get recipients for group #{$group['id']}");
                            return false;
                        }
                        $rs = $response['data'];
                        $ms = $this->mailpieces ($c['id']);
                        foreach ($rs as $i=>$r) {
                            foreach ($ms as $m) {
                                if ($m['recipient_id']==$r['id']) {
                                    $rs[$i]['mailpiece_status'] = $m['status'];
                                    $rs[$i]['mailpiece_id'] = $m['id'];
                                    break;
                                }
                            }
                            if (!array_key_exists('mailpiece_status',$rs[$i])) {
                                    $rs[$i]['mailpiece_status'] = null;
                                    $rs[$i]['mailpiece_id'] = null;
                            }
                        }
                        $c['recipient_list'] = $rs;
                    }
                    else {
                        $c['group_id'] = null;
                        $c['recipient_list'] = [];
                    }
                }
                else {
                    $c['group_id'] = null;
                    $c['recipient_list'] = [];
                }
                return $c;
            }
        }
        fwrite (STDERR,"Campaign '$name' was not found\n");
        return false;
    }

    public function campaign_create ($name,$template_id,$recipients) {
        $v = false;
        if (defined('STANNP_ERROR_LOG') && STANNP_ERROR_LOG) {
            $v = true;
        }
        // House rules: campaign and group have the same name
        $group_id = $this->group_create ($name);
        // Create recipients
        foreach ($recipients as $r) {
            // `barcode` is for Royal mail use and should never be passed
            if (array_key_exists('barcode',$r)) {
                $this->exception (102,"`barcode` is an illegal field name - reserved by Stannp for internal use");
                return false;
            }
            // Overwrite any existing recipient with this newest data
            $r['on_duplicate'] = 'update';
            /*
                Stannp quirk. "Execute a method behind the scenes to
                add recipient to a group by setting a property called
                group_id".
                It should really have been called next_group_id...
            */
            $r['group_id'] = $group_id; // Next group to join
            /*
                Put another way, Stanpp's API gives the false impression
                to a programmer (expecting conventional property names)
                that the relationship recipient:group is N:1.
                It is actually N:M so that a recipient can be in many
                undispatched campaigns.
            */
            $fields = [
                'full_name','job_title','company',
                'address1','address2','address3','city','country','postcode'
            ];
            foreach ($fields as $f) {
                if (!array_key_exists($f,$r)) {
                    $r[$f] = '';
                }
                $r[$f] = trim ($r[$f]);
            }
            $fields = [
                'full_name','address1','city','country','postcode'
            ];
            foreach ($fields as $f) {
                if (!strlen($r[$f])) {
                    $this->exception (103,"`$f` is a compulsory Stannp field");
                    return false;
                }
                if ($f=='postcode' && !preg_match('<'.STANNP_POSTCODE_PREG.'>',$r[$f])) {
                    $this->exception (104,"Postcode '{$r[$f]}' is not valid");
                    return false;
                }
            }
            $response = $this->curl_post ('recipients/new',$r);
            if (!$response->success) {
                $this->exception (104,"Failed to create recipient '{$r['ClientRef']}' for campaign/group '$name'");
                return false;
            }
            $this->info ("Stannp successfully posted recipient: ".print_r($r,true),$v);
        }
        // Add a new campaign using template ID and group ID
        $campaign = [
            'name'              => $name,
            'type'              => 'letter',
            'template_id'       => $template_id,
            'what_recipients'   => 'all',
            'addons'            => ''
        ];
        $campaign['group_id']   = $group_id;
        $response = $this->curl_post ('campaigns/new',$campaign);
        if (!$response->success) {
            $this->exception (105,"Failed to create campaign $name");
            return false;
        }
        $campaign_id = $response->data;
        // Notify the administrator
        mail (
            $this->email_admin,
            "Stannp campaign $name is loaded",
            "Campaign #$campaign_id $name has been loaded over the API ready for you to approve and book.\n\n",
            "From: {$this->email_from}\n"
        );
        return [
            'name'              => $name,
            'campaign_id'       => $campaign_id,
            'group_id'          => $group_id,
            'template_id'       => $template_id,
            'recipients'        => count ($recipients)
        ];
    }

    public function campaigns ( ) {
        if ($this->campaign_list) {
            return $this->campaign_list;
        }
        $response = $this->curl_get ('campaigns/list');
        if (!$response['success']) {
            $this->exception (106,"Failed to get campaigns");
            return false;
        }
        $this->campaign_list = $response['data'];
        return $this->campaign_list;
    }

	private function curl_get ($request) {   
        // Modified from Stannp documentation
		$opts = [
		    'http' => [
		        'method'  => 'GET',
		        'header'  => 'Content-type: application/x-www-form-urlencoded'
		    ]
		];
		$context = stream_context_create ($opts);
        // TODO: Is file_get_contents() the best way here? Seem to remember
        // something about server configuration constraints...
        // Also what is the timeout preset here and can we override it?
		$result = file_get_contents (
            $this->url.$request.'?api_key='.$this->key,
            false,
            $context
        );
		$response = json_decode ($result, true);
        if (!$response) {
            $this->exception (107,"cURL GET error");
            return false;
        }
		return $response;
	}

    private function curl_post ($request,$post,$options=[]) {
        // Modified from PHP manual
        if (!is_array($post) || !is_array($options)) {
            $this->exception (108,"Post and option arguments must be arrays");
            return false;
        }
        $defaults = array (
            CURLOPT_POST            => 1,
            CURLOPT_HEADER          => 0,
            CURLOPT_URL             => $this->url.$request.'?api_key='.$this->key,
            CURLOPT_FRESH_CONNECT   => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_FORBID_REUSE    => 1,
            CURLOPT_TIMEOUT         => $this->timeout,
            CURLOPT_POSTFIELDS      => http_build_query ($post)
        );    
        $ch = curl_init ();
        curl_setopt_array ($ch,$options+$defaults);
        if (!$result=curl_exec($ch)) {
            $this->exception (109,"cURL POST error");
            return false;
        }
        curl_close ($ch);
        return json_decode ($result);
    }

    private function exception ($code,$message) {
        if (defined('STANNP_ERROR_LOG') && STANNP_ERROR_LOG) {
            error_log ($code.' '.$message);
        }
        throw new \Exception ($message,$code);
        return false;
    }

    public function group ($name) {
        if (!$this->group_list) {
            $this->groups ();
        }
        foreach ($this->group_list as $g) {
            if ($g['name']==$name) {
                return $g;
            }
        }
        return false;
    }

    public function group_by_id ($id) {
        if (!$this->group_list) {
            $this->groups ();
        }
        foreach ($this->group_list as $g) {
            if ($g['id']==$id) {
                return $g;
            }
        }
        return false;
    }

    public function group_create ($name) {
        $response = $this->curl_post ('groups/new/',['name'=>$name]);
        if (!$response->success) {
            $this->exception (110,"Failed to create group $name");
            return false;
        }
        return $response->data;
    }

    public function groups ( ) {
        $response = $this->curl_get ('groups/list');
        if (!$response['success']) {
            $this->exception (111,"Failed to get groups");
            return false;
        }
        $this->group_list = $response['data'];
        return $this->group_list;
    }

    public function info ($message,$verbose) {
        if ($verbose) {
            echo $message;
        }
    }

    private function log ($message) {
        if (defined('STANNP_ERROR_LOG') && STANNP_ERROR_LOG) {
            error_log ($message);
        }
    }

    public function mailpieces ($campaign_id) {
        $response = $this->curl_get ('reporting/campaignSingles/'.$campaign_id);
        if (!$response['success']) {
            $this->exception (112,"Failed to get mailpieces for campaign #$campaign_id");
            return false;
        }
        return $response['data'];
    }

    public function memberships ($recipient_id) {
        $response = $this->curl_get ('groups/memberships/'.$recipient_id);
        if (!$response['success']) {
            $this->exception (113,"Failed to get membership of groups for recipient #$recipient_id");
            return false;
        }
        return $response['data'];
    }

    private function recipient_delete ($id,$v=false) {
        $this->info ('    Deleting user #$id\n',$v);
        $response = $this->curl_post ('recipients/delete/',['id'=>$id]);
        if (!$response['success']) {
            $this->exception (114,"Failed to delete recipient #$id");
            return false;
        }
        return $response['data'];
    }

    public function recipients ($name) {
        $campaign = $this->campaign ($name);
        if (!$campaign) {
            $this->exception (115,"Could not find campaign '$name'");
            return false;
        }
        return $campaign->recipient_list;
    }

    public function recipients_redact ($campaign_left_match,$v=false) {
        $ids = $this->recipients_redactable_ids ($campaign_left_match,$v);
        $this->info ("Redacting ".count($ids)." recipients\n",$v);
        foreach ($ids as $id) {
// TODO: be brave and switch it on
            $this->info ("    Eventually deleting user #$id\n",$v);
//            $this->recipient_delete ($id);
//            $this->info ("    Deleted user #$id\n",$v);
        }
        return true;
    }

    public function recipients_redactable_ids ($campaign_left_match,$v=false) {
        $campaign_left_match = trim ($campaign_left_match);
        if (strlen($campaign_left_match)<STANNP_REDACT_SCOPE_LEN) {
            $this->exception (116,"Redaction requires a campaign name left-matching string for limiting scope");
            return false;
        }
        $response = $this->curl_get ('recipients/list/');
        if (!$response['success']) {
            $this->exception (117,"Failed to get all recipients");
            return false;
        }
        $campaigns = $this->campaigns ();
        $redactable = [];
        foreach ($campaigns as $c) {
            $this->info ("Campaign #{$c['id']} {$c['name']}\n",$v);
            if (strpos(trim($c['name']),$campaign_left_match)!==0) {
                $this->info ("    skipping (does not begin with '$campaign_left_match')\n",false);
                continue;
            }
            $c = $this->campaign ($c['name']);
            if ($c['status']=='complete') {
                $this->info ("    complete\n",$v);
                $this->info ("    ".count($c['recipient_list'])." recipients\n",$v);
                $c = $this->campaign ($c['name']);
                $delivered = [];
                foreach ($c['recipient_list'] as $i=>$r) {
                    if ($r['mailpiece_status']=='delivered') {
                        $delivered[$r['id']] = true;
                    }
                }
                $this->info ("        ".count($delivered)." delivered\n",$v);
                if (count($delivered)==count($c['recipient_list'])) {
                    $this->info ("  redactable: ".count($delivered)."\n",$v);
                    foreach ($delivered as $id=>$bool) {
                        $redactable[$id] = true;
                    }
                }
            }
            else {
                $this->info ("    incomplete\n",$v);
            }
        }
        if ($count=count($redactable)) {
            $this->info ("$count recipients found to redact but:\n",$v);
            $this->info ("Do not redact a recipient if either in a campaign is incomplete or having a mailpiece that is undelivered\n",$v);
            foreach ($campaigns as $c) {
                $this->info ("    Campaign #{$c['id']} {$c['name']}\n",$v);
                $c = $this->campaign ($c['name']);
                foreach ($c['recipient_list'] as $i=>$r) {
                    if (array_key_exists($r['id'],$redactable)) {
                        if ($c['status']!='complete' || $r['mailpiece_status']!='delivered') {
                            $this->info ("        Removing recipient #{$r['id']} : {$r['full_name']} : {$r['mailpiece_status']} :: campaign #{$c['id']} : {$c['name']} : {$c['status']}\n",$v);
                            unset ($redactable[$r['id']]);
                        }
                    }
                }
            }
        }
        ksort ($redactable);
        $ids = [];
        foreach ($redactable as $id=>$bool) {
            $ids[] = $id;
        }
        $this->info ("Now ".count($ids)." recipients to redact:\n".implode(',',$ids)."\n",$v);
        return $ids;
    }

    public function whoami ( ) {
        $response = $this->curl_get ('users/me');
        if (!$response->success) {
            throw new \Exception ();
                $this->exception (118,"Failed to get account data");
            return false;
        }
        return $response->data;
    }

}

