<?php

if (!defined('ABSPATH')) {
    // phpinfo();
    exit;
}

/* Recommend php.ini settings (Development/Production)

max_execution_time = 0/8192
max_input_time = 8192
max_input_vars = 8192
memory_limit = 8G
post_max_size = 8G
upload_max_filesize = 8G
max_file_uploads = 20
output_buffering = 4096

error_reporting = E_ALL & ~E_DEPRECATED
log_errors = On
log_errors_max_len = 1024
display_errors = On/Off
display_startup_errors = On/Off
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
track_errors = Off
html_errors = On

disable_functions = error_reporting,ini_set

*/


set_error_handler(
    /**
     * @throws ErrorException
     */
    function ($severity, $message, $file, $line) {
        if (!(ini_get('error_reporting') & $severity)) {
            // This error code is not included in error_reporting
            return;
        }
        $op = "error";
        $op_fancy = 'Error';
        switch ($severity) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $op_fancy = "Fatal error";
                break;
            case E_RECOVERABLE_ERROR:
                $op_fancy = "Recoverable fatal error";
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $op = "warn";
                $op_fancy = "Warning";
                break;
            case E_PARSE:
                $op_fancy = "Parse error";
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $op = "info";
                $op_fancy = "Notice";
                break;
            case E_STRICT:
                $op_fancy = "Strict Standards";
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $op = "warn";
                $op_fancy = "Deprecated";
                break;
            default:
                $op_fancy = "Unknown error";
                break;
        }
        if (defined('WP_DEBUG_STRICT') && WP_DEBUG_STRICT) {
            // threat warnings and notices as errors
            throw new ErrorException("PHP $op_fancy: $message", 0, $severity, $file, $line);
        } else {
            ob_start();
            debug_print_backtrace();
            $trace = ob_get_contents();
            ob_end_clean();
            console_log($op, "PHP $op_fancy: $message in $file on line $line", $trace);
        }
    }
);

function console_log(): string
{
    list(, $caller) = debug_backtrace(false);
    $action = current_action();
    $encoded_args = [];
    foreach (func_get_args() as $arg) {
        if (!isset($op)) {
            switch ($arg) {
                case "info":
                    $op = "info";
                    continue 2;
                case "warn":
                    $op = "warn";
                    continue 2;
                case "error":
                    $op = "info";
                    continue 2;
                default:
                    $op = "log";
            }
        }
        try {
            if (is_object($arg)) {
                $extract_props = function ($obj) use (&$extract_props): array {
                    $members = [];
                    $class = get_class($obj);
                    foreach ((new ReflectionClass($class))->getProperties() as $prop) {
                        $prop->setAccessible(true);
                        $name = $prop->getName();
                        if (isset($obj->{$name})) {
                            $value = $prop->getValue($obj);
                            if (is_array($value)) {
                                $members[$name] = [];
                                foreach ($value as $item) {
                                    if (is_object($item)) {
                                        $itemArray = $extract_props($item);
                                        $members[$name][] = $itemArray;
                                    } else {
                                        $members[$name][] = $item;
                                    }
                                }
                            } else if (is_object($value)) {
                                $members[$name] = $extract_props($value);
                            } else $members[$name] = $value;
                        }
                    }
                    return $members;
                };

                $encoded_args[] = json_encode($extract_props($arg));
            } else {
                $encoded_args[] = json_encode($arg);
            }
        } catch (Exception $ex) {
            $encoded_args[] = '`' . print_r($arg, true) . '`';
        }
    }
    $msg = '`📜`, `'
        . (array_key_exists('class', $caller) ? $caller['class'] : "\x3croot\x3e")
        . '\\\\'
        . $caller['function'] . '()`, '
        . (strlen($action) > 0 ? '`🪝`, `' . $action . '`, ' : '')
        . '` ➡️ `, ' . implode(', ', $encoded_args);
    $html = '<script type="text/javascript">console.' . $op . '(' . $msg . ')</script>';
    add_action('wp_enqueue_scripts', function () use ($html) {
        echo $html;
    });
    add_action('admin_enqueue_scripts', function () use ($html) {
        echo $html;
    });
    error_log($msg);
    return $html;
}

function add_local_wp_css()
{
    $localWpCss = '#wpadminbar { background-color: #9c3e3d !important; } #wpadminbar li:hover > .ab-item { background-color: #00000033 !important; }';
    add_action('wp_footer', function () use ($localWpCss) {
        echo "<style>$localWpCss</style>";
    });
    add_action('admin_print_scripts', function () use ($localWpCss) {
        echo "<style>$localWpCss</style>";
    });
}
