<?php

namespace Whitelamp;

require __DIR__.'/config.php';

/*
https://www.stannp.com/uk/direct-mail-api/guide#introduction
General flow for sending letters is:
- create recipients
- create new group from recipients
- create campaign with group id and template id
- approve and book
*/

class Stannp {

	// curl_get from stannp docs
	private function curl_get($request)
	{   
		$opts = array(
		    'http' => array(
		        'method'  => 'GET',
		        'header'  => 'Content-type: application/x-www-form-urlencoded'
		    )
		);
		$context  = stream_context_create($opts);
		$result = file_get_contents(STANNP_API_URL.$request."?api_key=" . STANNP_API_KEY, false, $context);
		$response = json_decode($result, true);

		return $response;
	}

	// curl_post lifted from the PHP manual - switch to stannp version?
    private function curl_post ($url,$post,$options=[]) {
    /*
        * Send a POST requst using cURL
        * @param string $url to request
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
            CURLOPT_URL => $url,
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
        return json_decode($result);
    } 

    private function error_log ($code,$message) {
        $this->errorCode    = $code;
        $this->error        = $message;
        if (!defined('STANNP_ERROR_LOG') || !STANNP_ERROR_LOG) {
            return;
        }
        error_log ($code.' '.$message);
    }

	public function get_campaigns() {
		$list = array();
		$response = $this->curl_get('campaigns/list');
		if ($response['success'] != true) { //docs say true, real life says 1
	        throw new \Exception ('Failed to get campaigns');
	        return false;
		}
		$campaigns_list = $response['data'];
		foreach ($campaigns_list as $c) {
			$list[] = array ('id' => $c['id'], 'name' => $c['name'], 'send_date' =>$c['send_date'], 'recipients_group' => $c['recipients_group'] );
		}
    	return $list;
    }

	public function get_recipients_group($id) {
		$list = array();
		$response = $this->curl_get('recipients/list/'.$id);
		if ($response['success'] != true) { //docs say true, real life says 1
	        throw new \Exception ('Failed to get recipients_group');
	        return false;
		}
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
    }

    public function send_letters($data) {
        // Take some data, use some constants
        // and make Stannp print and post letters
        throw new \Exception ('Whoa Leslie... not built yet innit?');
        return false;
    }

    public function whoami() {
    	return $this->curl_get('users/me');
    }

}

