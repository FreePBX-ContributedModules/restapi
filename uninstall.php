<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

sql('DROP TABLE IF EXISTS restapi_general, restapi_log_event_details, restapi_log_events, restapi_token_user_mapping, restapi_token_details, restapi_tokens');

unlink($amp_conf['AMPWEBROOT'] . '/restapi/rest.php');
?>
