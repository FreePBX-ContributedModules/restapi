<?php
/**
 * RETURN CODES AND MEANING
 *
 * --RESPONSE CODES--
 *
 * 200 OK					Indicates a nonspecific success
 * 201 Created				Sent primarily by collections and stores but sometimes also by
 *							controllers, to indicate that a new resource has been created
 * 202 Accepted				Sent by controllers to indicate the start of an asynchronous action
 * 204 No Content			Indicates that the body has been intentionally left blank
 * 301 Moved Permanently	Indicates that a new permanent URI has been assigned to the client’s
 *							requested resource
 * 303 See Other			Sent by controllers to return results that it considers optional
 * 304 Not Modified			Sent to preserve bandwidth (with conditional GET)
 * 307 Temporary Redirect	Indicates that a temporary URI has been assigned to the client’s
 *							requested resource
 *
 * --ERROR CODES--
 *
 * 400 Bad Request				Indicates a nonspecific client error
 * 401 Unauthorized				Sent when the client either provided invalid credentials
 *								or forgot to send them
 * 402 Forbidden				Sent to deny access to a protected resource
 * 404 Not Found				Sent when the client tried to interact with a URI that the
 *								REST API could not map to a resource
 * 405 Method Not Allowed		Sent when the client tried to interact using an unsupported HTTP method
 * 406 Not Acceptable			Sent when the client tried to request data in an unsupported
 *								media type format
 * 409 Conflict					Indicates that the client attempted to violate resource state
 * 412 Precondition Failed		Tells the client that one of its preconditions was not met
 * 415 Unsupported Media Type	Sent when the client submitted data in an unsupported media type format
 * 500 Internal Server Error	Tells the client that the API is having problems of its own
 *
 */

class Api{
	public
		$body_data,
		$db,
		$ctype,
		//$req = new stdClass,//request
		//$res = new stdClass,//response
		$maps = array(),
		$nonce,
		$responses = array(
					200	=> 'OK',
					201	=> 'Created',
					202	=> 'Accepted',
					204	=> 'No Content',
					301	=> 'Moved Permanently',
					303	=> 'See Other',
					304	=> 'Not Modified',
					307	=> 'Temporary Redirect',
					400	=> 'Bad Request',
					401	=> 'Unauthorized',
					402	=> 'Forbidden',
					404	=> 'Not Found',
					405	=> 'Method Not Allowed',
					406	=> 'Not Acceptable',
					409	=> 'Conflict',
					412	=> 'Precondition Failed',
					415	=> 'Unsupported Media Type',
					500	=> 'Internal Server Error',
					503 => 'Service Unavailable'
		);

	function __construct() {
		global $amp_conf, $db;
		$this->amp_conf				= & $amp_conf;
		$this->auth					= new RestAuth($this);
		$this->db					= & $db;//import our db object
		$this->hash_algo			= 'sha256';
		$this->opts					= restapi_opts_get();
		if ($this->opts['logging'] == 'enabled') {
			$this->log					= new RestLogger($this);
		} else {
			$this->log = NULL;
		}
		$this->mods					= modulelist::create($this->db);

		//matched routes, if any
		$this->_register_routes();

		$this->res					= new stdClass;
		$this->res->nonce			= restapi_tokens_generate();

		//setup $this->req
		$this->_get_req();
		if ($this->log) {
			$this->log->init();
		}


	}


