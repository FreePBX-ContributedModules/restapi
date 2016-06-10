<?php

class restapi_Queues {
	private $api;
    	
	function __construct($api) {
		$this->api = & $api;
	}	
	
	function index() {
		return $this->get_queues();
        }

	/**
         * @verb GET
         * @returns - the queue list
         * @uri /queues
         */
         function get_queues($params) {
		$queues = queues_list();

		foreach ($queues as $queue) {
			$entry = new stdClass();
			$entry->extension = $queue[0];
			$entry->name = $queue[1];
			$list[$queue[0]] = $entry;
		}

                return $list ? $list : false;
         }

	/**
	 * @verb GET
	 * @returns - a list of queues with their members (static and dynamic)
	 * @uri /queues/members
	 */
	function get_queues_members($params) {
		global $astman;

		$list = array();

		// Get dynamic members priority from astdb
		$get = $astman->database_show('QPENALTY');
		if ($get) {
			foreach ($get as $key => $value) {
				$keys = explode('/', $key);
				if (isset($keys[3]) && $keys[3] == 'agents') {
					$queue = $keys[2];
					$dynmember = $keys[4];

					$list[$queue]['dynmembers'][] = $dynmember;
				}
			}
		}

		// Get static members
		$allmembers = queues_get_static_members();
		foreach ($allmembers as $queue => $members) {
			foreach ($members as $member) {
				if (preg_match("/^.*?\/([\d]+).*,([\d]+)$/", $member, $matches)) {
					$list[$queue]['members'][] = $matches[1];
				}
			}
		}

		return $list ? $list : false;
	}

        /**
         * @verb GET
         * @returns - a list of queue settings
         * @uri /queue/:id
         */
         function get_queue_id($params) {
		$queue = queues_get($params['id']);

		if ($queue) {
			$queue['extension'] = $params['id'];

			$dynmembers = array();
			foreach (explode("\n", $queue['dynmembers']) as $member) {
				$dynmembers[] = substr($member, 0, strpos($member, ","));
			}
			$queue['dynmembers'] = $dynmembers;

			$members = array();
			foreach ($queue['member'] as $member) {
				if (preg_match("/^.*?\/([\d]+).*,([\d]+)$/", $member, $matches)) {
					$members[] = $matches[1];
				}
			}
			$queue['member'] = $members;
		}

		return $queue ? $queue : false;
         }

}
