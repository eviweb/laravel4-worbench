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

use evidev\laravel4\extensions\workbench\console\WorkbenchCommand;
use evidev\laravel4\extensions\workbench\PackageCreator;
use evidev\laravel4\extensions\workbench\tests\fixtures\stubs\ConfigStub;
use evidev\laravel4\extensions\workbench\tests\helpers\Helper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Illuminate\Filesystem\Filesystem;

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
     * set up test environment
     */
    public function setUp()
    {
        parent::setUp();
        $this->helper = Helper::create();
        $this->app = array(
            'path.base' => sys_get_temp_dir().'/'.uniqid('workbench-'),
            'config' => array(
                'workbench' => ConfigStub::create()->config()->get('workbench')
            )
        );
        mkdir($this->app['path.base'], 0777);
    }

    /**
     * reset test environment
     */
    public function tearDown()
    {
        (new Filesystem())->deleteDirectory($this->app['path.base'], false);
    }
    
    /**
     * get a command instance
     * 
     * @return WorkbenchCommand
     */
    private function getCommand()
    {
        $application = new Application();
        $command = new WorkbenchCommand(new PackageCreator(new Filesystem()));
        $command->setLaravel($this->app);
        $application->add($command);
        return $application->find('workbench');
    }

    public function testWithoutOptions()
    {
        $vendor = 'Vendor';
        $name = 'Package';
        $package = 'vendor/package';
        $command = $this->getCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('command' => $command->getName(), 'package' => $package)
        );
        $path = $this->app['path.base'].'/workbench/'.$package;
        $composerfile = $path.'/composer.json';
        $providerfile = $path.'/src/'.$vendor.'/'.$name.'/PackageServiceProvider.php';

        // check paths
        $this->assertFileExists($path);
        $this->assertFileExists($composerfile);
        $this->assertFileExists($providerfile);

        // check composer authors
        $composer = $this->helper->getJSON($composerfile);
        $this->assertEquals($package, $composer->name);
        $this->assertCount(
            count($this->app['config']['workbench']['composer']['authors']),
            $composer->authors
        );
        $this->assertEquals(
            $this->app['config']['workbench']['composer']['authors'][1]['homepage'],
            $composer->authors[1]->homepage
        );
        
        // check autoload psr-0
        $psr0 = $vendor.'\\\\'.$name;
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

        // check service provider namespace
        $matches = array();
        $this->assertTrue(
            1 === preg_match(
                '/namespace\s+([^\s;]+)/',
                file_get_contents($providerfile),
                $matches
            )
        );
        $namespace = $matches[1];
        $this->assertEquals(
            preg_replace('/[\\\\]+/', '\\', $psr0),
            $namespace
        );
    }

    public function testWithPsr0Option()
    {
        $vendor = 'Vendor';
        $name = 'Package';
        $package = 'vendor/package';
        $command = $this->getCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'package' => $package,
                '--psr0' => $vendor
            )
        );
        $path = $this->app['path.base'].'/workbench/'.$package;
        $composerfile = $path.'/composer.json';
        $this->assertFileExists($composerfile);
        $composer = $this->helper->getJSON($composerfile);
        
        // check autoload psr-0
        $this->objectHasAttribute(
            $vendor,
            $composer->autoload->{'psr-0'},
            'Autoload - PSR-0 key check: '
        );

        $this->assertEquals(
            $composer->autoload->{'psr-0'}->$vendor,
            'src/',
            'Autoload - PSR-0 value check: '
        );
    }

    public function testWithNsOption()
    {
        $this->markTestIncomplete('to be implemented');
    }

    public function testWithPsr0AndNsOption()
    {
        $this->markTestIncomplete('to be implemented');
    }
}
