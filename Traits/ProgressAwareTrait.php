<?php

namespace Siso\Bundle\ContentLoaderBundle\Traits;
/**
 * Trait for progress aware objects
 */
trait ProgressAwareTrait
{
    /**
     * @var \Closure
     */
    private $progressCallback;

    /**
     * Sets a callback function to be called on progress.
     * A callback has to be a function with 1 string parameter.
     *
     * Example:
     * $myProgressObject->setProgressCallback(
     *     function($message) use ($output) {
     *       $output->writeln($message);
     *     }
     * );
     *
     * @param callable $progressCallback
     */
    public function setProgressCallback(\Closure $progressCallback)
    {
        $this->progressCallback = $progressCallback;
    }

    /**
     * Runs callback function on progress.
     * Classes that use this trait should it call this method.
     *
     * Example:
     * $this->doProgress('Loading test content types data...');
     *
     * @param string $message
     * @param array $messageParameters
     */
    protected function doProgress($message, $messageParameters = [])
    {
        if(!is_null($this->progressCallback)) {
            $outputMessage = vsprintf($message, $messageParameters);
            $callback = $this->progressCallback;
            $callback($outputMessage);
        }
    }
}