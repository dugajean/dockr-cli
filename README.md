# Dockr CLI

[![Build Status](https://travis-ci.org/dugajean/dockr-cli.svg?branch=master)](https://travis-ci.org/dugajean/dockr-cli) 
[![Latest Stable Version](https://poser.pugx.org/dugajean/dockr-cli/v/stable)](https://packagist.org/packages/dugajean/dockr-cli) 
[![Total Downloads](https://poser.pugx.org/dugajean/dockr-cli/downloads)](https://packagist.org/packages/dugajean/dockr-cli) 
[![License](https://poser.pugx.org/dugajean/dockr-cli/license)](https://packagist.org/packages/dugajean/dockr-cli) 

Easy Docker Compose setup for your LAMP and LEMP projects.

## Requirements

- Docker & docker-compose
- PHP 7.1+
- `ext-json`
- `ext-ctype`

## Download

###### For direct use

To download the latest release, head over to [Releases](https://github.com/dugajean/dockr-cli/releases) and pick the latest PHAR. Then:

```bash
$ dockr.phar --version
```

Feel free to move this to `/usr/local/bin` and remove the `.phar` extension.

###### Per project installation

```bash
$ composer require dugajean/dockr-cli --dev
```

```bash
$ vendor/bin/dockr --version
```

## Usage

Run the following command to initialize dockr:

```bash
$ dockr init
```
 
Open the newly created file `dockr.json` and read through it. Make sure everything is what you expect it to be. Then refer to the `aliases` section of the file. There you will see a couple of aliases preset for you: One will turn on the Docker containers and the other will shut them off.

Use as follows:

```bash
$ dockr up

$ dockr down
```

You can also set your own aliases there to control your setup. You can set aliases for SSH-ing into a container, delete the images or whatever you want. You can also point to a class which extends Symfony's `Command` class by providing the fully qualified namespace. That would look like this:

```
// ...

"aliases": {
    // ...
    "myalias": [
        "\\Fully\\Qualified\\Namespace\\To\\MyCommand"
    ]
}
```

For a full list of available commands, run `dockr` and if you need help with a specific command run `dockr help <command>`.

## Testing

```bash
$ vendor/bin/phpunit
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License
Pouch is released under [the MIT License](LICENSE).