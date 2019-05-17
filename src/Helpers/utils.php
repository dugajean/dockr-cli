<?php

declare(strict_types=1);

namespace Dockr\Helpers;

use Symfony\Component\Process\Process;

/**
 * Convert case to studly.
 *
 * @param string $str
 *
 * @return string
 */
function studly_case(string $str): string
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
function camel_case(string $str): string
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
function snake_case(string $str, string $delimiter = '_'): string
{
    if (!ctype_lower($str)) {
        $str = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $str));
    }

    return $str;
}

/**
 * Resolves the current path.
 *
 * @param string $path
 *
 * @return string
 */
function current_path(string $path = ''): string
{
    $defaultPath = \Phar::running() ? add_slash(getcwd(), 'end') : '.' . DIRECTORY_SEPARATOR;

    $root = add_slash(env('DOCKR_PATH', ''), 'end') ?: $defaultPath;

    return $root . $path;
}

/**
 * Get the class "basename" of the given object / class.
 *
 * @param  string|object  $class
 * @return string
 */
function class_basename($class): string
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
function color(string $mode, string $str, bool $padded = false): string
{
    if ($padded) {
        $padding = str_repeat(' ', strlen($str) + 4);
        $str = "{$padding}\n  {$str}  \n{$padding}";
    }

    switch ($mode) {
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
function comma_list(array $array): string
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
function starts_with(string $haystack, $needles): bool
{
    foreach ((array)$needles as $needle) {
        if ($needle != '' && strpos($haystack, $needle) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Determine if a given string ends with a given substring.
 *
 * @param  string        $haystack
 * @param  string|array  $needles
 *
 * @return bool
 */
function ends_with(string $haystack, $needles): bool
{
    foreach ((array)$needles as $needle) {
        if ((string)$needle === substr($haystack, -strlen($needle))) {
            return true;
        }
    }

    return false;
}

/**
 * Execute a shell command.
 *
 * @param string $command
 * @param array  $env
 *
 * @return string
 */
function process(string $command, array $env = []): string
{
    $process = (Process::fromShellCommandline($command))->setTty(Process::isTtySupported());
    $process->setTimeout(3600);
    $process->start(null, $env);
    $process->wait(function ($type, $buffer) { echo $buffer; });

    return $process->getOutput();
}

/**
 * Flatten a multi-dimensional array into a single level.
 *
 * @param  array  $array
 * @return array
 */
function array_flatten(array $array): array
{
    $return = [];
    array_walk_recursive($array, function ($x) use (&$return) { $return[] = $x; });

    return $return;
}

/**
 * Determines whether an array if associative (has strings as keys) or indexed with numbers.
 *
 * @param array $array
 *
 * @return bool
 */
function is_assoc(array $array): bool
{
    return count(array_filter(array_keys($array), 'is_string')) > 0;
}

/**
 * Adds a slash at the beginning of the string is it's not present.
 *
 * @param string $str
 *
 * @return string
 */
function add_slash(string $str, string $position = 'start'): string
{
    if (!$str) {
        return '';
    }

    $func = __NAMESPACE__ . '\\' . ($position === 'start' ? 'starts_with' : 'ends_with');

    if ($position == 'start') {
        return (!$func($str, DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '') . $str;
    }
    
    return $str . (!$func($str, DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '');
}

/**
 * Expands the posix tilde (~) to the current home directory.
 *
 * @param string $path
 *
 * @return string
 */
function expand_tilde(string $path): string
{
    if (strpos($path, '~') === false) {
        return $path;
    }

    $home = getenv('HOME');
    if (!empty($home)) {
        $home = rtrim($home, DIRECTORY_SEPARATOR);
    } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
        $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
        $home = rtrim($home, DIRECTORY_SEPARATOR);
    }

    return !empty($home) ? str_replace('~', $home, $path) : $path;
}

/**
 * Fixes the slashes according to the OS.
 *
 * @param string $path
 *
 * @return string
 */
function slash(string $path): string
{
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

/**
 * Gets the value of an environment variable. Supports boolean, empty and null.
 *
 * @param  string  $key
 * @param  mixed   $default
 * 
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

    if (starts_with($value, '"') && ends_with($value, '"')) {
        return substr($value, 1, -1);
    }

    return $value;
}