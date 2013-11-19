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
 * @package     evidev\laravel4\extensions\workbench\console
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace evidev\laravel4\extensions\workbench\console;

use Illuminate\Workbench\Console\WorkbenchMakeCommand;
use evidev\laravel4\extensions\workbench\Package;
use evidev\laravel4\extensions\workbench\PackageCreator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * WorkbenchCommand
 *
 * @package     evidev\laravel4\extensions\workbench\console
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
final class WorkbenchCommand extends WorkbenchMakeCommand
{
    /**
     * constructor
     *
     * @param  \evidev\laravel4\extensions\workbench\PackageCreator  $creator
     * @return void
     */
    public function __construct(PackageCreator $creator)
    {
        parent::__construct($creator);
    }

    /**
     * Build the package details from user input.
     *
     * @return \Illuminate\Workbench\Package
     */
    protected function buildPackage()
    {
        list($vendor, $name) = $this->getPackageSegments();

        $config = $this->laravel['config']['workbench'];

        $psr0 = $this->option('psr0');
        $namespace = $this->option('ns');

        return Package::emptyInst()
            ->vendorProvider($vendor)
            ->nameProvider($name)
            ->authorsProvider($config['composer']['authors'])
            ->licenseProvider($config['composer']['license'])
            ->psr0Provider($psr0)
            ->namespaceProvider($namespace);
    }

    /**
     * Get the package vendor and name segments from the input.
     *
     * @return array
     */
    protected function getPackageSegments()
    {

        $package = $this->argument('package');

        return array_map('studly_case', explode('/', $package, 2));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('package', InputArgument::REQUIRED, 'The name (vendor/name) of the package.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('resources', null, InputOption::VALUE_NONE,
                'Create Laravel specific directories.'),
            array('psr0', null, InputOption::VALUE_OPTIONAL,
                'Specify a specific PSR-0 compliant namespace mapping.', ''),
            array('ns', null, InputOption::VALUE_OPTIONAL,
                'Specify a custom namespace for this package.', ''),
        );
    }
}
