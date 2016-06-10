<?php

class restapi_Conferences {
	private $api;
    	
	function __construct($api) {
		$this->api = & $api;
	}	
	
	function index() {
		return $this->get_conferences();
        }

	/**
         * @verb GET
         * @returns - the conference list
         * @uri /conferences
         */
         function get_conferences($params) {
		$conferences = conferences_list();

		foreach($conferences as $conference) {
			$room = new stdClass();
			$room->id = $conference[0];
			$room->description = $conference[1];
			$list[$conference[0]] = $room;
		}

                return $list ? $list : false;
         }

        /**
         * @verb GET
         * @returns - a list of conference settings
         * @uri /conference/:id
         */
         function get_conference_id($params) {
		$conference = conferences_get($params['id']);

		return $conference ? $conference : false;
         }

}
