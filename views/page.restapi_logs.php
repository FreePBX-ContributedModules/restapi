<?php
$vars['logs'] = restapi_logger_get(1, 25, 'DESC', true);
echo load_view(dirname(__FILE__) . '/log_report.php', $vars);
