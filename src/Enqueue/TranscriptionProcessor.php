<?php

namespace App\Enqueue;


use App\Repository\AudioUploadRepository;
use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProcessor;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TranscriptionProcessor implements PsrProcessor, TopicSubscriberInterface
{
    private $audioUploadRepository;
    private $projectDir;

    /**
     * TranscriptionProcessor constructor.
     * @param AudioUploadRepository $audioUploadRepository
     * @param string $projectDir
     */
    public function __construct(AudioUploadRepository $audioUploadRepository, string $projectDir)
    {
        $this->audioUploadRepository = $audioUploadRepository;
        $this->projectDir = $projectDir;
    }

    /**
     * @param PsrMessage $message
     * @param PsrContext $session
     * @return object|string
     */
    public function process(PsrMessage $message, PsrContext $session)
    {
        $data = json_decode($message->getBody(), true);

        $audioUploadId = $data['id'] ?? 0;
        $audioUpload = $this->audioUploadRepository->find($audioUploadId);

        if (!$audioUpload) {
            return self::REJECT;
        }

        $command = sprintf('bin/console app:create-transcript %s', $audioUploadId);
        $process = new Process($command, $this->projectDir);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return self::ACK;
    }

    /**
     * @return array
     */
    public static function getSubscribedTopics()
    {
        return ['transcribe'];
    }
}