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

use evidev\laravel4\extensions\workbench\tests\fixtures\stubs\WorkbenchCommandWrapper;
use evidev\laravel4\extensions\workbench\PackageCreator;
use evidev\laravel4\extensions\workbench\tests\fixtures\stubs\ConfigStub;
use evidev\laravel4\extensions\workbench\tests\helpers\Helper;
use Illuminate\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;

/**
 * Test class for WorkbenchCommand.
 *
 * @package     evidev\laravel4\extensions\workbench\tests\unit
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class WorkbenchCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * application object
     * 
     * @var array
     */
    private $app;
    
    /**
     * helper instance
     * 
     * @var Helper
     */
    private $helper;

    /**
     * package meta information
     *
     * @var array
     */
    private $meta;

    /**
     * set up test environment
     */
    public function setUp()
    {
        parent::setUp();
        $this->helper = Helper::create();
        $this->app = array(
            'path.base' => vfsStream::setup('workbench')->url(),
            'config' => array(
                'workbench' => ConfigStub::create()->config()->get('workbench')
            )
        );
        $this->meta = array(
            'vendor' => 'Vendor',
            'name' => 'Package',
            'package' => 'vendor/package',
        );
        $this->meta['namespace'] = $this->meta['vendor'].'\\\\'.$this->meta['name'];
    }

    /**
     * reset test environment
     */
    public function tearDown()
    {
    }
    
    /**
     * get a command instance
     * 
     * @return WorkbenchCommandWrapper
     */
    private function getCommand()
    {
        $command = WorkbenchCommandWrapper::create(
            new PackageCreator(new Filesystem)
        );
        $command->setLaravel($this->app);
        return $command;
    }

    public function testComposerFileWithoutOptions()
    {
        $this->getCommand()->execute(array('package' => $this->meta['package']));
        $path = $this->app['path.base'].'/workbench/'.$this->meta['package'];
        $composerfile = $path.'/composer.json';

        // check file
        $this->assertFileExists($composerfile);

        // check authors
        $composer = $this->helper->getJSON($composerfile);
        $this->assertEquals($this->meta['package'], $composer->name);
        $this->assertCount(
            count($this->app['config']['workbench']['composer']['authors']),
            $composer->authors
        );
        $this->assertEquals(
            $this->app['config']['workbench']['composer']['authors'][1]['homepage'],
            $composer->authors[1]->homepage
        );
        
        // check autoload psr-0
        $this->objectHasAttribute(
            $this->meta['namespace'],
            $composer->autoload->{'psr-0'},
            'Autoload - PSR-0 key check: '
        );

        $this->assertEquals(
            $composer->autoload->{'psr-0'}->{$this->meta['namespace']},
            'src/',
            'Autoload - PSR-0 value check: '
        );

    }

    public function testProviderFileWithoutOptions()
    {
        $this->getCommand()->execute(array('package' => $this->meta['package']));
        $path = $this->app['path.base'].'/workbench/'.$this->meta['package'];
        $providerfile = $path.'/src/'.
            $this->meta['vendor'].'/'.$this->meta['name'].'/PackageServiceProvider.php';

        // check file
        $this->assertFileExists($providerfile);


        // check namespace
        $matches = array();
        $this->assertTrue(
            1 === preg_match(
                '/namespace\s+([^\s;]+)/',
                file_get_contents($providerfile),
                $matches
            )
        );
        
        $this->assertEquals(
            preg_replace('/[\\\\]+/', '\\', $this->meta['namespace']),
            $matches[1]
        );
    }

    public function testComposerFileWithPsr0Option()
    {
        $this->getCommand()->execute(
            array(
                'package' => $this->meta['package'],
                '--psr0' => $this->meta['vendor']
            )
        );
        $path = $this->app['path.base'].'/workbench/'.$this->meta['package'];
        $composerfile = $path.'/composer.json';
        $this->assertFileExists($composerfile);
        $composer = $this->helper->getJSON($composerfile);
        
        // check autoload psr-0
        $this->objectHasAttribute(
            $this->meta['vendor'],
            $composer->autoload->{'psr-0'},
            'Autoload - PSR-0 key check: '
        );

        $this->assertEquals(
            $composer->autoload->{'psr-0'}->{$this->meta['vendor']},
            'src/',
            'Autoload - PSR-0 value check: '
        );
    }

    public function testProviderFileWithNsOption()
    {
        $namespace = $this->meta['namespace'].'\\\\'.$this->meta['vendor'];
        $this->getCommand()->execute(
            array(
                'package' => $this->meta['package'],
                '--ns' => $namespace
            )
        );
        $path = $this->app['path.base'].'/workbench/'.$this->meta['package'];
        $providerfile = $path.'/src/'.
            $this->meta['vendor'].'/'.$this->meta['name'].'/'.
            $this->meta['vendor'].'/PackageServiceProvider.php';

        // check file
        $this->assertFileExists($providerfile);


        // check namespace
        $matches = array();
        $this->assertTrue(
            1 === preg_match(
                '/namespace\s+([^\s;]+)/',
                file_get_contents($providerfile),
                $matches
            )
        );

        $this->assertEquals(
            preg_replace('/[\\\\]+/', '\\', $namespace),
            $matches[1]
        );
    }

    public function testComposerFileWithPsr0AndNsOption()
    {
        $namespace = $this->meta['namespace'].'\\\\'.$this->meta['vendor'];
        $this->getCommand()->execute(
            array(
                'package' => $this->meta['package'],
                '--psr0' => $this->meta['vendor'],
                '--ns' => $namespace
            )
        );
        $path = $this->app['path.base'].'/workbench/'.$this->meta['package'];
        $composerfile = $path.'/composer.json';
        $this->assertFileExists($composerfile);
        $composer = $this->helper->getJSON($composerfile);

        // check autoload psr-0
        $this->objectHasAttribute(
            $this->meta['vendor'],
            $composer->autoload->{'psr-0'},
            'Autoload - PSR-0 key check: '
        );

        $this->assertEquals(
            $composer->autoload->{'psr-0'}->{$this->meta['vendor']},
            'src/',
            'Autoload - PSR-0 value check: '
        );
    }

    public function testProviderFileWithPsr0AndNsOption()
    {
        $namespace = $this->meta['namespace'].'\\\\'.$this->meta['vendor'];
        $this->getCommand()->execute(
            array(
                'package' => $this->meta['package'],
                '--psr0' => $this->meta['vendor'],
                '--ns' => $namespace
            )
        );
        $path = $this->app['path.base'].'/workbench/'.$this->meta['package'];
        $providerfile = $path.'/src/'.
            $this->meta['vendor'].'/'.$this->meta['name'].'/'.
            $this->meta['vendor'].'/PackageServiceProvider.php';

        // check file
        $this->assertFileExists($providerfile);


        // check namespace
        $matches = array();
        $this->assertTrue(
            1 === preg_match(
                '/namespace\s+([^\s;]+)/',
                file_get_contents($providerfile),
                $matches
            )
        );

        $this->assertEquals(
            preg_replace('/[\\\\]+/', '\\', $namespace),
            $matches[1]
        );
    }
}
