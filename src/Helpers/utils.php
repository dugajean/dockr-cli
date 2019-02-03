<?php

/**
 * Prepends ./ to a path.
 *
 * @param string $path
 *
 * @return string
 */
function current_path($path)
{
    return './' . $path;
}

/**
 * Convert case to studly.
 *
 * @param string $str
 *
 * @return string
 */
function studly_case($str)
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
}

/**
 * Convert case to camel.
 *
 * @param string $str
 *
 * @return string
 */
function camel_case($str)
{
    return lcfirst(studly_case($str));
}

/**
 * Convert a string to snake case.
 *
 * @param string $str
 * @param string $delimiter
 *
 * @return string
 */
function snake_case($str, $delimiter = '_')
{
    if (!ctype_lower($str)) {
        $str = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $str));
    }

    return $str;
}

/**
 * Get the class "basename" of the given object / class.
 *
 * @param  string|object  $class
 * @return string
 */
function class_basename($class)
{
    $class = is_object($class) ? get_class($class) : $class;
    return basename(str_replace('\\', '/', $class));
}

function color($mode, $str)
{
    switch ($mode)
    {
        case 'green':
        case 'success':
            $tag = 'info';
            break;
        case 'red':
        case 'danger':
        case 'error':
            $tag = 'error';
            break;
        default:
        case 'yellow':
        case 'info':
            $tag = 'comment';
            break;
    }

    return "<{$tag}>{$str}</{$tag}>";
}
