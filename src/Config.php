<?php

namespace Dockr;

final class Config
{
    /**
     * @var array
     */
    private $data;

    /**
     * Config's file name.
     */
    const FILE = './dockr.json';

    /**
     * @var string
     */
    private $jsonArray;

    /**
     * Required config structure.
     */
    const STRUCTURE = [
        'project-name', 'project-domain', 'web-server', 'cache-store',
        'php-version', 'php-extensions', 'aliases', 'addons',
    ];

    /**
     * Config constructor.
     *
     * @return void
     */
    public function __construct()
    {
        if (file_exists(self::FILE)) {
            $this->data = json_decode(file_get_contents(self::FILE), true);
        }
    }

    /**
     * @param mixed       $dataOrKey
     * @param string|null $value
     *
     * @return bool
     */
    public function set($dataOrKey = null, $value = null)
    {
        if (is_array($dataOrKey)) {
            $this->data = $dataOrKey;
        } elseif ($this->data) {
            $this->data[$dataOrKey] = $this->prepareValue($value);
        }

         return (bool)file_put_contents(self::FILE, $this->makeJson());
    }

    /**
     * Fetch a key from the config.
     *
     * @param string|null $key
     *
     * @return string
     */
    public function get($key = null)
    {
        if (!$this->exists()) {
            return null;
        }

        if ($key === null) {
            return $this->data;
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    /**
     * Returns true if the config data exists, false otherwise.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->data && count($this->data) > 0;
    }

    /**
     * Prepares the value for storing.
     *
     * @param $value
     *
     * @return string
     */
    private function prepareValue($value)
    {
        $this->jsonArray = str_replace("\n", "\n\t", json_encode($value, JSON_PRETTY_PRINT));

        return is_array($value) ? '{ARRAY_PLACEHOLDER}' : $value;
    }

    /**
     * Prepares the JSON for saving.
     *
     * @return false|mixed|string
     */
    private function makeJson()
    {
        $json = json_encode($this->data, JSON_PRETTY_PRINT);

        if (strpos($json, '{ARRAY_PLACEHOLDER}') !== false) {
            $json = str_replace('"{ARRAY_PLACEHOLDER}"', $this->jsonArray, $json);
        }

        return $json;
    }
}
