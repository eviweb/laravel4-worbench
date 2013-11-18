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

namespace evidev\laravel4\extensions\workbench\tests\unit;

use evidev\laravel4\extensions\workbench\PackageCreator;
use evidev\laravel4\extensions\workbench\Package;
use evidev\laravel4\extensions\workbench\tests\fixtures\stubs\ConfigStub;
use org\bovigo\vfs\vfsStream;
use Illuminate\Filesystem\Filesystem;

/**
 * Test class for PackageCreator
 *
 * @package     evidev\laravel4\extensions\workbench\tests\unit
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class PackageCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * root directory
     *
     * @var vfsStreamDirectory
     */
    private $rootdir;

    /**
     * configuration stub provider
     *
     * @var ConfigStub
     */
    private $config;

    /**
     * old fashioned package
     *
     * @var Package
     */
    private $oldfashioned;

    /**
     * package using the new implementation
     *
     * @var Package
     */
    private $newpackage;

    /**
     * set up test environment
     */
    public function setUp()
    {
        parent::setUp();
        $this->rootdir = vfsStream::setup('workbench');
        $this->config = ConfigStub::create();
        $this->createPackages();
    }

    /**
     * reset test environment
     */
    public function tearDown()
    {
    }

    /**
     * create two packages one old fashioned, the other using the new implementation
     */
    private function createPackages()
    {
        $config = $this->config->config()->get('workbench.composer');
        $this->oldfashioned = Package::emptyInst()
            ->vendorProvider('Vendor')
            ->nameProvider('OldFashioned')
            ->authorsProvider($config['authors']);
        $this->newpackage = Package::emptyInst()
            ->vendorProvider('Vendor')
            ->nameProvider('NewPackage')
            ->authorsProvider($config['authors'])
            ->licenseProvider($config['license'])
            ->psr0Provider('psr\root')
            ->namespaceProvider('custom\\namespace');
        $creator = new PackageCreator(new Filesystem());
        $creator->create($this->oldfashioned, $this->rootdir->url(), true);
        $creator->create($this->newpackage, $this->rootdir->url(), true);
    }

    /**
     * get composer.json files of the created packages
     * 
     * @return array        returns the path of the composer.json file
     */
    private function getComposerFiles()
    {
        return array(
            'oldfashioned' => $this->rootdir->url().'/vendor/old-fashioned/composer.json',
            'newpackage' => $this->rootdir->url().'/vendor/new-package/composer.json'
        );
    }

    /**
     * get composer.json files of the created packages
     *
     * @return array        returns the path of the composer.json file
     */
    private function getServiceProviderFiles()
    {
        return array(
            'oldfashioned' => $this->rootdir->url().'/vendor/old-fashioned/src/'.
                $this->oldfashioned->vendor.'/'.
                $this->oldfashioned->name.'/'.
                $this->oldfashioned->name.'ServiceProvider.php',
            'newpackage' => $this->rootdir->url().'/vendor/new-package/src/'.
                preg_replace('/\/+/', '/', str_replace('\\', '/', $this->newpackage->namespace).'/').
                $this->newpackage->name.'ServiceProvider.php'
        );
    }

    //--------------------------------------------------------------------------
    // composer.json tests
    //--------------------------------------------------------------------------
    public function testComposerFilesExist()
    {
        $files = $this->getComposerFiles();
        $this->assertFileExists($files['oldfashioned']);
        $this->assertFileExists($files['newpackage']);
    }

    public function testOldFashionedComposerIntegrity()
    {
        $files = $this->getComposerFiles();
        $composer = json_decode(file_get_contents($files['oldfashioned']));

        // check package name
        $this->assertEquals(
            $this->oldfashioned->getFullName(),
            $composer->name,
            'Package name check: '
        );

        // check authors
        $this->assertCount(2, $composer->authors, 'Authors check: ');
        $this->assertEquals(
            $this->oldfashioned->author,
            $composer->authors[0]->name,
            'Author name check: '
        );
        $this->assertEquals(
            $this->oldfashioned->email,
            $composer->authors[0]->email,
            'Author email check: '
        );

        // check autoload
        $psr0 = $this->oldfashioned->vendor.'\\\\'.$this->oldfashioned->name;
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

    public function testPackageAuthors()
    {
        $files = $this->getComposerFiles();
        $composer = json_decode(file_get_contents($files['newpackage']));
        $authors = $this->config->config()->get('workbench.composer.authors');
        $this->assertCount(count($authors), $composer->authors);
        $this->assertEquals($authors[0]['name'], $composer->authors[0]->name);
        $this->assertEquals($authors[1]['email'], $composer->authors[1]->email);
    }

    public function testPackagePsr0()
    {
        $files = $this->getComposerFiles();
        $composer = json_decode(file_get_contents($files['newpackage']));
        $psr0 = $this->newpackage->psr0;
        $this->assertTrue(isset($composer->autoload->{"psr-0"}->$psr0));
        $this->assertEquals('src/', $composer->autoload->{"psr-0"}->$psr0);
    }

    //--------------------------------------------------------------------------
    // service provider tests
    //--------------------------------------------------------------------------
    public function testServiceProviderFilesExist()
    {
        $files = $this->getServiceProviderFiles();
        $this->assertFileExists($files['oldfashioned']);
        $this->assertFileExists($files['newpackage']);
    }

    public function testOldFashionedServiceProviderIntegrity()
    {
        $files = $this->getServiceProviderFiles();
        $matches = array();
        $this->assertTrue(
            1 === preg_match(
                '/namespace\s+([^\s;]+)/',
                file_get_contents($files['oldfashioned']),
                $matches
            )
        );
        $namespace = $matches[1];
        $this->assertEquals(
            $this->oldfashioned->vendor.'\\'.$this->oldfashioned->name,
            $namespace
        );
    }

    public function testPackageServiceProviderNamespace()
    {
        $files = $this->getServiceProviderFiles();
        $matches = array();
        $this->assertTrue(
            1 === preg_match(
                '/namespace\s+([^\s;]+)/',
                file_get_contents($files['newpackage']),
                $matches
            )
        );
        $namespace = $matches[1];
        $this->assertEquals(
            preg_replace('/\\+/', '\\', $this->newpackage->namespace),
            $namespace
        );
    }
}
