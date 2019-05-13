<?php

namespace Dockr\PHPUnit\Tests;

use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
   /**
     * TestCase setup.
     *
     * @return void
     * @throws \org\bovigo\vfs\vfsStreamException
     */
    protected function setUp()
    {
        parent::setUp();

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('dockrVirtualspace'));
    }
}
