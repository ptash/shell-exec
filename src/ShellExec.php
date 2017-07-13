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
    public function exec($command, $withException = true)
    {
        $commandToExec = $command;
        if (false === strpos($command, ' 2>')) {
            $tmpStdErr = \tempnam(\sys_get_temp_dir(), 'shellExecStdErr');
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

    /**
     * Explode multiline string to array with no empty values.
     *
     * @param string $str Multiline string.
     *
     * @return array[string]
     * @since 0.0.1 introduced.
     */
    public function explodeLinesToArray($str)
    {
        $lines = explode("\n", $str);
        $lines = array_filter($lines, function ($line) {
            return !empty($line);
        });
        return $lines;
    }
}
