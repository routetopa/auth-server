<?php

function smarty_function_t($params, $template) {
    $k = null;
    if (empty($k = @$params['key'])) {
        trigger_error("assign: missing 'key' parameter");
        return;
    }

    $l = $template->getVariable('l')->value;
    $k = @$params['key'];
    return (@$l[$k]) ?: "{{$k}}";
}