	/**
	 * Register maps with router
	 *
	 */
	function _register_routes() {
		/**
		 * get's a list of al registered api maps. Origionally this info was pulled
		 * out of each modules's module.xml. Here, we are actually fethcing a cached
		 * copy
		 *
		 */
		//module_getinfo(false,false,true);
		foreach ($this->mods->module_array as $mod => $details) {
			//ensure module is enabled
			//MODULE_STATUS_ENABLE == 2
			if ($details['status'] != 2) {
				continue;
			}

			//include routes
			$routes_dir = dirname(__FILE__)
				. '/../modules/'
				. $details['rawname'];
			if (is_dir($routes_dir)
				&& file_exists($routes_dir . '/maps.php')
			) {
				include_once($routes_dir . '/maps.php');
			}

			//extract maps from module.xml
			if (empty($details['apimap'])) {
				continue;
			}

			foreach ($details['apimap'] as $verb => $maps) {
				foreach ($maps as $url => $map) {
					$this->add_map($verb, $url, $mod, $map);
				}
			}
		}

		unset($details);
		//dbug($this->maps);
		$this->router = new Router($this);
		//iterate over maps and pass to Router
		foreach ($this->maps as $verb => $urls) {
			foreach ($urls as $url => $maps) {
				foreach ($maps as $map => $details) {
					$target = array();
					if (isset($details['controller']) && $details['controller'])  {
						$target['controller'] = $details['controller'];
					}
					if (isset($details['method']) && $details['method']) {
						$target['method'] = $details['method'];
					}
					$details['target'] 	= $target;
					$this->router->add_map($details);
				}
			}
		}

		//get the routes built up
		$this->router->generate_routes();

		if ($this->log) {
			$this->log->event('Router', $this->router);
		}
	}

	/**
	 * Add a map to the maps global
	 * @param string - verb
	 * @param string route - the map route
	 * @param string module - the host module
	 * @param array - other map related options
	 *
	 * @TODO: add support for variable restrictions (by regex)
	 */
	 function add_map($verb, $route, $module, $opts) {
	 	$opts['url']	= $route;
	 	$opts['module']	= $module;
	 	$opts['verb']	= $verb;
	 	$this->maps[$verb][$route][] = $opts;
	 }

	/**
	 * Gets all the sent headers
	 */
	function _get_req() {
		$this->req = isset($this->req) ? $this->req : new stdClass;

		//dbug($_SERVER);
		$h = array(
			'address'		=> '',
			'content_type'	=> '',
			'host' 			=> '',
			'ip'			=> '',
			'nonce'			=> '',
			'port'			=> '',
			'token'			=> '',
			'tokenkey'		=> '',
			'timestamp'		=> '',
			'user_agent'	=> '',
			'uri'			=> '',
			'signature'		=> ''
		);
		foreach ($_SERVER as $k => $v) {
			switch ($k) {
				case 'HTTP_HOST':
					$h['host'] = $v;
					break;
				case 'CONTENT_TYPE':
					$h['content_type'] = $v;
					break;
				case 'SERVER_NAME':
					$h['address'] = $v;
					break;
				case 'SERVER_PORT':
					$h['port'] = $v;
					break;
				case 'REMOTE_ADDR':
					$h['ip'] = $v;
					break;
				case 'REQUEST_URI':
					$h['uri'] = $v;
					break;
				case 'HTTP_TOKEN':
					$h['token'] = $v;
					break;
				/*case 'HTTP_TOKEN_KEY':
					$h['token_key'] = $v;
					break;*/
				case 'HTTP_NONCE':
					$h['nonce'] = $v;
					break;
				case 'HTTP_SIGNATURE':
					$h['signature'] = $v;
					break;
				case 'HTTP_USER_AGENT':
					$h['user_agent'] = $v;
				default:
					break;
			}
		}
		//always add fake data if none is set, otherwise auth test will never fail
		//when no data is passed
		$this->req->token	= $h['token'] ? $h['token'] : md5(time());
		$this->req->nonce	= $h['nonce'] ? $h['nonce'] : md5(time());
		$h['protocol']		= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"
							? "https" : "http";
		$h['url']			= $h['host'] . $h['uri'];

		//get data associated with this token
		$opts = restapi_tokens_get($this->req->token, 'token');

		if ($opts) {
			foreach ($opts as $k => $v) {
				$this->req->token_opts->$k = $v;
			}
		} else {
			//add defaults
			$this->req->token_opts = new StdClass;
			$this->req->token_opts->token_status	= 'not_found';
			$this->req->token_opts->users			= array();
			$this->req->token_opts->modules			= array();
			$this->req->token_opts->allow			= array();
			$this->req->token_opts->deny			= array();
			$this->req->token_opts->name			= '';
			$this->req->token_opts->rate			= '';
			$this->req->token_opts->token			= '';
			$this->req->token_opts->tokenkey		= '';
			$this->req->token_opts->assoc_user 		= '';

			if ($this->opts['token'] != '') {
				switch ($this->opts['status']) {
				case 'normal':
					$this->req->token_opts->token_status = 'enabled';
					break;
				default:
					$this->req->token_opts->token_status = 'disabled';
					break;
				}
				$this->req->token_opts->token = $this->opts['token'];
				$this->req->token_opts->tokenkey = $this->opts['tokenkey'];
				$this->req->token_opts->name = 'general';
				$this->req->token_opts->users = array('*');
				$this->req->token_opts->modules = array('*');
				$this->req->token_opts->rate = -1;
			}
		}

		//headers
		$this->req->headers			= $h;
		//build request body hash
		$this->req->body_hash		= $this->auth->get_data_hash(
											$this->req->token,
											$this->req->headers['url'],
											$this->router->verb,
											$this->req->nonce,
											$this->router->body
									);
		//dbug($h);
		$this->req->files		= $_FILES;

		if ($this->log) {
			$this->log->event('Request', $this->req);
		}

	}

