<?php
/**
 * Testing ShellExec.
 * @package ShellExec\Tests
 */

namespace ShellExec\Tests;

use ShellExec\ShellExec;

/**
 * Class to test Git
 */
class ShellExecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ShellExecExeption.
     *
     * @return void
     */
    public function testShellExecExeption()
    {
        $shellExec = new ShellExec();
        $this->setExpectedExceptionRegExp('ShellExec\ShellExecException', '/Command not found/');
        $shellExec->exec('CommandNotFound', true);
    }
}
