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
 * @package     evidev\laravel4\extensions\workbench
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace evidev\laravel4\extensions\workbench;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Workbench\Package;

/**
 * PackageCreator
 *
 * @package     evidev\laravel4\extensions\workbench
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
final class PackageCreator extends \Illuminate\Workbench\PackageCreator
{

    /**
     * @inheritdoc
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * @inheritdoc
     */
    protected function formatPackageStub(Package $package, $stub)
    {
        return $this->formatStub(get_object_vars($package), $stub);
    }

    /**
     * format composer.json stubs
     *
     * @param  \Illuminate\Workbench\Package  $package
     * @param  string  $stub
     * @return string
     */
    protected function formatComposerStub(Package $package, $stub)
    {
        $vars = get_object_vars($package);
        foreach ($vars as $key => $value) {
            if (is_string($vars[$key])) {
                $stub = str_replace('{{' . snake_case($key) . '}}', addslashes($value), $stub);
            } elseif (is_array($vars[$key])) {
                $stub = str_replace('"{{' . snake_case($key) . '}}"', json_encode($value), $stub);
            }
        }

        return $stub;
    }

    /**
     * format stub delegate
     *
     * @param array $vars       property list to parse
     * @param string $stub      stub to format
     */
    private function formatStub($vars, $stub)
    {
        foreach ($vars as $key => $value) {
            if (is_string($vars[$key])) {
                $stub = str_replace('{{' . snake_case($key) . '}}', $value, $stub);
            }
        }

        return $stub;
    }

    /**
     * @inheritdoc
     */
    protected function writeComposerFile(Package $package, $directory, $plain)
    {
        if (isset($package->authors)) {
            $stub = $this->getComposerStub($plain);
            $stub = $this->formatComposerStub($package, $stub);
        } else {
            $stub = parent::getComposerStub($plain);
            $stub = $this->formatPackageStub($package, $stub);
        }
        $this->files->put($directory . '/composer.json', $stub);
    }

    /**
     * @inheritdoc
     */
    protected function getComposerStub($plain)
    {
        if ($plain) {
            return $this->files->get(__DIR__ . '/stubs/plain.composer.json');
        }

        return $this->files->get(__DIR__ . '/stubs/composer.json');
    }

    /**
     * @inheritdoc
     */
    protected function getProviderStub(Package $package, $plain)
    {
        return $this->formatPackageStub($package, $this->getProviderFile($plain, $package));
    }

    /**
     * Load the raw service provider file.
     *
     * @param  bool   $plain
     * @param  \Illuminate\Workbench\Package  $package
     * @return string
     */
    protected function getProviderFile($plain, $package)
    {
        if (!isset($package->namespace) || empty($package->namespace)) {
            return parent::getProviderFile($plain);
        }
        if ($plain) {
            return $this->files->get(__DIR__ . '/stubs/plain.provider.stub');
        } else {
            return $this->files->get(__DIR__ . '/stubs/provider.stub');
        }
    }

    /**
     * Create the main source directory for the package.
     *
     * @param  \Illuminate\Workbench\Package  $package
     * @param  string  $directory
     * @return string
     */
    protected function createClassDirectory(Package $package, $directory)
    {
        $path = $directory . '/src/' .
            (isset($package->namespace) && !empty($package->namespace) ?
                preg_replace('/\/+/', '/', str_replace('\\', '/', $package->namespace) . '/') :
                $package->vendor . '/' . $package->name);

        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true);
        }

        return $path;
    }
}
