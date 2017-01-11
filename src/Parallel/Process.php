<?php
/**
 * Info about parallel process and it execution.
 *
 * @package Cognitive\ShellExec\Parallel
 */

namespace Cognitive\ShellExec\Parallel;

use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * Class Process
 */
class Process
{
    /** @var SymfonyProcess */
    protected $process;
    /** @var array */
    protected $parameters;
    /** @var bool */
    protected $started = false;
    /** @var bool */
    protected $finished = false;

    /**
     * Process constructor.
     *
     * @param string $command    Command string.
     * @param array  $parameters Parameters.
     *
     * @return void
     */
    public function __construct($command, array $parameters = array())
    {
        $this->process = new SymfonyProcess($command);
        $this->parameters = array_merge(array('%command%' => $command), $parameters);
    }

    /**
     * Is process started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Is process finished.
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * Start process.
     *
     * @return void
     */
    public function start()
    {
        $this->process->start();
        $this->started = true;
    }

    /**
     * Is running or not.
     *
     * @return bool
     */
    public function isRunning()
    {
        $isRunning = false;
        if ($this->started && !$this->finished) {
            $isRunning = $this->process->isRunning();
            if (!$isRunning) {
                $this->finished = true;
            }
        }
        return $isRunning;
    }

    /**
     * Get exit code.
     *
     * @return int|null
     */
    public function getExitCode()
    {
        return $this->process->getExitCode();
    }

    /**
     * Get new output after previous call getIncrementalOutput.
     *
     * @return string
     */
    public function getIncrementalOutput()
    {
        return $this->process->getIncrementalOutput();
    }

    /**
     * Get parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Waits for the process to terminate.
     *
     * The callback receives the type of output (out or err) and some bytes
     * from the output in real-time while writing the standard input to the process.
     * It allows to have feedback from the independent process during execution.
     *
     * @param callable|null $callback A valid PHP callback.
     *
     * @return int The exitcode of the process.
     */
    public function wait($callback = null)
    {
        return $this->process->wait($callback);
    }
}
