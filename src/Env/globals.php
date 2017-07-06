<?php

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        $startsWith = function ($haystack, $needles) {
            foreach ((array) $needles as $needle) {
                if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                    return true;
                }
            }
            return false;
        };

        $endsWith = function ($haystack, $needles) {
            foreach ((array) $needles as $needle) {
                if (substr($haystack, -strlen($needle)) === (string) $needle) {
                    return true;
                }
            }
            return false;
        };

        if (strlen($value) > 1 && $startsWith($value, '"') && $endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
