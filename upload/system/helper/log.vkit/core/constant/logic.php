<?php
/**
 * @desc constant logic
 */
# for all
define('DRIVE_C', 'C:');
define('VIRTUAL_DIRECTORY_CURRENT', '.');
define('VIRTUAL_DIRECTORY_PARENT', '..');
define('EMPTY_STRING', '');
define('PHP_EXTENSION', '.php');
define('LOG_EXTENSION', '.log');
define('CONFIG_EXTENSION', '.config');
define('LOG_DIRECTORY_NAME', 'log');
define('CONFIG_DIRECTORY_NAME', 'config');
define('CHARACTER_UNDERSCORE', '_');
define('CHARACTER_SPACE', ' ');
# invalid character in file name and directory name, formated in regular expression
define('INVALID_CHARACTER_IN_FILE_NAME', '/[\?\/\\\\%\*:\|"<>]/');
define('DATE_TIME_FORMAT', 'Y-m-d H.i.s'); # for directory or file name
# constant kit path
define('PROJECT_SOURCE_PATH', dirname(dirname(dirname(__FILE__))));
# constant CONSTANT PATH for subdirectory of kit
foreach (scandir(PROJECT_SOURCE_PATH) as $index => $item) {
    switch (true) {
        case 0 === strpos($item, '.'): # virtual, local store directory isn't cared ex. ., .., .svn
            break;
        case is_dir(PROJECT_SOURCE_PATH . DIRECTORY_SEPARATOR . $item): # normal subdirectory
            # suffix by _SOURCE_PATH ex. CONFIG_SOURCE_PATH, CORE_SOURCE_PATH
            define(strtoupper($item) . '_SOURCE_PATH', PROJECT_SOURCE_PATH . DIRECTORY_SEPARATOR . $item);
            break;
        default:
            break;
    }
}

# for core
define('MARK_OF_COMMENT_LINE', '#'); # which comment line is start with
define('APPEND_KEY', '[]'); #  append an item
define('USE_ABOVE_KEY', 'useUponKey'); # flag whether value is array type, move pointer of array to end of SAME level
define('TO_END_KEY', '[E]'); # select final key ~ use key APPEND_KEY & key USE_ABOVE_KEY
define('DELIMITER', '|'); # explode when $excepts is of string
define('CONFIG_SEPARATOR', '|'); # separator for keys, value
define('EXE_AS_PHP_KEY', 'PHP'); # execute value as php code
define('USE_NEWEST', 'use_newest'); # flag for function browseKeyValue, whether only get latest keys, value
define('START_POINT_KEY', 'start_point'); # is access point for child element
define('VARIABLE_KEY', 'variable'); #  create variable ex. prefix, flag, ...
define('UNSET_KEY', 'unset'); # unset variable
define('OPEN_BRACKET', '[(]'); # using combination that is least of conflicting ex. '{', '[(]'
define('CLOSE_BRACKET', '[)]'); # using combination that is least of conflicting ex. '}', '[)]'
define('CREATE_NEW_AT_LAST_VAR', 'isLastCreateMode'); # apply for []|value, not []|key|value
define('USER_TRIM_FUNCTION', 'user_trim'); # trim lines
define('USER_TRIM_FUNCTION_CODE', 'function ' . USER_TRIM_FUNCTION . '(&$item, $key) {$item = trim($item);};');
define('DEFAULT_REPLACEMENT', '-');
