<?php

namespace Whitelamp;

require __DIR__.'/config.php';

class Stannp {

    protected $timeout;
    protected $url;
    protected $key;
    protected $email_admin;
    protected $email_from;

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

    public function campaign_create ($name,$template_id,$recipients) {
        // Create the group
        $response = $this->curl_get ('groups/new/'.$name);
        if (!$response['success']) {
            throw new \Exception ("Failed to create group $name");
            return false;
        }
        $group_id = $response['data'];
        // Create recipients
        $this->group ($recipients,$group_id);
        $response = $this->curl_post ('recipients/new',$recipients);
        if (!$response['success']) {
            throw new \Exception ("Failed to create recipients for campaign/group $name");
            return false;
        }
        // Add a new campaign using template and group
        $campaign = [
            'name'              => $name,
            'type'              => 'letter',
            'template_id'       => $template_id,
            'group_id'          => $group_id,
            'what_recipients'   => 'all',
            'addons'            => ''
        ];
        $response = $this->curl_post ('campaigns/new',$campaign);
        if (!$response['success']) {
            throw new \Exception ("Failed to create campaign $name");
            return false;
        }
        $campaign_id = $response['data']['id'];
        // Notify the administrator
        mail (
            "Stannp API admin <{$this->email_admin}>",
            "Stannp campaign '$name' is loaded",
            "Campaign #$campaign_id '$name' has been loaded over the API ready for you to approve and book.\n\n",
            "From: {$this->email_from}\n"
        );
        return [
            'name'              => $name,
            'campaign_id'       => $campaign_id,
            'group_id'          => $group_id,
            'template_id'       => $template_id,
            'letters'           => count ($recipients)
        ];
    }

    public function campaigns ( ) {
        $list = [];
        $response = $this->curl_get ('campaigns/list');
        if (!$response['success']) {
            throw new \Exception ("Failed to get campaigns");
            return false;
        }
        return $response['data'];
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
        // something about server configuration constraints
        // Also what is the timeout here?
		$result = file_get_contents (
            $this->url.$request.'?api_key='.$this->key,
            false,
            $context
        );
		$response = json_decode ($result, true);
        if (!$response) {
            $this->error_log (101,'cURL GET error');
            throw new \Exception ('cURL GET error');
            return false;
        }
		return $response;
	}

    private function curl_post ($request,$post,$options=[]) {
        // Modified from PHP manual
        if (!is_array($post) || !is_array($options)) {
            throw new \Exception ('Post and option arguments must be arrays');
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
            $this->error_log (102,curl_error($ch));
            throw new \Exception ("cURL POST error");
            return false;
        }
        curl_close ($ch);
        return json_decode ($result);
    } 

    private function error_log ($code,$message) {
        $this->errorCode    = $code;
        $this->error        = $message;
        if (!defined('STANNP_ERROR_LOG') || !STANNP_ERROR_LOG) {
            return;
        }
        error_log ($code.' '.$message);
    }

    public function group (&$recipients,$group_id) {
        foreach ($recipients as $i=>$r) {
            $recipients[$i]['group_id'] = $group_id;
        }
    }

    public function recipients ($group_id) {
        $response = $this->curl_get ('recipients/list/'.$id);
        if (!$response['success']) {
            throw new \Exception ("Failed to get recipients for group $group_id");
            return false;
        }
        return $response['data'];
    }

    public function whoami ( ) {
        $response = $this->curl_get ('users/me');
        if (!$response['success']) {
            throw new \Exception ("Failed to get account data");
            return false;
        }
        return $response['data'];
    }

}

