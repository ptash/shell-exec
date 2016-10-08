<?php
/**
 * Util to exec shell command.
 *
 * @package ShellExec
 */

namespace ShellExec;

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
        $tmpStdErr = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shellExecStdErr';
        $commandToExec = $command . " 2>$tmpStdErr";
        exec($commandToExec, $output, $code);
        if (count($output) === 0) {
            $output = '';
        } else {
            $output = trim(implode(PHP_EOL, $output));
        }
        $stdErr = file_get_contents($tmpStdErr);
        unlink($tmpStdErr);
        if (0 !== $code && $withException) {
            throw new ShellExecException($command, $output, $stdErr, $code);
        }
        return $output;
    }
}
