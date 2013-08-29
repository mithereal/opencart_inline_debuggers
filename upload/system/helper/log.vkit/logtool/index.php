<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'core.php';

scanPhpFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant');

scanPhpFile(__FILE__);

new logr('checkLog', null);
 