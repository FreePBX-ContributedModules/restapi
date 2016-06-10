<?php
// vim: set ai ts=4 sw=4 ft=php:
namespace FreePBX\modules;
class Restapi implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->freepbx = $freepbx;
		$this->db = $freepbx->Database;
		$this->userman = $freepbx->Userman;
		include_once(dirname(__FILE__) . '/functions.inc.php');
	}

	public function getActionBar($request){
		$buttons = array();
		$request['display'] = !empty($request['display']) ? $request['display'] : '';
		switch($request['display']) {
			case 'restapi':
				$buttons = array(
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _("Submit")
					)
				);
			break;
		}
		return $buttons;
	}

	public function doConfigPageInit($page) {
	}

	public function install() {
		global $db;
		global $amp_conf;
		$sql[]='CREATE TABLE IF NOT EXISTS `restapi_general` (
		  `keyword` varchar(50),
		  `value` varchar(150) default NULL,
		  UNIQUE KEY `keyword` (`keyword`)
		);';

		$sql[]='CREATE TABLE IF NOT EXISTS `restapi_log_event_details` (
		  `id` int(11) default NULL AUTO_INCREMENT,
		  `e_id` int(11) default NULL,
		  `time` int(11) default NULL,
		  `event` varchar(150) default NULL,
		  `data` text,
		  `trigger` text,
		   UNIQUE KEY `id` (`id`),
		   KEY `e_id` (`e_id`)
		);';

		$sql[]='CREATE TABLE IF NOT EXISTS `restapi_log_events` (
		  `id` int(11) default NULL AUTO_INCREMENT,
		  `time` int(11) default NULL,
		  `token` varchar(75) default NULL,
		  `signature` varchar(150) default NULL,
		  `ip` varchar(20) default NULL,
		  `server` varchar(75) default NULL,
		   UNIQUE KEY `id` (`id`),
		   KEY `time` (`time`),
		   KEY `token` (`token`)
		);';

		$sql[]='CREATE TABLE IF NOT EXISTS `restapi_token_details` (
		  `token_id` int(11) default NULL,
		  `key` varchar(50) default NULL,
		  `value` text default NULL
		);';

		$sql[]='CREATE TABLE IF NOT EXISTS `restapi_tokens` (
		  `id` int(11) default NULL AUTO_INCREMENT,
		  `name` varchar(150) default NULL,
		  `desc` varchar(250) default NULL,
		  UNIQUE KEY `id` (`id`)
		);';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `restapi_token_user_mapping` (
		  `user` varchar(25) default NULL,
		  `token_id` int(11) default NULL
		);';

		$firstinstall = false;
		$q = $db->query('SELECT * FROM restapi_general;');
		if (\DB::isError($q)) {
			$firstinstall = true;

			$sql[] = 'INSERT IGNORE INTO `restapi_general` VALUES
			  ("status", "normal"),
			  ("token", "' . restapi_tokens_generate() . '"),
			  ("tokenkey", "' . restapi_tokens_generate() . '");';
		}

		foreach ($sql as $statement){
		        $check = $db->query($statement);
		        if (\DB::IsError($check)){
		                die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
		        }
		}

		$sql = "SHOW KEYS FROM restapi_log_event_details WHERE Key_name='e_id'";
		$check = $db->getOne($sql);
		if (empty($check)) {
			$sql = "ALTER TABLE restapi_log_event_details ADD KEY `e_id` (`e_id`)";
			$result = $db->query($sql);
			if(\DB::IsError($result)) {
				out(_("Unable to add index to e_id field in restapi_log_event_details table"));
				freepbx_log(FPBX_LOG_ERROR, "Failed to add index to e_id field in the restapi_log_event_details table");
			} else {
				out(_("Adding index to e_id field in the restapi_log_event_details table"));
			}
		}

		$sql = "SHOW KEYS FROM restapi_log_events WHERE Key_name='time'";
		$check = $db->getOne($sql);
		if (empty($check)) {
			$sql = "ALTER TABLE restapi_log_events ADD KEY `time` (`time`), ADD KEY `token` (`token`)";
			$result = $db->query($sql);
			if(\DB::IsError($result)) {
				out(_("Unable to add index to time field in restapi_log_events table"));
				freepbx_log(FPBX_LOG_ERROR, "Failed to add index to time field in the restapi_log_events table");
			} else {
				out(_("Adding index to time field in the restapi_log_events table"));
			}
		}

		$sql = "SELECT data_type FROM information_schema.columns WHERE table_name = 'restapi_token_details' AND column_name = 'value' AND data_type = 'varchar'";
		$check = $db->getOne($sql);
		if (!empty($check)) {
			$sql = "ALTER TABLE restapi_token_details MODIFY `value` TEXT";
			$result = $db->query($sql);
			if(\DB::IsError($result)) {
				out(_("Unable to modify data type of value field in restapi_token_details table"));
			} else {
				out(_("Modifying data type of value field in the restapi_token_details table"));
			}
		}

		if(!file_exists($amp_conf['AMPWEBROOT'] . '/restapi')) {
		  @mkdir($amp_conf['AMPWEBROOT'] . '/restapi');
		}
		if(!file_exists($amp_conf['AMPWEBROOT'] . '/restapi/rest.php')) {
		  @symlink(dirname(__FILE__) . '/rest.php', $amp_conf['AMPWEBROOT'] . '/restapi/rest.php');
		}

		$mod_info = module_getinfo('restapi');
		if(!empty($mod_info['restapi']) && version_compare($mod_info['restapi']['dbversion'],'2.11.1.2','<')) {
		    out('Migrating Token Users to User Manager');
		    $sql = 'SELECT * FROM restapi_token_user_mapping';
		    $users = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
		    if(!empty($users)) {
		        $usermapping = array();
		        $userman = $this->freepbx->Userman;
		        $umusers = array();
		        $umusersn = array();
		        foreach($userman->getAllUsers() as $user) {
		            $umusersn[] = $user['username'];
		            if($user['default_extension'] == 'none') {
		                continue;
		            }
		            $umusers[$user['default_extension']] = $user['id'];
		        }
		        foreach($users as $user) {
		            $sql = "SELECT * FROM restapi_tokens WHERE `id` = ".$user['token_id'];
		            $tokenDetails = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
		            $ucpuser = $user['user'];
		            out('Found Token '.$tokenDetails[0]['name']);
		            if(empty($usermapping[$user['user']]['id'])) {
		                if(isset($umusers[$user['user']])) {
		                    $id = $umusers[$user['user']];
		                    $uInfo = $userman->getUserByID($id);
												try {
													$userman->updateUser($id, $uInfo['username'], $uInfo['username'], $uInfo['default_extension'], $tokenDetails[0]['desc']);
												} catch(\Exception $e) {
													continue;
												}
		                } else {
		                    out('Creating a User Manager User called '. $ucpuser .' for token '.$tokenDetails[0]['name']);
												try {
		                    	$output = $userman->addUser($ucpuser, bin2hex(openssl_random_pseudo_bytes(6)), $user['user'], $tokenDetails[0]['desc']);
												} catch(\Exception $e) {
													$output['status'] = false;
												}
		                    if(!$output['status']) {
		                        out('User confliction detected, attempting to autogenerate a username for token '. $tokenDetails[0]['name']);
														try {
															$output = $userman->addUser(bin2hex(openssl_random_pseudo_bytes(6)), bin2hex(openssl_random_pseudo_bytes(6)), $user['user'], $tokenDetails[0]['desc']);
														} catch(\Exception $e) {
															$output['status'] = false;
														}
		                        if(!$output['status']) {
		                            out('Username auto generation failed, skipping token '.$tokenDetails[0]['name']);
		                            continue;
		                        }
		                    }
		                    $id = $output['id'];
		                }
		            } else {
		                out('Adding token '.$tokenDetails[0]['name'].' to  User '. $ucpuser);
		                $id = $usermapping[$user['user']]['id'];
		            }
		            $sql = "UPDATE restapi_token_user_mapping SET user = '".$id."' WHERE user = '".$user['user']."'";
		            sql($sql);
		            $sql = "SELECT value FROM restapi_token_details WHERE `key` = 'users' AND token_id = ".$user['token_id'];
		            $uljson = sql($sql,'getOne');
		            $ul = json_decode($uljson,true);
		            if($ul[0] == "*") {
		                $ul = array();
		                foreach(core_users_list() as $list) {
		                    $ul[] = $list[0];
		                }
		            }
		            $devices = $userman->getAssignedDevices($id);
		            $devices = !empty($devices) ? $devices : array();
		            foreach($ul as $d) {
		                if(!in_array($d,$devices)) {
		                    $devices[] = $d;
		                }
		            }
		            out('Attaching devices '.implode(',',$ul).' from token '.$tokenDetails[0]['name'].' to user '.$ucpuser);
		            $userman->setAssignedDevices($id,$devices);

		            $usermapping[$user['user']]['id'] = $id;
		        }
		    }
		}
		//migrate to valid settings
		//this needs more work.
		if(function_exists('restapi_user_get_user_tokens') && function_exists('restapi_tokens_put')) {
			$users = $this->userman->getAllUsers();
			foreach($users as $user) {
				$tokens = \restapi_user_get_user_tokens($user['id']);
				foreach($tokens as $id => $token) {
					$data = \restapi_tokens_get($token);
					if (!empty($data['users']) && in_array("*",$data['users'])) {
						$data['users'] = array(
							$user['default_extension']
						);
						\restapi_tokens_put($data);
					}
				}
			}
		}
	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}

	public function usermanDelGroup($id,$display,$data) {
	}

	public function usermanAddGroup($id, $display, $data) {
		$this->usermanUpdateGroup($id,$display,$data);
	}

	public function usermanUpdateGroup($id,$display,$data) {
		if(!function_exists('restapi_user_get_user_tokens') && function_exists('restapi_tokens_del')) {
			return '';
		}
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'group') {
			if(isset($_POST['restapi_0_token_status'])) {
				if($_POST['restapi_0_token_status'] == "enabled") {
					$this->userman->setModuleSettingByGID($id,'restapi','restapi_token_status', true);
					$users = !empty($_POST['restapi_0_users']) ? $_POST['restapi_0_users'] : array();
					$this->userman->setModuleSettingByGID($id,'restapi','restapi_users',$users);
					$modules = !empty($_POST['restapi_0_modules']) ? $_POST['restapi_0_modules'] : array();
					if (in_array('*', $modules)) {
						$modules = array('*');
					}
					$this->userman->setModuleSettingByGID($id,'restapi','restapi_modules',$modules);
					$this->userman->setModuleSettingByGID($id,'restapi','restapi_rate',$_POST['restapi_0_rate']);
				} else {
					$this->userman->setModuleSettingByGID($id,'restapi','restapi_token_status', false);
				}
			}
		}

		$group = $this->userman->getGroupByGID($id);
		$users = is_array($group['users']) ? $group['users'] : array();
		foreach($users as $user) {
			$enabled = $this->userman->getCombinedModuleSettingByID($user, 'restapi', 'restapi_token_status');
			$tokens = \restapi_user_get_user_tokens($user);
			$userdata = $this->userman->getUserByID($user);
			if(!$enabled) {
				if(!empty($tokens)) {
					foreach($tokens as $token) {
						restapi_user_del_token($token);
					}
				}
				continue;
			} else {
				if(empty($tokens)) {
					$tokendata = \restapi_tokens_get();
					$tokendata['assoc_user'] = $user;
					$tokendata['token'] = \restapi_tokens_generate();
					$tokendata['tokenkey'] = \restapi_tokens_generate();
					$tokendata['modules'] = $this->userman->getCombinedModuleSettingByID($user, 'restapi', 'restapi_modules');
					$tokendata['users'] = $this->userman->getCombinedModuleSettingByID($user, 'restapi', 'restapi_users');
					$tokendata['rate'] = $this->userman->getCombinedModuleSettingByID($user, 'restapi', 'restapi_rate');
					$tokendata['token_status'] = 'enabled';

					$tokendata['name'] = 'User '.$user.' (autogen)';
					$tokendata['desc'] = 'Autogenerated token on new user creation';

					\restapi_tokens_put($tokendata);
				} else {
					foreach($tokens as $token) {
						$tokendata = \restapi_tokens_get($token);
						$tid = $tokendata['id'];
						$tokendata['assoc_user'] = $user;
						$tokendata['modules'] = $this->userman->getCombinedModuleSettingByID($user, 'restapi', 'restapi_modules');
						$tokendata['users'] = $this->userman->getCombinedModuleSettingByID($user, 'restapi', 'restapi_users');
						$tokendata['rate'] = $this->userman->getCombinedModuleSettingByID($user, 'restapi', 'restapi_rate');
						$tokendata['token_status'] = 'enabled';

						\restapi_tokens_put($tokendata);
					}
				}
			}
		}
	}

	public function usermanDelUser($id,$display,$data) {
		if(!function_exists('restapi_user_get_user_tokens') && function_exists('restapi_tokens_del')) {
			return '';
		}
		$tokens = \restapi_user_get_user_tokens($id);
		foreach($tokens as $token) {
			\restapi_tokens_del($token);
		}
	}

	public function usermanAddUser($id, $display, $data) {
		$this->usermanUpdateUser($id,$display,$data);
	}

	public function usermanUpdateUser($id,$display,$data) {
		if(!function_exists('restapi_user_get_user_tokens') && function_exists('restapi_tokens_put')) {
			return '';
		}
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'user') {
			foreach($_POST as $key => $data) {
				if(preg_match('/^restapi_(\d*)_token_status/i',$key,$matches)) {
					$token_status = $_POST[$matches[0]];
					if(isset($token_status)) {
						if($token_status == "enabled") {
							$this->userman->setModuleSettingByID($id,'restapi','restapi_token_status', true);
						} elseif($token_status == "disabled") {
							$this->userman->setModuleSettingByID($id,'restapi','restapi_token_status', false);
						} else {
							$inherit = true;
							$this->userman->setModuleSettingByID($id,'restapi','restapi_token_status', null);
						}
					}
				}
			}
		}

		$tokens = \restapi_user_get_user_tokens($id);
		$userdata = $this->userman->getUserByID($id);
		$enabled = $this->userman->getCombinedModuleSettingByID($id, 'restapi', 'restapi_token_status');
		if(!$enabled) {
			if(!empty($tokens)) {
				foreach($tokens as $token) {
					restapi_user_del_token($token);
				}
			}
			return;
		}
		//Coming from the Extensions or Users Page
		if($display == 'extensions' || $display == 'users') {
			//Only Generate a token if we have no tokens
			if(empty($tokens)) {
				$tokendata = \restapi_tokens_get();
				$tokendata['assoc_user'] = $id;
				$tokendata['token'] = \restapi_tokens_generate();
				$tokendata['tokenkey'] = \restapi_tokens_generate();
				$tokendata['modules'] = array('*');
				$tokendata['users'] = array('*');
				$tokendata['token_status'] = 'enabled';

				$tokendata['name'] = 'User '.$userdata['username'].' (autogen)';
				$tokendata['desc'] = 'Autogenerated token on new user creation';

				\restapi_tokens_put($tokendata);
			}
		//Coming from User Manager
		} elseif($display == 'userman') {
			$settings = array();
			foreach($_POST as $key => $data) {
				if(preg_match('/^restapi_(\d*)_(.*)/i',$key,$matches)) {
					if($matches[2] == "token_status") {
						$settings[$matches[1]][$matches[2]] = ($data == "enabled") ? "enabled" : "disabled";
					} else {
						$settings[$matches[1]][$matches[2]] = $data;
					}
				}
			}
			$defaultExt = $userdata['default_extension'] != "none" ? array($userdata['default_extension']) : array();
			//If tokens exist then update each token
			if(!empty($tokens)) {
				foreach($tokens as $token) {
					$tokendata = \restapi_tokens_get($token);
					$tid = $tokendata['id'];
					if(empty($settings[$tid])) {
						continue;
					}
					$tokendata['assoc_user'] = $id;
					$tokendata['token'] = $settings[$tid]['token'];
					$tokendata['tokenkey'] = $settings[$tid]['tokenkey'];

					if ($inherit) {
						$tokendata['modules'] = $this->userman->getCombinedModuleSettingByID($id, 'restapi', 'restapi_modules');
						$tokendata['users'] = $this->userman->getCombinedModuleSettingByID($id, 'restapi', 'restapi_users');
						$tokendata['rate'] = $this->userman->getCombinedModuleSettingByID($id, 'restapi', 'restapi_rate');
						$tokendata['token_status'] = $this->userman->getCombinedModuleSettingByID($id, 'restapi', 'restapi_token_status') ? 'enabled' : 'disabled';
					} else {
						$tokendata['modules'] = !empty($settings[$tid]['modules']) ? $settings[$tid]['modules'] : array();
						$tokendata['users'] = !empty($settings[$tid]['users']) ? $settings[$tid]['users'] : $defaultExt;
						$tokendata['rate'] = isset($settings[$tid]['rate']) ? $settings[$tid]['rate'] : '1000';
						$tokendata['token_status'] = $settings[$tid]['token_status'];
					}

					$tokendata['name'] = 'User '.$userdata['username'].' (autogen)';
					$tokendata['desc'] = 'Autogenerated token on new user creation';

					\restapi_tokens_put($tokendata);
				}
			//There are no pre-existing tokens so we create from whats been sent to us on the page
			} elseif(!empty($settings[0])) {
				$tokendata = \restapi_tokens_get();
				$tid = 0;
				$tokendata['assoc_user'] = $id;
				$tokendata['token'] = $settings[$tid]['token'];
				$tokendata['tokenkey'] = $settings[$tid]['tokenkey'];

				$tokendata['modules'] = !empty($settings[$tid]['modules']) ? $settings[$tid]['modules'] : array();
				$tokendata['users'] = !empty($settings[$tid]['users']) ? $settings[$tid]['users'] : $defaultExt;
				$tokendata['rate'] = $settings[$tid]['rate'];
				$tokendata['token_status'] = $settings[$tid]['token_status'];

				$tokendata['name'] = 'User '.$userdata['username'].' (autogen)';
				$tokendata['desc'] = 'Autogenerated token on new user creation';

				\restapi_tokens_put($tokendata);
			//No tokens and no data so we need to generate a token now
			} else {
				$tokendata = \restapi_tokens_get();
				$tokendata['assoc_user'] = $id;
				$tokendata['token'] = \restapi_tokens_generate();
				$tokendata['tokenkey'] = \restapi_tokens_generate();
				$tokendata['modules'] = array('*');
				$tokendata['users'] = $defaultExt;
				$tokendata['token_status'] = 'disabled';

				$tokendata['name'] = 'User '.$userdata['username'].' (autogen)';
				$tokendata['desc'] = 'Autogenerated token on new user creation';

				\restapi_tokens_put($tokendata);
			}
		}
	}

	public function usermanShowPage() {
		if(isset($_REQUEST['action'])) {
			switch($_REQUEST['action']) {
				case 'showgroup':
				case 'addgroup':
				case 'adduser':
				case 'showuser':
					$enabled = null;
					if($_REQUEST['action'] == "showuser") {
						$enabled = $this->userman->getModuleSettingByID($_REQUEST['user'], 'restapi', 'restapi_token_status',true);
						$tokens = restapi_user_get_user_tokens($_REQUEST['user']);
					} else {
						$tokens = array();
					}
					$displayvars = array(
						"mode" => (in_array($_REQUEST['action'],array("showgroup","addgroup")) ? "group" : "user"),
						"enabled" => $enabled
					);
					$tokens = !empty($tokens) ? $tokens : array();
					$displayvars['user_list_all'] = array();

					if(in_array($_REQUEST['action'],array("showgroup","addgroup"))) {
						$displayvars['user_list_all']['self'] = _("User Primary Extension");
					}

					$cul = array();
					foreach(core_users_list() as $list) {
						$cul[$list[0]] = array(
							"name" => $list[1],
							"vmcontext" => $list[2]
						);
						$displayvars['user_list_all'][$list[0]] = $list[1] . " &#60;".$list[0]."&#62;";
					}

					// Get list of modules that have been API enabled.
					$api = new \Api;
					$api_mods = array();
					foreach($api->maps as $verb => $urls) {
						foreach ($urls as $url => $maps) {
							foreach ($maps as $map => $details) {
								$api_mods[$details["module"]] = 1;
							}
						}
					}
					unset($api);

					//modules
					global $db;
					$mods = \modulelist::create($db);
					$displayvars['module_list'] = array();
					foreach ($mods->module_array as $mod) {
						if (isset($mod['rawname']) && isset($api_mods[$mod['rawname']])) {
							$displayvars['module_list'][$mod['rawname']] = $mod['name'];
						}
					}
					asort($displayvars['module_list']);
					$displayvars['module_list'] = array('*' => _('All')) + $displayvars['module_list'];

					//everything else
					$rest_template = $displayvars;
					if(!empty($tokens)) {
						foreach($tokens as $token) {
							$displayvars['tokens'][] = array_merge($rest_template, restapi_tokens_get($token));
						}
					} else {
						$displayvars['tokens'][0] = array_merge($rest_template, restapi_tokens_get());
						$displayvars['tokens'][0]['token'] = \restapi_tokens_generate();
						$displayvars['tokens'][0]['tokenkey'] = \restapi_tokens_generate();
						$displayvars['tokens'][0]['id'] = 0;
						$displayvars['tokens'][0]['users'] = array("self");
						$displayvars['tokens'][0]['rate'] = 1000;

					}

					if($displayvars['mode'] == "user") {

					} else {
						//group mode
						$enabled = $this->userman->getModuleSettingByGID($_REQUEST['group'],'restapi','restapi_token_status');
						$users = $this->userman->getModuleSettingByGID($_REQUEST['group'],'restapi','restapi_users');
						$modules = $this->userman->getModuleSettingByGID($_REQUEST['group'],'restapi','restapi_modules');
						$rate = $this->userman->getModuleSettingByGID($_REQUEST['group'],'restapi','restapi_rate');
						$displayvars['tokens'][0] = array_merge($rest_template, restapi_tokens_get());
						$displayvars['tokens'][0]['token'] = 1;
						$displayvars['tokens'][0]['tokenkey'] = 1;
						$displayvars['tokens'][0]['id'] = 0;
						if(!$enabled) {
							$displayvars['tokens'][0]['users'] = is_array($users) ? $users : array("self");
							$displayvars['enabled'] = $enabled;
						} else {
							$displayvars['tokens'][0]['users'] = is_array($users) ? $users : array("self");
							$displayvars['tokens'][0]['rate'] = !empty($rate) ? $rate : "1000";
							$displayvars['tokens'][0]['modules'] = is_array($modules) ? $modules : array();
							$displayvars['enabled'] = $enabled;
						}
					}

					return array(
						array(
							"title" => _("Rest API"),
							"rawname" => "restapi",
							"content" => load_view(__DIR__.'/views/hook_userman.php',$displayvars)
						)
					);
				break;
			}
		}
	}
}
