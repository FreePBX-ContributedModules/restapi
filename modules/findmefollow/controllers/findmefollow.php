<?php

class restapi_Findmefollow{
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
	* @uri /findmefollow
	*/
        function get($params) {
        
	}

	/**
         * @verb GET
         * @returns - a list of users' findmefollow settings
         * @uri /findmefollow/users
         */
         function get_findmefollow_users($params) {
	        $users = array();

		$findmefollow_allusers = findmefollow_allusers();

		foreach ($findmefollow_allusers as $user) {
                        $users[$user[0]] = $user[1];
                	unset($user);
		}

                return $users ? $users : false;
         }

        /**
         * @verb GET
         * @returns - a list of users' find me follow me settings
         * @uri /findmefollow/users/:id
         */
         function get_findmefollow_users_id($params) {
		$users = findmefollow_get($params['id'], 1);

		return $users ? $users : false;
         }

	/**
         * @verb PUT
         * @uri /findmefollow/users/:id
         */
         function put_findmefollow_users_id($params) {
		findmefollow_del($params['id']);
		return findmefollow_add(
					$params['id'],
					$params['strategy'],
					$params['grptime'],
					$params['grplist'],
					$params['postdest'],
					$params['grppre'],
					$params['annmsg_id'],
					$params['dring'],
					$params['needsconf'],
					$params['remotealert_id'],
					$params['toolate_id'],
					$params['ringing'],
					$params['pre_ring'],
					$params['ddial'],
					$params['changecid'],
					$params['fixedcid']);
         }
}
