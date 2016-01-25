<?php

function smarty_function_flasht($params, $template) {
    $key = null;
    if (empty($key = @$params['key'])) {
        trigger_error("assign: missing 'key' parameter");
        return;
    }
    if( !empty( $_SESSION[$key] ) )
    {
        $class = !empty( $_SESSION[$key.'_class'] ) ? $_SESSION[$key.'_class'] : 'success';
        $message_key = $_SESSION[$key];
        unset($_SESSION[$key]);
        unset($_SESSION[$key.'_class']);
        return '<div class="'.$class.'" id="msg-flash">'.smarty_function_t(['key' => $message_key], $template).'</div>';
    }

}