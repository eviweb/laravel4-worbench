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

use evidev\laravel4\extensions\workbench\Package;

/**
 * Test class for Package
 *
 * @package     evidev\laravel4\extensions\workbench\tests\unit
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class PackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * data object
     * 
     * @var stdClass 
     */
    private $obj;

    /**
     * set up test environment
     */
    public function setUp()
    {
        parent::setUp();
        $this->obj = new \stdClass();
        $this->obj->vendor = 'Evidev';
        $this->obj->name = 'Workbench';
        $this->obj->author = 'AUTHOR';
        $this->obj->email = 'EMAIL';
        $this->obj->psr0 = 'new\psr0///compliant\\namespace';
        $this->obj->psr0expected = addslashes('new\psr0\compliant\namespace');
        $this->obj->namespace = 'new\long///name\\space';
        $this->obj->namespaceexpected = 'new\long\name\space';
        $this->obj->license = 'MIT';
    }

    /**
     * reset test environment
     */
    public function tearDown()
    {
    }

    /**
     * check public properties of an object are empty
     *
     * @param type $object      object to test
     * @param type $excludes    propperty not to check
     */
    private function checkEmptyState($object, $excludes = array())
    {
        $props = get_object_vars($object);
        foreach ($props as $name => $value) {
            print('<pre>'.print_r($name.' / '.(string)(integer)  in_array($name, $excludes), true).'</pre>');
            if (!in_array($name, $excludes)) {
                $this->assertEmpty($props[$name], 'Check '.$name.' with value: '.$value.' is empty');
            }
        }
    }

    public function testEmptyInst()
    {
        $inst = Package::emptyInst();
        $this->checkEmptyState($inst);
    }

    public function testVendorProvider()
    {
        $given = $this->obj->vendor;
        $inst = Package::emptyInst()->vendorProvider($given);
        $this->assertEquals($given, $inst->vendor);
        $this->checkEmptyState($inst, array('vendor', 'lowerVendor'));
    }

    public function testNameProvider()
    {
        $given = $this->obj->name;
        $inst = Package::emptyInst()->nameProvider($given);
        $this->assertEquals($given, $inst->name);
        $this->checkEmptyState($inst, array('name', 'lowerName'));
    }

    public function testAuthorProvider()
    {
        $given = $this->obj->author;
        $inst = Package::emptyInst()->authorProvider($given);
        $this->assertEquals($given, $inst->author);
        $this->checkEmptyState($inst, array('author'));
    }

    public function testEmailProvider()
    {
        $given = $this->obj->email;
        $inst = Package::emptyInst()->emailProvider($given);
        $this->assertEquals($given, $inst->email);
        $this->checkEmptyState($inst, array('email'));
    }

    public function testPsr0Provider()
    {
        $given = $this->obj->psr0;
        $inst = Package::emptyInst()->psr0Provider($given);
        $this->assertEquals($this->obj->psr0expected, $inst->psr0);
        $this->checkEmptyState($inst, array('psr0'));
    }

    public function testNamespaceProvider()
    {
        $given = $this->obj->namespace;
        $inst = Package::emptyInst()->namespaceProvider($given);
        $this->assertEquals($this->obj->namespaceexpected, $inst->namespace);
        $this->checkEmptyState($inst, array('namespace'));
    }

    public function testLicenseProvider()
    {
        $given = $this->obj->license;
        $inst = Package::emptyInst()->licenseProvider($given);
        $this->assertEquals($this->obj->license, $inst->license);
        $this->checkEmptyState($inst, array('license'));
    }

    public function testCumulativeFactory()
    {
        $inst = Package::emptyInst()
            ->vendorProvider($this->obj->vendor)
            ->nameProvider($this->obj->name)
            ->authorProvider($this->obj->author)
            ->emailProvider($this->obj->email)
            ->psr0Provider($this->obj->psr0)
            ->namespaceProvider($this->obj->namespace)
            ->licenseProvider($this->obj->license);
        //
        $this->assertEquals($this->obj->vendor, $inst->vendor);
        $this->assertEquals($this->obj->name, $inst->name);
        $this->assertEquals($this->obj->author, $inst->author);
        $this->assertEquals($this->obj->email, $inst->email);
        $this->assertEquals($this->obj->psr0expected, $inst->psr0);
        $this->assertEquals($this->obj->namespaceexpected, $inst->namespace);
        $this->assertEquals(
            strtolower($this->obj->vendor).'/'.strtolower($this->obj->name),
            $inst->getFullName()
        );
        $this->assertEquals($this->obj->license, $inst->license);
    }
}