	/**
	 * Call an action if the map was matched
	 */
	function main() {

		//return if server is down
		switch ($this->opts['status']) {
			case 'normal':
				break;//nothing to do here
			case 'tempdown':
				$this->die_api(503,
					array('status' => 'error',
							'msg' => 'Currently Unavailable'),
					array('event' => 'server status',
							'data' => $this->opts['status'])
				);
				break;
			case 'maint':
				$this->die_api(503,
					array('status' => 'error',
							'msg' => 'Down for Maintenance'),
					array('event' => 'server status',
							'data' => $this->opts['status'])
				);
				break;
			case 'disabled':
				$this->die_api(500,
					array('status' => 'error', 'msg' => 'Server Disabled'),
					array('event' => 'server status',
							'data' => $this->opts['status'])
				);
				break;
		}

		//make sure we have routes to precede with!
		if (!$this->router->routes) {
			$this->die_api(404,
				array('status' => 'error', 'msg' => 'Route not found'),
				array('event' => 'No Routes',
					array('routes' => $this->router->routes))
			);
		}

		//AAA
		//ensure token is enabled. If no token is found, this will NOT fail
		switch ($this->req->token_opts->token_status){
			case 'enabled':
				//all is good!
				break;
			case 'not_found':
				//not good, but lets see if there is a route that can handel this
				//anyway
				break;
			default:
				$this->die_api(404,
					array('status' => 'error', 'msg' => 'AAA Error'),
					array('event' => 'AAA failed', 'data' => 'Token is not Enabled!')
				);
				break;
		}

		//AAA
		$this->AAA['sig'] = $this->auth->is_authenticated(
					$this->req->body_hash,
					$this->req->token_opts->tokenkey,
					$this->req->headers['signature']);
		$this->AAA['auth'] = $this->auth->is_authorized(
					$this->req->token_opts->modules,
					$this->req->token_opts->users);
		$this->AAA['rate'] = $this->auth->usage_count($this->req->token);

		//ensure we found this token, signature and auth
		if (!$this->AAA['sig'] || !$this->AAA['auth']) {
			//even if we didnt pass auth, if ALL routes do not requre auth
			//we can let the call through anyway
			$needs_auth = '';
			$this->router->each_route(function($r) use (&$needs_auth) {
				if ($needs_auth !== true && $r['authentication'] === false) {
					$needs_auth = false;
				}
			});

			if ($needs_auth !== false) {
				//for security reasons, we should try to stay crypic here
				$this->die_api(401,
					array('status' => 'error', 'msg' => 'AAA Error'),
					array('event' => 'AAA failed', 'data' => $this->AAA)
				);
			} else {
				if ($this->log) {
					$this->log->event('AAA failed',
						'Invalid Token/Signature, but we dont care because no routes '
						. 'require proper authentication');
				}
			}
		}

		//rate throttling of token
		if ($this->req->token_opts->rate != -1 && $this->AAA['rate'] > $this->req->token_opts->rate) {

			$needs_accouning = '';
			$this->router->each_route(function($r) use (&$needs_accouning) {
				if ($needs_accouning !== true && $r['accounting'] === false) {
					$needs_accouning = false;
				}
			});

			if ($needs_accouning !== false) {
				$this->die_api(401,
					array('status' => 'error', 'msg' => 'AAA Token Limit exceded'),
					array('event' => 'AAA Usage Rate', 'data' => $this->AAA['rate'])
				);
			} else {
				if ($this->log) {
					$this->log->event('AAA Usage Rate',
						$this->AAA['rate'] . ', but no routes care ');
				}
			}
		}

		$this->include_controllers();

		//finally, call the controllers
		//copy $this to $self as php 5.3 doesnt allow $this in an anonymous function
		$self = $this;
		$results = $this->router->each_route(function($r) use($self){
			$body		= false;
			$res		= $r['inst']->$r['method']($r['params']);

			//dbug($r['controller_name'], $self->db->last_query);
			//controllers should NEVER return false
			if ($res === false) {
				$self->die_api(
					404,
					array('status' => 'error', 'msg' => 'Invalid Response'),
					array('event' => 'Call controller loop', 'data' => array(
						'route' => $r,
						'msg'	=> 'Controller/method returned an invalid response',
						'res'	=> $res
					), true, true));
			} elseif (is_array($res) || ($res && $res !== true)) {
				$self->add_header(200);
				$self->add_body($res);
			} else {
				switch ($self->router->verb) {
					case 'GET':
						$self->add_header(204);
						break;
					case 'PUT':
					case 'DELETE':
						$self->add_header(200);
						break;
					case 'POST':
						$self->add_header(201);
						break;
				}
			}

		});

		$this->response();
		if ($this->log) {
			$this->log->event('response called');
		}
		exit();

	}

