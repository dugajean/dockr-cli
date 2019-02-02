<?php

namespace Dockr;

class Config
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Config's file name.
     */
    const FILE = './dockr.json';

    /**
     * Required config structure.
     */
    const STRUCTURE = [
        'project-name', 'project-domain', 'webserver',
        'cache-store', 'php-version', 'php-extensions',
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
            if ($this->validate($dataOrKey)) {
                $this->data = $dataOrKey;
            } else {
                throw new \RuntimeException('Could not save dockr.json.');
            }

        } elseif ($this->data) {
            $this->data[$dataOrKey] = (string)$value;
        }

        return (bool)file_put_contents(self::FILE, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    /**
     * Fetch a key from the config.
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        if (!$this->data) {
            throw new \RuntimeException('Config file does not exist or ist empty.');
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    /**
     * Validate the configuration structure.
     *
     * @param array $data
     *
     * @return bool
     */
    public function validate(array $data)
    {
        return array_keys($data) == self::STRUCTURE;
    }
}
