<?php

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

/**
 * Make a string colorful for CLI output.
 *
 * @param string $mode
 * @param string $str
 * @param bool   $padded
 *
 * @return string
 */
function color($mode, $str, $padded = false)
{
    if ($padded) {
        $padding = str_repeat(' ', strlen($str) + 4);
        $str = "{$padding}\n  {$str}  \n{$padding}";
    }

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

/**
 * Join array with a comma-space.
 *
 * @param array $array
 *
 * @return string
 */
function comma_list(array $array)
{
    return implode(', ', $array);
}

/**
 * Determine if a given string starts with a given substring.
 *
 * @param  string        $haystack
 * @param  string|array  $needles
 *
 * @return bool
 */
function starts_with($haystack, $needles)
{
    foreach ((array) $needles as $needle)
    {
        if ($needle != '' && strpos($haystack, $needle) === 0) {
            return true;
        }
    }

    return false;
}
