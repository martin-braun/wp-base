<?php if ( ! defined( 'ABSPATH' ) ) exit;

function console_log(): string {
    list( , $caller ) = debug_backtrace( false );
    $action = current_action();
    $encoded_args = [];
    foreach ( func_get_args() as $arg ) try {
        if ( is_object( $arg ) ) {
            function extract_props( $obj ): array {
                $members = [];
                foreach ( ( new ReflectionClass( get_class( $obj ) ) )->getProperties() as $prop ) {
                    $prop->setAccessible( true );
                    $value = $prop->getValue( $obj );
                    $name = $prop->getName();
                    if ( is_array( $value ) ) {
                        $members[$name] = [];
                        foreach ( $value as $item ) {
                            if ( is_object( $item ) ) {
                                $itemArray = extract_props( $item );
                                $members[$name][] = $itemArray;
                            } else {
                                $members[$name][] = $item;
                            }
                        }
                    } else if ( is_object( $value ) ) {
                        $members[$name] = extract_props( $value );
                    } else $members[$name] = $value;
                }
                return $members;
            }

            $encoded_args[] = json_encode( extract_props( $arg ) );
        } else {
            $encoded_args[] = json_encode( $arg );
        }
    } catch ( Exception $ex ) {
        $encoded_args[] = '`' . print_r( $arg, true ) . '`';
    }
    $msg = '`📜`, `'
        . ( array_key_exists( 'class', $caller ) ? $caller['class'] : "<root>" )
        . '\\\\'
        . $caller['function'] . '()`, '
        . ( strlen( $action ) > 0 ? '`🪝`, `' . $action . '`, ' : '' )
        . '` ➡️ `, ' . implode( ', ', $encoded_args );
    $html = '<script type="text/javascript">console.log(' . $msg . ')</script>';
    add_action( 'wp_enqueue_scripts', function() use ( $html ) {
        echo $html;
    } );
    add_action( 'admin_enqueue_scripts', function() use ( $html ) {
        echo $html;
    } );
    error_log( $msg );
    return $html;
}