	/**
	 * Ensures that controllers exist and instantiates them
	 */
	function include_controllers() {
		if (!$this->router->routes) {
			if ($this->log) {
				$this->log->event('find routes', array('No Routes Found!'));
			}
			$this->die_api(404, array('status' => 'error', 'msg' => 'No routes found!'));
		}

		$self = $this;
		$this->router->each_route(function(&$r) use($self){
			//return if we dont have a controller
			if (!isset($r['controller'])) {
				$self->die_api(404,
					array('status' => 'error', 'msg' => 'route or controller not found'),
					array('event' => 'find controller', 'data' => array(
						'status' => 'not found', 'controller' => $r['controller']
					))
				);
			}

			//try to include a controller
			$mod_base = $self->amp_conf['AMPWEBROOT']
					. '/admin/modules/';
			$path = '';
			if (isset($r['controller_path'])) {
				$paths[] = $mod_base
						. 'restapi/modules/'
						. $r['module']
						. '/controllers/'
						. $r['controller_path'];
				$paths[] = $mod_base
						. $r['module'] . '/'
						. $r['controller_path'];
			}
			$paths[] = $mod_base
					. 'restapi/modules/'
					. $r['module']
					. '/controllers/'
					. 'class.' . $r['controller_name'] . '.php';
			$paths[] = $mod_base
					. $r['module'] . '/'
					. 'class.' . $r['controller_name'] . '.php';

			foreach ($paths as $p) {
				if (file_exists($p)) {
					$path = $p;
					break;
				}
			}

			$req = file_exists($path) && include_once($path);

			//return if we cant include the proposed controller
			if(!$req || !class_exists($r['controller_name'])) {
				$self->die_api(404,
					array('status' => 'error', 'msg' => 'controller not found'),
					array('event' => 'include controller', 'data' =>
						array('controller' => $r['controller_name'],
						'status' => 'not found',
						'path'	=> $path)
					)
				);
			}

			//ensure the specifc modules functions are included
			$mod_controller = $mod_base . $r['module'] . '/functions.inc.php';
			if (file_exists($mod_controller) && !include_once($mod_controller)) {
				if ($this->log) {
					$self->log->event('include module', array(
						'status'	=> 'Could not include module',
						'path'		=> $mod_controller
					));
				}
			}

			//include dependencies
			$mod = $self->mods->module_array[$r['module']];

			if (isset($mod['depends']['module'])) {
				if (!is_array($mod['depends']['module'])) {
					$deps[] = $mod['depends']['module'];
				} else {
					$deps = $mod['depends']['module'];
				}

				foreach ($deps as $d) {
					$d = explode(' ', $d);
					$mod = isset($self->mods->module_array[$d[0]])
							? $self->mods->module_array[$d[0]]
							: false;

					//ensure dependcy exits
					if (!$mod) {
						$self->die_api(500,
							array('status' => 'error',
								'msg' => 'Missing Dependency'),
							array('event' => 'Missing Dependency',
								'data' => $d)
						);
					}

					//ensure dependecy is enabled
					if ($self->mods->module_array[$d[0]]['status'] != 2) {
						$self->die_api(500,
							array('status' => 'error',
								'msg' => 'Missing Dependency'),
							array('event' => 'Dependency Disabled',
								'data' => $self->mods->module_array[$d[0]])
						);
					}

					//only include active modules
					$path = $self->amp_conf['AMPWEBROOT']
							. '/admin/modules/' . $d[0] . '/functions.inc.php';
					if (file_exists($path) && !include_once($path)) {
						$self->die_api(500,
							array('status' => 'error',
								'msg' => 'Missing Dependency'),
							array('event' => 'Could not include dependency',
								'data' => array(
									'path' => $path,
									'module' =>
										$self->mods->module_array[$d[0]]
								))
						);
					}
				}
			}

			//instantiate controller
			$controller	= new $r['controller_name']($self);
			$method		= $r['action'] ? $r['action'] : 'index';

			//return if we cant find a method, defaults to index
			if (!method_exists($controller, $method)) {
				$self->die_api(404,
					array('status' => 'error', 'msg' => 'method not found'),
					array('event' => 'method not found', 'data' => $method)
				);
			}

			//add instantiated object so that we dont have to do it again
			$r['inst']		= $controller;

			//confirm method were using, usefull if we defualted to index
			$r['method']	= $method;
		});
	}

