<?php

class restapi_Parking{
	private $api;
    	
	function __construct($api) {
		$this->api = & $api;
	}	
	
	function index() {
		return $this->get_parking();
        }

	/**
         * @verb GET
         * @returns - the default parking lot
         * @uri /parking
         */
         function get_parking($params) {
		$lot = parking_get('default');

                return $lot ? $lot : false;
         }

	/**
         * @verb PUT
         * @uri /parking
         */
         function put_parking($params) {
		return parking_save($params);
         }
}
