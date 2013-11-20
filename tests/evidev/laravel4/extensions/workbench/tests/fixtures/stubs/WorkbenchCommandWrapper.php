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
 * @package     evidev\laravel4\extensions\workbench\tests\fixtures\stubs
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace evidev\laravel4\extensions\workbench\tests\fixtures\stubs;

use evidev\laravel4\extensions\workbench\console\WorkbenchCommand;
use evidev\laravel4\extensions\workbench\PackageCreator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use ReflectionClass;

/**
 * WorkbenchCommandWrapper
 *
 * wrapper for the WorkbenchCommand used to bypass callComposerUpdate calls
 * this allows to run tests using a virtual filesystem
 *
 * @package     evidev\laravel4\extensions\workbench\tests\fixtures\stubs
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
final class WorkbenchCommandWrapper
{
    /**
     * command object
     * 
     * @var WorkbenchCommand
     */
    private $command;

    /**
     * constructor
     *
     * @param \evidev\laravel4\extensions\workbench\PackageCreator $creator
     */
    private function __construct($creator)
    {
        \Patchwork\replace(
            'evidev\laravel4\extensions\workbench\console\WorkbenchCommand::callComposerUpdate',
            function ($path) {
                return true;
            }
        );
        $this->command = new WorkbenchCommand($creator);
    }

    /**
     * static factory method
     *
     * @param \evidev\laravel4\extensions\workbench\PackageCreator $creator
     * @return \WorkbenchCommandWrapper
     */
    public static function create(PackageCreator $creator)
    {
        return new static($creator);
    }

    /**
     * execute the command
     */
    public function fire()
    {
        $reflect = new ReflectionClass(get_class($this->command));
        $runCreator = $reflect->getMethod('runCreator');
        $runCreator->setAccessible(true);
        $buildPackage = $reflect->getMethod('buildPackage');
        $buildPackage->setAccessible(true);
        return $runCreator->invokeArgs(
            $this->command,
            array($buildPackage->invoke($this->command))
        );
    }

     /**
     * Executes the command.
     *
     * Available options:
     *
     *  * interactive: Sets the input interactive flag
     *  * decorated:   Sets the output decorated flag
     *  * verbosity:   Sets the output verbosity flag
     *
     * @param array $input   An array of arguments and options
     * @param array $options An array of options
     *
     * @return integer The command exit code
     */
    public function execute(array $input, array $options = array())
    {
        $app = new Application();
        $app->add($this->command);
        $name =  $this->command->getName();
        $wrapper = new CommandTester($app->find($name));
        $input['command'] = $name;
        return $wrapper->execute($input, $options);
    }

    /**
     * delegate method calls to the encapsulated command using __call PHP magic method
     *
     * @param string    $name       called method name
     * @param array     $arguments  arguments passed to the method
     * @return mixed    returns what the called method returns
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->command, $name)) {
            throw new \BadFunctionCallException('No method found in '.get_class($this->command));
        }
        return call_user_func_array(
            array($this->command, $name),
            $arguments
        );
    }
}