	/**
	 * Function to be called when things go wrong, provides a quick way to send the
	 * erorr and exit
	 * @param int - header error
	 * @param mixed - error message to be appended to body
	 * @param bool - true will send all output and exit
	 *
	 */
	function die_api($header, $error = '', $log = array(), $exit = true, $flush_body = false) {
		$this->add_header($header);
		if ($flush_body) {
			$this->body_data = array();
		}
		if ($error) {
			$this->add_body($error, 'error');
		}
		if ($log) {
			$log['event']	= isset($log['event'])
							? $log['event']
							: '[no event set]';
			$log['data']	= isset($log['data'])
							? $log['data']
							: '[no data set]';
			$bt = debug_backtrace();
			$trigger = $bt[0]['file'] . ':' . $bt[0]['line'] . PHP_EOL;
			if ($this->log) {
				$this->log->event($log['event'], $log['data'], $trigger);
			}
		}
		if ($exit) {
			$this->response();
			exit();
		}
		return true;
	}

	/**
	 * Prepare headers to be returned
	 * @pram mixed - type of header to be returned
	 * @pram mixed optional - value header should be set to
	 * NOTE: if just type is set, it will be assumed to be a value
	 */
	function add_header($type, $value = '') {

		if ($type && !$value) {
			$value = $type;
			$type = 'HTTP/1.1';
		}

		//clean up type
		$type = str_replace(array('_', ' '), '-', trim($type));
		//HTTP responses headers
		if ($type == 'HTTP/1.1') {
			$value = ucfirst($value);
			//ok is always fully capitalized, not just its first letter
			if ($value == 'Ok') {
				$value = 'OK';
			}

			if (array_key_exists($value, $this->responses)
				|| $value = array_search($value, $this->responses)
			) {
				$this->res->headers['HTTP/1.1'] = $value . ' ' . $this->responses[$value];
				return true;
			} else {
				return false;
			}
		} //end HTTP responses

		//all other headers. Not sure if/how we can validate them more...
		$this->res->headers[$type] = $value;

		return true;
	}


