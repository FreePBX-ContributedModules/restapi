<?php

class restapi_Fax{
	private $api;
	
	function __construct($api) {
		$this->api = & $api;
	}
	
	function index() {
		return $this->get();
	}
	
	/**
	 * @verb GET
	 * @returns - list of fax settings
	 * @uri /fax
	 */
	function get($params) {
		return fax_get_settings();
	}

	/**
	* @verb GET
	 * @returns - list of fax related modules that may be installed
	 * @uri /fax/detect
	 */
	function get_fax_detect($params) {
		return fax_detect();
	}
	
	/**
	 * Updates the fax settings
	 * @verb POST
	 * @returns - the freshly created settings
	 * @uri /fax
	 */
	function post_fax($params) {
		return fax_save_settings($params);
	}

	/**
	 * Updates the fax settings
	 * @verb PUT
	 * @returns - bool
	 * @uri /fax
	 * NOTE: THIS SHOULD BE A PUT, THE POST ROUTE SHOULD BE REMOVED!!
	 * BLOCKED BY: https://bugs.php.net/bug.php?id=55815
	 */
	function put_faxpro($params) {
		//return faxpro_save_settings($params);
		return $this->api->add_header(501);
	}

}
