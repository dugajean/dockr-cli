<?php

declare(strict_types=1);

namespace Dockr;

use function Dockr\Helpers\{ends_with, expand_tilde};

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
    private $configFile = './dockr.json';

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
        $this->loadConfigFile();   
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

         return (bool)@file_put_contents($this->configFile, $this->makeJson());
    }

    /**
     * Fetch a key from the config.
     *
     * @param string|null $key
     *
     * @return string
     */
    public function get(?string $key = null)
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
     * Getter for configFile.
     *
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->configFile;
    }

    /**
     * Sets the location of the dockr.json file. Defaults to ./dockr.json
     *
     * @param string $newPath, string $name = 'dockr.json'
     *
     * @return $this
     */
    public function setConfigFile(string $newPath, string $name = 'dockr.json'): self
    {
        $setSlashes = (!ends_with($newPath, DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '');
        $configFile = $newPath . $setSlashes . $name;

        $this->configFile = expand_tilde($configFile);

        return $this->loadConfigFile();
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

    /**
     * Loads the config file (dockr.json).
     *
     * @return $this
     */
    private function loadConfigFile(): self
    {
        if (file_exists($this->configFile)) {
            $this->data = json_decode(file_get_contents($this->configFile), true);
        }

        return $this;
    }
}
