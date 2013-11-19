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
 * @package     evidev\laravel4\extensions\workbench\tests\helpers
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace evidev\laravel4\extensions\workbench\tests\helpers;

/**
 * Helper class
 *
 * provides some useful services
 * 
 * @package     evidev\laravel4\extensions\workbench\tests\helpers
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright   (c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
final class Helper
{
    /**
     * constructor
     */
    private function __construct()
    {
    }
    
    /**
     * static factory method
     * 
     * @return Helper
     */
    public static function create()
    {
        return new static();
    }
    
    /**
     * get a json representation object of a file
     * 
     * @param string $file  file to get JSON from
     * @return string       returns JSON object
     */
    public function getJSON($file)
    {
        return json_decode(
            str_replace('\\', '\\\\', file_get_contents($file))
        );
    }
}