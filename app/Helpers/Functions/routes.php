<?php

function route_prefix_records($is_radio)
{
    return $is_radio ? 'radio' : 'video';
}

function route_prefix_channels($is_radio)
{
    return $is_radio ? 'radio-stations' : 'channels';
}

function typed_route($route, $is_radio, $params = [])
{
    $route = str_replace('[RECORD]', route_prefix_records($is_radio), $route);
    $route = str_replace('[CHANNEL]', route_prefix_channels($is_radio), $route);
    return route($route, $params);
}