	/**
	 * Format msg based on the clients request
	 * @pram array - msg to be added
	 *
	 */
	function add_body($msg, $type = '') {

		if (
			$type == 'user' && !$this->router->current_route('id')
			|| $type == ''
				&& $this->router->current_route('type') == 'user'
				&& !$this->router->current_route('id')
		){
			//dbug('type', $type);
			//dbug('current_route type', $this->router->current_route('type'));
			//dbug('id', $this->router->current_route('id'));
			$this->res->body['users'][] = $msg;
		} else {
			$this->res->body[] = $msg;
		}

	}

	/**
	 * Process body in returnable format
	 */
	function _process_body($body_data) {
		if (isset($body_data['user'])) {
			$users = array();
			foreach ($body_data['user'] as $array => $data) {
				foreach ($data as $user => $values) {
					if (isset($users[$user])) {
						$users[$user] + $values;
					} else {
						$users[$user] = $values;
					}
				}
			}
			unset($body_data['user']);
			$body_data = array_merge($body_data, $users);
		}

		//remove the first elements from the array
		if (is_array($body_data)) {
			$data = array_shift($body_data);
		}

		if (!is_array($data)) {
			$data = array($data);
		}

		//if there are more parts, append them to the array
		if (count($body_data) > 0) {
			foreach ($body_data as $bdata) {
				if (!is_array($bdata)) {
					$bdata = array($bdata);
				}
				$data = array_merge($data, $bdata);
			}
		}

	/*	switch ($this->req->headers['content_type']) {
			case 'application/xml':
				$this->add_header('Content-Type', 'application/xml');
				return false;//TODO: make this work
				break;
			case 'text/plain':
				$this->add_header('Content-Type', 'text/plain');
				return implode($msg);
				break;
			case 'application/json':
			default:
	*/			$this->add_header('Content-Type', 'application/json');
				//return json_encode($data);
				//temp, this must go in php 5.4, where prettyprint is native
				return json_print_pretty(json_encode($data), '  ');
	/*			break;
		}*/

	}

	/**
	 * Send headers
	 */
	function send_headers() {
		if ($this->log) {
			$this->log->event('sending headers', $this->res->headers);
		}

		//send http header
		if (isset($this->res->headers['HTTP/1.1'])) {
			header('HTTP/1.1 ' . $this->res->headers['HTTP/1.1']);
			unset($this->res->headers['HTTP/1.1']);
		} else {
			header('HTTP/1.1 200 OK'); //defualt to 200
		}

		//send all headers, if any
		if ($this->res->headers) {
			foreach ($this->res->headers as $k => $v) {
				header($k . ': ' . $v);
				//unlist sent headers, as this mehtod can be called more than once
				unset($this->res->headers[$k]);
			}
		}
	}

	/**
	 * Respond to client
	 */
	function response() {
		//prepare body
		if (!empty($this->res->body)) {
			if ($this->log) {
				$this->log->event('Processing Body', $this->res->body);
			}
			$body = $this->_process_body($this->res->body) . PHP_EOL;
			//send length header
			$this->add_header('Content-Length', strlen($body));
		} else {
			$body = '';
			$this->add_header('Content-Length', 0);//no body, just sayin'
		}

		//report the framework version
		$this->add_header('App-Version', getversion());

		//send the nonce as a header
		$this->add_header('Nonce', $this->res->nonce);

		//build response signature
		$data = $this->auth->get_data_hash(
			$this->opts['token'],
			$this->req->headers['url'],
			$this->router->verb,
			$this->res->nonce,
			$body
		);
		$sig = $this->auth->get_signature($data, $this->opts['tokenkey']);
		$this->add_header('Signature', $sig);



		$this->send_headers();

		echo $body;
		if ($this->log) {
			$this->log->event('Response', $this->res);
		}
		return true;
	}
}
?>
