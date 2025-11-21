<?php


if (!function_exists('convertStates')) {
    function convertStates($state)
    {
        return strtoupper(str_replace('_', ' ', $state));
    }
}
