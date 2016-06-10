<?php

class restapi_Daynight {
	private $api;
    	
	function __construct($api) {
		$this->api = & $api;
	}	
	
	function index() {
		return $this->get();
        }

       /**
	* @verb GET
	* @returns 
	* @uri /daynight
	*/
        function get($params) {
        
	}

	/**
         * @verb GET
         * @returns - a list of daynight settings
         * @uri /daynight
         */
         function get_daynight($params) {
		$daynights = daynight_list();

                return $daynights ? $daynights : false;
         }

        /**
         * @verb GET
         * @returns - daynight state
         * @uri /daynight/:id
         */
         function get_daynight_id($params) {
		$dn = new dayNightObject($params['id']);

		if ($dn) {
			$daynight = array();
			$daynight['state'] = $dn->getState();
		}

		return $daynight ? $daynight : false;
         }

	/**
         * @verb PUT
         * @uri /daynight/:id
         */
         function put_daynight_id($params) {
		$dn = new dayNightObject($params['id']);

		if ($dn) {
			$dn->setState($params['state']);

			return $true;
		}

		return $false;
         }
}
