<?php

class restapi_Contactmanager {
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
	 * @uri /contactmanager
	 */
	function get($params) {
	}

	/**
	 * @verb GET
	 * @returns - true if contactmanager module is licensed, false otherwise
	 * @uri /contactmanager
	 */
	function get_contactmanager($params) {
		$contactmanager = $this->contactmanager_module();
		if ($contactmanager) {
			return true;
		}

		return false;
	}

	/**
	 * @verb GET
	 * @returns - contactmanager groups
	 * @uri /contactmanager/groups/:id
	 */
	function get_contactmanager_groups_id($params) {
		$contactmanager = $this->contactmanager_module();
		if ($contactmanager) {
			$groups = array();

			$allGroups = $contactmanager->getGroups();
			if ($allGroups) {
				foreach ($allGroups as $group) {
					if ($group['owner'] != -1 && $group['owner'] != $params['id']) {
						continue;
					}

					$groups[] = $group;
				}

			}

			return $groups;
		}

		return false;
	}

	/**
	 * @verb GET
	 * @returns - contactmanager group info
	 * @uri /contactmanager/groups/:id/:groupid
	 */
	function get_contactmanager_groups_id_groupid($params) {
		$contactmanager = $this->contactmanager_module();
		if ($contactmanager) {
			$group = $contactmanager->getGroupByID($params['groupid']);
			if ($group['owner'] != -1 && $group['owner'] != $params['id']) {
				return false;
			}

			$allEntries = $contactmanager->getEntriesByGroupID($params['groupid']);

			foreach ($allEntries as $id => $entry) {
				unset($entry['groupid']);

				if ($entry['numbers']) {
					$numbers = NULL;
					foreach ($entry['numbers'] as $numid => $number) {
						$number['id'] = $numid;
						$numbers[] = $number;
					}
					$entry['numbers'] = $numbers;
				}

				if ($entry['xmpps']) {
					$xmpps = NULL;
					foreach ($entry['xmpps'] as $xmppid => $xmpp) {
						$xmpp['id'] = $xmppid;
						$xmpps[] = $xmpp;
					}
					$entry['xmpps'] = $xmpps;
				}

				if ($entry['emails']) {
					$emails = NULL;
					foreach ($entry['emails'] as $emailid => $email) {
						$email['id'] = $emailid;
						$emails[] = $email;
					}
					$entry['email'] = $emails;
				}

				if ($entry['websites']) {
					$websites = NULL;
					foreach ($entry['websites'] as $websiteid => $website) {
						$website['id'] = $websiteid;
						$websites[] = $website;
					}
					$entry['websites'] = $websites;
				}

				$entry['id'] = $id;
				$entries[] = $entry;
			}

			$group['entries'] = $entries;

			return $group;
		}

		return false;
	}

	/**
	 * @verb GET
	 * @returns - contactmanager entry info
	 * @uri /contactmanager/groups/:id/:groupid/:entryid
	 */
	function get_contactmanager_groups_id_groupid_entryid($params) {
		$contactmanager = $this->contactmanager_module();
		if ($contactmanager) {
			$group = $contactmanager->getGroupByID($params['groupid']);
			if ($group['owner'] != -1 && $group['owner'] != $params['id']) {
				return false;
			}

			$allEntries = $contactmanager->getEntriesByGroupID($params['groupid']);

			$entry = $allEntries[$params['entryid']];
			if ($entry) {
				unset($entry['groupid']);

				$numbers = array();
				if ($entry['numbers']) {
					foreach ($entry['numbers'] as $numid => $number) {
						$number['id'] = $numid;
						$numbers[] = $number;
					}
				}
				$entry['numbers'] = $numbers;

				$xmpps = array();
				if ($entry['xmpps']) {
					foreach ($entry['xmpps'] as $xmppid => $xmpp) {
						$xmpp['id'] = $xmppid;
						$xmpps[] = $xmpp;
					}
				}
				$entry['xmpps'] = $xmpps;

				$emails = array();
				if ($entry['emails']) {
					foreach ($entry['emails'] as $emailid => $email) {
						$email['id'] = $emailid;
						$emails[] = $email;
					}
				}
				$entry['email'] = $emails;

				$websites = array();
				if ($entry['websites']) {
					foreach ($entry['websites'] as $websiteid => $website) {
						$website['id'] = $websiteid;
						$websites[] = $website;
					}
				}
				$entry['websites'] = $websites;
			}
		}

		return $entry ? $entry : false;
	}

	/**
	 * @verb GET
	 * @returns - contactmanager entries
	 * @uri /contactmanager/entries/:id
	 */
	function get_contactmanager_entries_id($params) {
		$contactmanager = $this->contactmanager_module();
		if ($contactmanager) {
			$entries = array();

			$allGroups = $contactmanager->getGroups();
			foreach ($allGroups as $group) {
				if ($group['owner'] != -1 && $group['owner'] != $params['id']) {
					continue;
				}

				$allEntries = $contactmanager->getEntriesByGroupID($group['id']);

				foreach ($allEntries as $id => $entry) {
					if ($entry['numbers']) {
						$numbers = NULL;
						foreach ($entry['numbers'] as $numid => $number) {
							$number['id'] = $numid;
							$numbers[] = $number;
						}
						$entry['numbers'] = $numbers;
					}

					if ($entry['xmpps']) {
						$xmpps = NULL;
						foreach ($entry['xmpps'] as $xmppid => $xmpp) {
							$xmpp['id'] = $xmppid;
							$xmpps[] = $xmpp;
						}
						$entry['xmpps'] = $xmpps;
					}

					if ($entry['emails']) {
						$emails = NULL;
						foreach ($entry['emails'] as $emailid => $email) {
							$email['id'] = $emailid;
							$emails[] = $email;
						}
						$entry['email'] = $emails;
					}

					if ($entry['websites']) {
						$websites = NULL;
						foreach ($entry['websites'] as $websiteid => $website) {
							$website['id'] = $websiteid;
							$websites[] = $website;
						}
						$entry['websites'] = $websites;
					}

					$entry['id'] = $id;
					$entries[] = $entry;
				}
			}

			return $entries;
		}

		return false;
	}

	function contactmanager_module() {
		global $bmo;

		if ($bmo && $bmo->Contactmanager) {
			$contactmanager = $bmo->Contactmanager;
		} else {
			$contactmanager = Contactmanager::create();
		}

		return $contactmanager;
	}
}
