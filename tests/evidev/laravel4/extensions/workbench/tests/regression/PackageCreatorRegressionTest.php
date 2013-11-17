<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * The MIT License
 *
 * Copyright 2013 Eric VILLARD <dev@eviweb.fr>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     evidev\laravel4\extensions\workbench\tests\unit
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace evidev\laravel4\extensions\workbench\tests\regression;

use evidev\laravel4\extensions\workbench\PackageCreator;
use org\bovigo\vfs\vfsStream;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Workbench\Package;

/**
 * Regression tests for PackageCreator
 *
 * to ensure backward compatibility
 *
 * @package     evidev\laravel4\extensions\workbench\tests\unit
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class PackageCreatorRegressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * root directory
     * 
     * @var vfsStreamDirectory 
     */
    private $rootdir;

    /**
     * package object
     * 
     * @var Package
     */
    private $package;

    /**
     * package creator object
     *
     * @var PackageCreator
     */
    private $creator;

    /**
     * set up test environment
     */
    public function setUp()
    {
        parent::setUp();
        $this->rootdir = vfsStream::setup('workbench');
        $this->package = new Package('evidev', 'workbench', 'AUTHOR', 'EMAIL');
        $this->creator = new PackageCreator(new Filesystem());
    }

    /**
     * reset test environment
     */
    public function tearDown()
    {
    }

    /**
     * composer.json guard
     */
    public function testWriteComposerGuard()
    {
        $this->creator->writeSupportFiles(
            $this->package,
            $this->rootdir->url(),
            true
        );
        $file = $this->rootdir->url().'/composer.json';
        $this->assertFileExists($file);
        $composer = json_decode(file_get_contents($file));
        // check package name
        $this->assertEquals(
            $this->package->getFullName(),
            $composer->name,
            'Package name check: '
        );

        // check authors
        $this->assertCount(1, $composer->authors, 'Authors check: ');
        $this->assertEquals(
            $this->package->author,
            $composer->authors[0]->name,
            'Author name check: '
        );
        $this->assertEquals(
            $this->package->email,
            $composer->authors[0]->email,
            'Author email check: '
        );

        // check autoload
        $psr0 = $this->package->vendor.'\\'.$this->package->name;
        $this->objectHasAttribute(
            $psr0,
            $composer->autoload->{'psr-0'},
            'Autoload - PSR-0 key check: '
        );

        $this->assertEquals(
            $composer->autoload->{'psr-0'}->$psr0,
            'src/',
            'Autoload - PSR-0 value check: '
        );
    }
}
