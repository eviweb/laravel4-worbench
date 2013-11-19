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

/**
 * Package
 *
 * this version adds the support of the cumulative factory pattern to facilitate
 * Package extension while keeping backward compatibility
 * {@link http://wiki.apidesign.org/wiki/CumulativeFactory Cumulative factory pattern description}
 *
 * @package     evidev\laravel4\extensions\workbench
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
final class Package extends \Illuminate\Workbench\Package
{
    /**
     * autoload PSR-0 namespace mapping
     * 
     * @var string 
     */
    public $psr0;

    /**
     * default package namespace
     * 
     * @var string
     */
    public $namespace;

    /**
     * package license information
     *
     * @var string
     */
    public $license;

    /**
     * authors information
     *
     * @var array
     */
    public $authors;

    /**
     * private factory method to handle new properties
     */
    private static function newInst()
    {
        $args = func_get_args();
        $inst = new static($args[0], $args[1], $args[2], $args[3]);

        $newProps = array_slice($args, -4);
        $inst->psr0 = $newProps[0];
        if (empty($inst->psr0) &&
            !empty($args[0]) &&
            !empty($args[1])) {
            $inst->psr0 = $args[0].'\\\\'.$args[1];
        }
        $inst->namespace = $newProps[1];
        $inst->license = $newProps[2];
        $inst->authors = $newProps[3];
        
        return $inst;
    }

    //--------------------------------------------------------------------------
    // cumulative factory methods
    //--------------------------------------------------------------------------

    /**
     * get an empty instance of Package
     * 
     * @return Package
     */
    public static function emptyInst()
    {
        return static::newInst('', '', '', '', '', '', '', array());
    }

    /**
     * vendor provider factory method
     * 
     * @param string $vendor    vendor name
     * @return Package
     */
    public function vendorProvider($vendor)
    {
        return static::newInst(
            $vendor,
            $this->name,
            $this->author,
            $this->email,
            $this->psr0,
            $this->namespace,
            $this->license,
            $this->authors
        );
    }

    /**
     * name provider factory method
     *
     * @param string $name      package name
     * @return Package
     */
    public function nameProvider($name)
    {
        return static::newInst(
            $this->vendor,
            $name,
            $this->author,
            $this->email,
            $this->psr0,
            $this->namespace,
            $this->license,
            $this->authors
        );
    }

    /**
     * authors provider factory method
     *
     * author and email properties are set using name and email information
     * of the first element of the provided array
     *
     * @param array $author     authors informations
     * @return Package
     */
    public function authorsProvider($authors)
    {
        return static::newInst(
            $this->vendor,
            $this->name,
            $authors[0]['name'],
            $authors[0]['email'],
            $this->psr0,
            $this->namespace,
            $this->license,
            $authors
        );
    }

    /**
     * psr0 namespace provider factory method
     *
     * @param string $psr0      psr0 namespace
     * @return Package
     */
    public function psr0Provider($psr0)
    {
        return static::newInst(
            $this->vendor,
            $this->name,
            $this->author,
            $this->email,
            str_replace('\\', '\\\\', preg_replace('/[\\\\]+|\/+/', '\\', $psr0)),
            $this->namespace,
            $this->license,
            $this->authors
        );
    }

    /**
     * package namespace provider factory method
     *
     * @param string $namespace package namespace
     * @return Package
     */
    public function namespaceProvider($namespace)
    {
        return static::newInst(
            $this->vendor,
            $this->name,
            $this->author,
            $this->email,
            $this->psr0,
            preg_replace('/[\\\\]+|\/+/', '\\', $namespace),
            $this->license,
            $this->authors
        );
    }

    /**
     * license provider factory method
     *
     * @param string $license   package license information
     * @return Package
     */
    public function licenseProvider($license)
    {
        return static::newInst(
            $this->vendor,
            $this->name,
            $this->author,
            $this->email,
            $this->psr0,
            $this->namespace,
            $license,
            $this->authors
        );
    }
    //--------------------------------------------------------------------------
    // cumulative factory - end
    //--------------------------------------------------------------------------
}
