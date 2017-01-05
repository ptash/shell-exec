<?php
/**
 * Testing ShellExecParallel.
 * @package ShellExec\Tests
 */

namespace Cognitive\ShellExec\Tests;

use \Cognitive\ShellExec\ShellExecParallel;

/**
 * Class ShellExecParallelTest
 */
class ShellExecParallelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test exec.
     *
     * @return void
     */
    public function testExec()
    {
        $exec = new ShellExecParallel();
        $code = $exec->exec(array('ls', 'ls -l', 'ps', 'pwd'));
        $this->assertEquals(0, $code, 'Check 0 exit code');
        $code = $exec->exec(array('ls', 'ls --oo', 'ps', 'pwd'));
        $this->assertEquals(1, $code, 'Check 1 exit code');
        $this->expectOutputRegex(
            '/Parallel execution total: 4 success and 0 fail.*' .
            'Parallel execution total: 3 success and 1 fail/s'
        );
    }

    /**
     * Test execEach.
     *
     * @return void
     */
    public function testExecEach()
    {
        $exec = new ShellExecParallel();
        $code = $exec->execEach(
            'ls %opt%',
            array(
                array('%opt%' => ''),
                array('%opt%' => '-la')
            )
        );
        $this->assertEquals(0, $code, 'Check exit code');

        $code = $exec->execEach(
            'ls %opt%',
            array(
                array('%opt%' => ''),
                array('%opt%' => '--ooo')
            )
        );
        $this->assertEquals(1, $code, 'Check 1 exit code');
        $this->expectOutputRegex(
            '/Parallel execution total: 2 success and 0 fail.*' .
            'Parallel execution total: 1 success and 1 fail/s'
        );
    }

    /**
     * Test setMessageStart, setMessageFinishedSuccess, setMessageFinishedWithErrors, setMessageTotal.
     *
     * @return void
     */
    public function testMessageAndParams()
    {
        $exec = new ShellExecParallel();

        $exec->setMessageStart('Go %name%: "%command%"');
        $exec->setMessageFinishedSuccess('%name% "%command%" end success');
        $exec->setMessageFinishedWithErrors('%name% "%command%" end with errors');
        $exec->setMessageTotal('Async execution total: %success% ok and %fail% bad');
        $code = $exec->execEach(
            'ls %opt%',
            array(array('%opt%' => '', '%name%' => 'success command'))
        );
        $this->assertEquals(0, $code, 'Check 0 exit code');
        $code = $exec->execEach(
            'ls %opt%',
            array(array('%opt%' => '--ooo', '%name%' => 'fail command'))
        );
        $this->assertEquals(1, $code, 'Check 1 exit code');
        $this->expectOutputRegex(
            '/Go success command: "ls ".*' .
            'success command "ls " end success.*' .
            'fail command "ls --ooo" end with errors.*' .
            'Async execution total: 0 ok and 1 bad/s'
        );
    }
}
