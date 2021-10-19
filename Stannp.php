<?php

namespace Whitelamp;

require __DIR__.'/config.php';

class Stannp {

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
            'what_recipients'   => 'all'
            'addons'            => '',
        ];
        $response = $this->curl_post ('campaigns/new',$campaign);
        if (!$response['success']) {
            throw new \Exception ("Failed to create campaign $name");
            return false;
        }
        $campaign_id = $response['data']['id'];
        // Notify the administrator
        mail (
            "Stannp API admin <".STANNP_EMAIL_ADMIN.">",
            "Stannp campaign '$name' is loaded",
            "Campaign #$campaign_id '$name' has been loaded over the API ready for you to approve and book.\n\n",
            "From: ".STANNP_EMAIL_FROM."\n"
        );
    }

    public function campaigns ( ) {
        $list = [];
        $response = $this->curl_get ('campaigns/list');
        if ($response['success'] != true) { // docs say true, real life says 1
            throw new \Exception ("Failed to get campaigns");
            return false;
        }
/*
        $campaigns_list = $response['data'];
        foreach ($campaigns_list as $c) {
            $list[] = array ('id' => $c['id'], 'name' => $c['name'], 'send_date' =>$c['send_date'], 'recipients_group' => $c['recipients_group'] );
        }
        return $list;
*/
        return $response['data'];
    }

	// curl_get from stannp docs
	private function curl_get ($request) {   
		$opts = [
		    'http' => [
		        'method'  => 'GET',
		        'header'  => 'Content-type: application/x-www-form-urlencoded'
		    ]
		];
		$context = stream_context_create ($opts);
        // Is file_get_contents() the best way here? Seem to remember
        // something about server configuration constraints
		$result = file_get_contents (
            STANNP_API_URL.$request."?api_key=".STANNP_API_KEY,
            false,
            $context
        );
		$response = json_decode ($result, true);
		return $response;
	}

	// curl_post lifted from the PHP manual - switch to stannp version?
    private function curl_post ($request,$post,$options=[]) {
    /*
        * Send a POST requst using cURL
        * @param array $post values to send
        * @param array $options for cURL
        * @return string
    */
        if (!is_array($post) || !is_array($options)) {
            throw new \Exception ('Post and option arguments must be arrays');
            return false;
        }
        $defaults = array (
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => STANNP_API_URL.$request."?api_key=".STANNP_API_KEY,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_POSTFIELDS => http_build_query ($post)
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

	public function recipients ($group_id) {
		$response = $this->curl_get ('recipients/list/'.$id);
		if ($response['success'] != true) { //docs say true, real life says 1
	        throw new \Exception ("Failed to get recipients for group $group_id");
	        return false;
		}
/*
		$recipients_list = $response['data'];
		foreach ($recipients_list as $r) {
			$list[] = array (
            'id'		=> $r['id'],
            'account_id'=> $r['account_id'],
            'email'		=> $r['email'],
            'title'		=> $r['title'],
            'firstname'	=> $r['firstname'],
            'lastname'	=> $r['lastname'],
            'company'	=> $r['company'],
            'job_title'	=> $r['job_title'],
            'address1'	=> $r['address1'],
            'address2'	=> $r['address2'],
            'address3'	=> $r['address3'],
            'city'		=> $r['city'],
            'county'	=> $r['county'],
            'country'	=> $r['country'],
            'postcode'	=> $r['postcode'],
        	);
		}
    	return $list;
*/
        return $response['data'];
    }

    public function group (&$recipients,$group_id) {
        foreach ($recipients as $i=>$r) {
            $recipients[$i]['group_id'] = $group_id;
        }
    }

    public function whoami ( ) {
    	return $this->curl_get ('users/me');
    }

}

