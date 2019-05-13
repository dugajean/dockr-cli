<?php

declare(strict_types=1);

namespace Dockr;

final class Config
{
    /**
     * @var array
     */
    private $data;

    /**
     * Config's file name.
     * 
     * @var string
     */
    public $configFile = './dockr.json';

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
        if (file_exists($this->configFile)) {
            $this->data = json_decode(file_get_contents($this->configFile), true);
        }
    }

    /**
     * @param mixed       $dataOrKey
     * @param string|null $value
     *
     * @return bool
     */
    public function set($dataOrKey = null, ?string $value = null): bool
    {
        if (is_array($dataOrKey)) {
            $this->data = $dataOrKey;
        } elseif ($this->data) {
            $this->data[$dataOrKey] = $this->prepareValue($value);
        }

         return (bool)file_put_contents($this->configFile, $this->makeJson());
    }

    /**
     * Fetch a key from the config.
     *
     * @param string|null $key
     *
     * @return string
     */
    public function get(?string $key = null): ?string
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
    public function exists(): bool
    {
        return $this->data && count($this->data) > 0;
    }

    /**
     * Sets the location of the dockr.json file. Defaults to ./dockr.json
     *
     * @param string $newConfig
     *
     * @return $this
     */
    public function setConfigFile(string $newConfig): self
    {
        $this->configFile = $newConfig;

        return $this;
    }

    /**
     * Prepares the value for storing.
     *
     * @param string $value
     *
     * @return string
     */
    private function prepareValue(string $value): string
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
