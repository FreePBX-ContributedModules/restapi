<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/**
 * originally based on: http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
 *
 * $router = new Router();
 * // ... some configs ...
 * $controller = $router->controller; // will return name as it appears in url, ex: 'user_images'
 * $controller = $router->controller_name; // will return processed name of controller
 * // for example, if class name in url is 'user_images', then 'controller_name' var will be UserImages
 * $router->action;
 * $router->id; // if parameter :id presents
 * $router->params; // array(...)
 * $router->route_matched; // true - if route found, false - if not
*/
class Router {
	public $request_uri,
			$maps,
			$verb,
			$routes;
	private $api,
			$map_prefix;

 	/**
	 * construct
	 */
	public function __construct($api) {
		$this->api			= $api;
		$this->request_uri	= isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		$this->verb			= $_SERVER['REQUEST_METHOD'];
		$this->routes		= array();
		$this->body			= file_get_contents('php://input');
		$this->map_prefix	= '/rest';
	}

	public function add_map($map) {

		//loop through posible object to ensure that there set
		$items = array(
				'target'	=> array(),
				'url'		=> '',
				'rule'		=> '',
				'verb'		=> '',
				'path'		=> ''
		);
		foreach ($items as $k => $v) {
			$map[$k] = isset($map[$k]) ? $map[$k] : $v;
		}

		if (!$map['verb'] || !$map['url']) {
			return false;
		}
		//outrightly reject maps that arent for our verb type
		if (strtolower($map['verb']) != strtolower($this->verb)) {
			return false;
		}

		$this->maps[] = $map;

		return true;
	}

	/**
	 * Sets route information, called only when we have a match
	 */
	private function add_route($route) {
		$add_route['action']		= isset($route['params']['method'])
									? $route['params']['method']
									: '';
		$add_route['controller']	= $route['params']['controller'];
		$add_route['id']			= isset($route['params']['id'])
									? $route['params']['id']
									: '';
		$add_route['map']['matched']= $route['url'];
		$add_route['module']		= $route['module'];
		$add_route['controller_path']= $route['path'];
		$add_route['type']			= isset($route['type'])
									? $route['type']
									: '';

		//optionaly skip AAA. Dont use this if you dont know
		//what your doing!
		$add_route['authentication'] = isset($route['authentication'])
									? $route['authentication']
									: true;

		$add_route['authorization'] = isset($route['authorization'])
									? $route['authorization']
									: true;

		$add_route['accounting'] = isset($route['accounting'])
									? $route['accounting']
									: true;
		//set parms
		unset($route['params']['controller'], $route['params']['method']);

		if (isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING'], $query_string);
		}

		$body_params = array();
		switch ($_SERVER['CONTENT_TYPE']) {
			case 'application/json':
				$vars = json_decode($this->body);
                if(empty($vars)) {
                    break;
                }
				foreach ($vars as $var_name => $var_value) {
					$body_params[$var_name] = $var_value;
				}
				break;
			case 'application/x-www-form-urlencoded':
				# JSON is highly preferred.  This works because I'm nice.
				parse_str($this->body, $vars);
				foreach ($vars as $var_name => $var_value) {
					$body_params[$var_name] = $var_value;
				}
				break;
			default:
				break;
		}
		$add_route['params'] = array_merge($route['params'], $query_string, $body_params);

		$add_route['controller_name'] = $route['controller'];

		$add_route['map']['raw_segements'] = explode('/', trim($route['url'], '/'));
		$add_route['req_segements'] = explode('/', trim($this->request_uri, '/'));

		$this->routes[] = $add_route;
	}

	/**
	 * Iterate over maps, adding matches to $this->routes
	 */
	public function generate_routes($as_array = false) {
		//return a blank if we dont have data
		if (!$this->maps) {
			return false;
		}

		//ensure map starts with 'rest' and catch the version number if its sent
		//i.e. rest/<module name/ or rest/1/<module name>
		//note: version is arbitrary, it is up to controller authors to decide if/how
		//to use it
		$regex = '/(rest)\/?(?:(\d+)(?:\/)?)?/';
		if (preg_match_all($regex, $this->request_uri, $m, PREG_SET_ORDER)) {
			$m = $m[0];
			if ($m[1] != 'rest') {
				return false;
			}
			$max = 1;//str_replace 's limit is passed by referece, must be passable
			$uri = str_replace($m[0], '', $this->request_uri, $max);
			$this->api->rest_version = isset($m[2]) ? $m[2] : '';
		} else {
			return false;
		}

		//loop through maps for a matching route
		foreach($this->maps as $map) {

			$p_names			= array();
			$p_values			= array();
			$this->conditions	= $map['rule'];

			//build regex to validate maps against
			$url_regex = preg_replace_callback('|:[\w]+|',
								array($this, 'regex_url'),
								$map['url']
						) . '/?';

			if (preg_match('|^' . $url_regex . '$|', $uri, $p_values)){

				array_shift($p_values);

				//break uri in to pieces
				preg_match_all('|:([\w]+)|', $map['url'], $p_names, PREG_PATTERN_ORDER);
				foreach($p_names[0] as $index => $value) {
					$route['params'][substr($value,1)] = urldecode($p_values[$index]);
				}
				foreach($map['target'] as $key => $value) {
					$route['params'][$key] = $value;
				}
				$route = array_merge($route, $map);

				$this->add_route($route);
			}
			unset($p_names, $p_values);
		}

		unset($this->conditions, $this->maps);

	}

	function regex_url($matches) {
		$key = str_replace(':', '', $matches[0]);
		if (array_key_exists($key, (array)$this->conditions)) {
			return '(' . $this->conditions[$key] . ')';
		} else {
			return '([a-zA-Z0-9_.\+\-%]+)';
		}
	}

	/**
	 * Iterate over every route with $callback
	 * routes are passed by reference, so they can be modified if nesesary
	 */
	function each_route($callback, &$opts = array()) {
		foreach($this->routes as &$r) {
			$this->current_route = $r;
			$callback($r, $opts);
			$this->current_route = '';
		}
		//this is VITAL, do not remove
		//see: http://php.net/manual/en/control-structures.foreach.php
		unset($r);
	}

	/**
	 * Safely returns the current route, or a part of it
	 */
	function current_route($peice = '') {
		if ($peice) {
			if (isset($this->current_route, $this->current_route[$peice])) {
				return $this->current_route[$peice];
			} else {
				return '';
			}
		}

		//else
		if (isset($this->current_route)) {
			return $this->current_route;
		} else {
			return array();
		}
	}
}
