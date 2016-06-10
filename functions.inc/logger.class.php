<?php

class RestLogger {
	private $api,
			$log = array(),
			$id;
			
	
	function __construct($api) {
		$this->api = $api;
	
		//ensure that we log even if we have a fatal error
		register_shutdown_function(function($that){
			$e = error_get_last();	
			//only trigger on E_ERROR (Fatal run-time errors)
			if(isset($e, $e['type']) && $e['type'] === E_ERROR) {
				$that->event('PHP Fatal error', $e);
				$that->commit();
			}
		}, $this);
	}
	
	function __destruct() {
		$this->commit();
	}
	
	function init() {
			restapi_logger_clear_history();

			$this->id = restapi_logger_put_header(array(
						'ip'		=> $this->api->req->headers['ip'],
						'server'	=> $this->api->opts['token'],
						'sig'		=> $this->api->req->headers['signature'],
						'token'		=> $this->api->req->token,

			));
			$this->api->add_header('Request-Id', 
				restapi_logger_format_id($this->id));
	}
	/**
	 * Add log events to the log buffer
	 * 
	 * @param string - event type
	 * @param array - event data
	 * @param string - line that triggered this call
	 *
	 */
	function event($event, $data = '', $trigger = '') {
		$bt = debug_backtrace();
		$trigger = $trigger ? $trigger : $bt[0]['file'] . ':' . $bt[0]['line'];
		$this->log[] = array('time' => time(), 
						'event' => $event, 
						'data' => $data, 
						'trigger' => $trigger);
	}
	
	/**
	 * Commit the buffer to the database
	 *
	 */
	function commit() {
		restapi_logger_put_events($this->id, $this->log);
		$this->flush();
	}
	
	/**
	 * Clears the log buffer
	 *
	 */
	function flush() {
		$this->log = array();
	}
}
?>
