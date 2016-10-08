<?php

namespace Stayallive\LaravelAzureBlobQueue;

use Illuminate\Contracts\Queue\Queue as QueueInterface;
use Illuminate\Queue\Queue;
use MicrosoftAzure\Storage\Queue\Internal\IQueue;
use MicrosoftAzure\Storage\Queue\Models\CreateMessageOptions;
use MicrosoftAzure\Storage\Queue\Models\GetQueueMetadataResult;
use MicrosoftAzure\Storage\Queue\Models\ListMessagesOptions;
use MicrosoftAzure\Storage\Queue\Models\ListMessagesResult;
use MicrosoftAzure\Storage\Queue\QueueRestProxy;

class AzureQueue extends Queue implements QueueInterface
{

    /**
     * The Azure IServiceBus instance.
     *
     * @var QueueRestProxy
     */
    protected $azure;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;
    /**
     * @var
     */
    private $peekTimeout;

    /**
     * Create a new Azure IQueue queue instance.
     *
     * @param IQueue $azure
     * @param string $default
     * @param int $visibilityTimeout
     */
    public function __construct(IQueue $azure, $default, $visibilityTimeout)
    {
        $this->azure = $azure;
        $this->default = $default;
        $this->peekTimeout = $visibilityTimeout ? $visibilityTimeout : 5;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string $job
     * @param  mixed $data
     * @param  string $queue
     *
     * @return void
     */
    public function push($job, $data = '', $queue = null)
    {
        $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string $payload
     * @param  string $queue
     * @param  array $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->azure->createMessage($this->getQueue($queue), $payload);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  int $delay
     * @param  string $job
     * @param  mixed $data
     * @param  string $queue
     *
     * @return void
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $options = new CreateMessageOptions();
        $options->setVisibilityTimeoutInSeconds($delay);

        $this->azure->createMessage($this->getQueue($queue), $payload, $options);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string $queue
     *
     * @return \Illuminate\Queue\Jobs\Job|null
     */
    public function pop($queue = null)
    {
        // As recommended in the API docs, first call listMessages to hide message from other code
        $listMessagesOptions = new ListMessagesOptions();
        $listMessagesOptions->setVisibilityTimeoutInSeconds($this->peekTimeout);

        /** @var ListMessagesResult $listMessages */
        $listMessages = $this->azure->listMessages($this->getQueue($queue), $listMessagesOptions);
        $messages = $listMessages->getQueueMessages();

        if (count($messages) > 0) {
            return new AzureJob($this->container, $this->azure, $messages[0], $queue);
        }

        return null;
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null $queue
     *
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the underlying Azure IQueue instance.
     *
     * @return IQueue
     */
    public function getAzure()
    {
        return $this->azure;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string $queue
     * @return int
     */
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);

        /** @var GetQueueMetadataResult $metaData */
        $metaData = $this->azure->getQueueMetadata($queue);

        return $metaData->getApproximateMessageCount();
    }
}
