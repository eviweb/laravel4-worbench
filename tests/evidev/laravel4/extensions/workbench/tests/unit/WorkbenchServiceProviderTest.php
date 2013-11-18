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

use evidev\laravel4\extensions\workbench\WorkbenchServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;

/**
 * Test class for WorkbenchServiceProvider
 *
 * @package     evidev\laravel4\extensions\workbench\tests\unit
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class WorkbenchServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * application object
     * 
     * @var Container
     */
    private $app;

    /**
     * set up test environment
     */
    public function setUp()
    {
        parent::setUp();
        $this->createApplication();
    }

    /**
     * reset test environment
     */
    public function tearDown()
    {
    }

    /**
     * create application object
     */
    private function createApplication()
    {
        $this->app = new Container();
        $this->app['events'] = new Dispatcher($this->app);
        $this->app['files'] = new Filesystem();
        $this->app->share = function (Closure $closure) {
            return function ($container) use ($closure) {
                static $object;
                if (is_null($object)) {
                    $object = $closure($container);
                }
                return $object;
            };
        };
    }
    
    public function testProvidesReturnTypes()
    {
        $service = new WorkbenchServiceProvider($this->app);
        $service->register();
        $provided = $service->provides();
        $this->assertInstanceOf(
            'evidev\laravel4\extensions\workbench\PackageCreator',
            $this->app[$provided[0]]
        );
        $this->assertInstanceOf(
            'evidev\laravel4\extensions\workbench\console\WorkbenchCommand',
            $this->app[$provided[1]]
        );
    }
}
