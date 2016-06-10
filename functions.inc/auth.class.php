<?php

class RestAuth {
	public $api;
	
	function __construct(&$api) {
		$this->api = $api;
	}
	
	/**
	 * Returns a hash against which we will build a signature
	 * @param string - originator toekn
	 * @param string - the exact url that was requestd, not including the protocol part (http://)
	 * @param string - the request verb
	 * @param string - the nonce as sent by the originator
	 * @param string - body of request
	 *
	 * @return string
	 */
	function get_data_hash($token, $url, $verb, $nonce, $body) {
		$a = hash($this->api->hash_algo, $url . ':' . strtolower($verb));
		$b = hash($this->api->hash_algo, $token . ':' . $nonce);
		$c = hash($this->api->hash_algo, base64_encode($body));
		return hash($this->api->hash_algo, $a . ':' . $b . ':' . $c);
	}
	
	/**
	 * @param string - hash data as returned by get_data_hash()
	 * @param string - token key (defaults to 0 if key is blank)
	 *
	 * @retun string - signature
	 */
	function get_signature($data, $key = 0) {
		return hash_hmac($this->api->hash_algo, $data, $key);
	}

	/*
	 * Test if a token passes authentication
	 *
	 */
	function is_authenticated($body_hash, $tokenkey, $sig) {
		$sig_should_be = $this->get_signature($body_hash, $tokenkey);
		$is_authenticated = $sig_should_be === $sig;

		if (!$is_authenticated) {		
			if ($this->api->log) {
				$this->api->log->event('AAA invalid signature',  'Should be ' . $sig_should_be);
			}
		}
		return $is_authenticated;
	
	}

	/**
	 * Test if a token is autherized to call a route
	 *
	 * @param array - list of modules
	 * @param array - list of users
	 * 
	 * @return array
	 */
	function is_authorized($mods, $users) {
		$opts['mod_auth']	= false;
		$opts['user_auth']	= true;
		$opts['mods']		= $mods;
		$opts['users']		= $users;
		$self = $this;	
		
		$this->api->router->each_route(function($r, &$opts) use ($self) {
			$mods	= $opts['mods'];
			$users	= $opts['users'];

			//no need to check indevidual perms if this route doesnt require them
			if ($r['authorization'] === false) {
				$opts['mod_auth'] = $opts['user_auth'] = true;
				return true;
			}

			//mod auth supersedes user auth, ensure this module is permissible
			if (!empty($mods) && (in_array('*', $mods) || in_array($r['module'], $mods))) {
				$opts['mod_auth'] = true;
			} else {
				if ($self->api->log) {
					$self->api->log->event('AAA Error:', 
						array(
							'status'	=> 'Uauthorized route', 
							'route'		=> $r['module'] . ': ' . $r['map']['matched']
						)
					);
				}
			}
			
			//user auth
			//see if this route requires user auth, defined in the map in modules.xml
			if ($r['type'] == 'user') {
				if (isset($r['id'])) {
					if (in_array('*', $users) || in_array($r['id'], $users)) {
						$opts['user_auth'] = true;
					} 
				} else {
					//if there is no id, ensure were alowed to view all
					if (in_array('*', $users)) {
						$opts['user_auth'] = true;
					}
				}

			}
		}, $opts);

		if ($this->api->log) {
			$this->api->log->event('AAA results', $opts + $this->api->AAA);
		}
		return $opts['mod_auth'] && $opts['user_auth'];
	}
	
	/**
	 * Check if a signature has been used before. Signatures are the ONLY thing
	 * standing between us and a replay attack (i.e. where the attacher captures the message
	 * and it again - exactly as is (i.e. with a proper signature)), 
	 * forcing us to reject any KNOWN signature on the basis that, 
	 * considering our elaborate hasing & building of the signature
	 * forcing them to almost unequivocally be unique,
	 * it is probobly a replay
	 *
	 * @param string - the signature to test
	 *
	 * @return bool
	 *
	 */
	function is_new_sig($sig) {
		return restapi_get_signature($sig);
	}
	
	/**
	 * Get count of requests by a token in the last hour
	 *
	 * @param string - token
	 */
	function usage_count($token) {
		return restapi_get_token_usage($token, time() - 3600);
	}
}
