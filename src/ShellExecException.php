<?php
/**
 * Exeption for exec.
 * @package Cognitive\ShellExec
 */

namespace Cognitive\ShellExec;

/**
 * Class ExecException
 */
class ShellExecException extends \RuntimeException
{
    const CODE_COMMAND_NOT_FOUND = 127;

    /**
     * ExecException constructor.
     *
     * @param string $command    Command line.
     * @param string $output     Output.
     * @param string $stdErr     Error output.
     * @param int    $returnCode Return code.
     *
     * @return void
     */
    public function __construct($command, $output, $stdErr, $returnCode)
    {
        if (self::CODE_COMMAND_NOT_FOUND == $returnCode) {
            $message = 'Command not found: "' . $command . '"';
        } else {
            $message = 'Command "' . $command . '" exited with code ' . $returnCode;
            if (!empty($output)) {
                $message .= ': ' . $output;
            }
            if (!empty($stdErr)) {
                $message .= PHP_EOL . 'Error text: ' . $stdErr;
            }
        }
        parent::__construct($message);
    }
}
