<?php
/**
 * Util to exec parallel shell command.
 *
 * @package Cognitive\ShellExec
 */

namespace Cognitive\ShellExec;

use Cognitive\ShellExec\Parallel\Process;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\Translator;

/**
 * Class to exec parallel shell command.
 */
class ShellExecParallel
{
    /** @var int Time to sleep during child processes wait (in microseconds) */
    const SLEEP_TIME_DURING_WAIT = 100000;

    /** @var TranslatorInterface */
    protected $translator;
    /** @var int */
    protected $parallelThreadCount = 4;
    /** @var string */
    protected $messageStart = 'Start command "%command%"';
    /** @var string */
    protected $messageFinishedWithErrors = 'Command "%command%" finished with errors. Exit code %exitCode%';
    /** @var string */
    protected $messageFinishedSuccess = 'Command "%command%" finished success';
    /** @var string */
    protected $messageTotal = 'Parallel execution total: %success% success and %fail% fail';
    /** @var string */
    protected $messageStop = 'Command "%command%" stop because execution time more then %execTime% seconds';
    /** @var int Process max execution time in seconds */
    protected $processMaxExecutionTime = 0;
    /**
     * ShellExecParallel constructor.
     *
     * @param TranslatorInterface $translator              Instance of TranslatorInterface.
     * @param int                 $processMaxExecutionTime Process max execution time in seconds. If 0 then no limit.
     *
     * @return void
     */
    public function __construct(TranslatorInterface $translator = null, $processMaxExecutionTime = null)
    {
        if (empty($translator)) {
            $this->translator = new Translator('en_EN');
        }
        if (null !== $processMaxExecutionTime) {
            $this->processMaxExecutionTime = $processMaxExecutionTime;
        }
    }

    /**
     * Set start message.
     *
     * @param string $messageStart Start message.
     *
     * @return void
     */
    public function setMessageStart($messageStart)
    {
        $this->messageStart = $messageStart;
    }

    /**
     * Set finish with success message.
     *
     * @param string $messageFinishedSuccess Message.
     *
     * @return void
     */
    public function setMessageFinishedSuccess($messageFinishedSuccess)
    {
        $this->messageFinishedSuccess = $messageFinishedSuccess;
    }

    /**
     * Set finish with errors message.
     *
     * @param string $messageFinishedWithErrors Message.
     *
     * @return void
     */
    public function setMessageFinishedWithErrors($messageFinishedWithErrors)
    {
        $this->messageFinishedWithErrors = $messageFinishedWithErrors;
    }

    /**
     * Set total message.
     *
     * @param string $messageTotal Text message.
     *
     * @return void
     */
    public function setMessageTotal($messageTotal)
    {
        $this->messageTotal = $messageTotal;
    }

    /**
     * Set stop message.
     *
     * @param string $messageStop Text message.
     *
     * @return void
     */
    public function setMessageStop($messageStop)
    {
        $this->messageStop = $messageStop;
    }

    /**
     * Set parallel thread count.
     *
     * @param int $parallelThreadCount Parallel thread count.
     *
     * @return void
     */
    public function setParallelThreadCount($parallelThreadCount)
    {
        $this->parallelThreadCount = $parallelThreadCount;
    }

    /**
     * Exec parallel list of commands.
     *
     * @param array[string] $commandList         Command list.
     * @param int|null      $parallelThreadCount Parallel thread count.
     *
     * @return int Exit code.
     */
    public function exec(array $commandList, $parallelThreadCount = null)
    {
        $processList = array();
        foreach ($commandList as $command) {
            $process = new Process($command);
            $processList[] = $process;
        }

        return $this->execProcessList($processList, $parallelThreadCount);
    }

    /**
     * Exec parallel list of commands by template and list of parameters.
     *
     * @param string   $commandTemplate     Command template.
     * @param array    $paramList           Array of parameters.
     * @param int|null $parallelThreadCount Parallel thread count.
     *
     * @return int Exit code.
     */
    public function execEach($commandTemplate, array $paramList, $parallelThreadCount = null)
    {
        $processList = array();
        foreach ($paramList as $params) {
            $command = $this->translator->trans($commandTemplate, $params);
            $process = new Process($command, $params);
            $processList[] = $process;
        }
        return $this->execProcessList($processList, $parallelThreadCount);
    }

    /**
     * Exec parallel list of Process.
     *
     * @param array[Process] $processList         Processes to exec.
     * @param int|null       $parallelThreadCount Parallel thread count.
     *
     * @return int
     */
    public function execProcessList(array $processList, $parallelThreadCount = null)
    {
        if (!empty($parallelThreadCount)) {
            $this->setParallelThreadCount($parallelThreadCount);
        }

        // Run process.
        $exitCode = 0;
        $success = 0;
        $fail = 0;
        $countRun = 0;
        do {
            /** @var Process $process */
            foreach ($processList as $process) {
                if ($countRun < $this->parallelThreadCount && !$process->isStarted()) {
                    $this->output($this->messageStart, $process->getParameters());
                    $process->start();
                    $countRun++;
                } elseif ($process->isStarted() && !$process->isFinished() && !$process->isRunning()) {
                    echo $process->getIncrementalOutput();

                    // In order to guarantee closing of pipes etc.
                    $process->wait();
                    echo $process->getIncrementalOutput();

                    if ($process->getExitCode() > 0) {
                        $exitCode = 1;
                        $fail++;
                        $this->output($this->messageFinishedWithErrors, $process->getParameters());
                    } else {
                        $success++;
                        $this->output($this->messageFinishedSuccess, $process->getParameters());
                    }
                    $countRun--;
                } elseif ($process->isStarted() && !$process->isFinished()) {
                    if (0 !== $this->processMaxExecutionTime &&
                        $process->getRunningTime() > $this->processMaxExecutionTime) {
                        $this->output(
                            $this->messageStop,
                            array_merge($process->getParameters(), ['%execTime%' => $this->processMaxExecutionTime])
                        );
                        $process->stop();
                    }
                    echo $process->getIncrementalOutput();
                }
            }

            if ($countRun > 0) {
                usleep(self::SLEEP_TIME_DURING_WAIT);
            }
        } while ($countRun);
        
        $this->output(
            $this->messageTotal,
            array_merge($process->getParameters(), array('%success%' => $success, '%fail%' => $fail))
        );
        return $exitCode;
    }

    /**
     * Output message.
     *
     * @param string $messageId  Message id.
     * @param array  $parameters Parameters.
     *
     * @return void
     */
    public function output($messageId, array $parameters)
    {
        echo $this->translator->trans($messageId, $parameters) . "\n";
    }
}
