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
        $shellExec->exec("ls -K", false);
        $this->setExpectedExceptionRegExp('Cognitive\ShellExec\ShellExecException', '/Error text:.* option/');
        $shellExec->exec("ls -K");
    }

    /**
     * Test ShellExecExeption.
     *
     * @return void
     */
    public function testExecExceptionWithOutput()
    {
        $shellExec = new ShellExec();
        $this->setExpectedExceptionRegExp('Cognitive\ShellExec\ShellExecException', '/: ls: .* option/');
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

    /**
     * Test explodeLinesToArray.
     *
     * @return void
     */
    public function testExplodeLinesToArray()
    {
        $shellExec = new ShellExec();
        $lines = $shellExec->explodeLinesToArray("\ns2\ns3\n");
        $this->assertEquals(2, count($lines));
        $this->assertEquals('s2', $lines[1]);
        $this->assertEquals('s3', $lines[2]);
    }
}
