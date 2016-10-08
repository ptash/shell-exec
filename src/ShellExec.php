<?php
/**
 * Util to exec shell command.
 *
 * @package Cognitive\ShellExec
 */

namespace Cognitive\ShellExec;

/**
 * Class to exec shell command.
 */
class ShellExec
{
    /**
     * Execute shell command.
     *
     * @param string $command       Shell command.
     * @param bool   $withException If true then mustRun with throw exception.
     *
     * @return string Output.
     * @throws ShellExecException If the process didn't terminate successfully and param $withException is true.
     */
    public function exec($command, $withException = false)
    {
        $commandToExec = $command;
        if (false === strpos($command, ' 2>')) {
            $tmpStdErr = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shellExecStdErr';
            $commandToExec .= " 2>$tmpStdErr";
        }
        exec($commandToExec, $output, $code);
        if (count($output) === 0) {
            $output = '';
        } else {
            $output = trim(implode(PHP_EOL, $output));
        }
        if (!empty($tmpStdErr)) {
            $stdErr = file_get_contents($tmpStdErr);
            unlink($tmpStdErr);
        } else {
            $stdErr = '';
        }
        if (0 !== $code && $withException) {
            throw new ShellExecException($command, $output, $stdErr, $code);
        }
        return $output;
    }
}
