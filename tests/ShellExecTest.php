<?php
/**
 * Testing ShellExec.
 * @package ShellExec\Tests
 */

namespace Cognitive\ShellExec\Tests;

use Cognitive\ShellExec\ShellExec;

/**
 * Class to test Git
 */
class ShellExecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test exec.
     *
     * @return void
     */
    public function testExec()
    {
        $shellExec = new ShellExec();
        $curDir = $shellExec->exec("pwd");
        $this->assertNotEquals('', $curDir);
    }

    /**
     * Test ShellExecExeption.
     *
     * @return void
     */
    public function testExecException()
    {
        $shellExec = new ShellExec();
        $shellExec->exec("ls -K");
        $this->setExpectedExceptionRegExp('Cognitive\ShellExec\ShellExecException', '/Error text:.*illegal option/');
        $shellExec->exec("ls -K", true);
    }

    /**
     * Test ShellExecExeption.
     *
     * @return void
     */
    public function testExecExceptionWithOutput()
    {
        $shellExec = new ShellExec();
        $this->setExpectedExceptionRegExp('Cognitive\ShellExec\ShellExecException', '/: ls: illegal option/');
        $shellExec->exec("ls -K 2>&1", true);
    }

    /**
     * Test ShellExecExeption.
     *
     * @return void
     */
    public function testExecExceptionCommandNotFound()
    {
        $shellExec = new ShellExec();
        $this->setExpectedExceptionRegExp('Cognitive\ShellExec\ShellExecException', '/Command not found/');
        $shellExec->exec('CommandNotFound', true);
    }
}
