<?php

namespace App\Command;


use App\Entity\AudioUpload;
use App\Repository\AudioUploadRepository;
use App\Security\Random\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Speech\Result;
use Google\Cloud\Speech\SpeechClient;
use Google\Cloud\Storage\Bucket;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CreateTranscriptCommand extends Command
{
    /**
     * @var AudioUploadRepository
     */
    private $audioUploadRepository;

    /**
     * @var Bucket
     */
    private $googleCloudStorageBucket;

    /**
     * @var SpeechClient
     */
    private $googleCloudSpeechClient;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $tmpAudioDir;

    /**
     * @var string
     */
    private $tmpTransDir;

    /**
     * @var string
     */
    private $webTransDir;

    /**
     * @var RandomStringGenerator
     */
    private $randomStringGenerator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * CreateTranscriptCommand constructor.
     * @param AudioUploadRepository $audioUploadRepository
     * @param Bucket $googleCloudStorageBucket
     * @param SpeechClient $googleCloudSpeechClient
     * @param EntityManagerInterface $em
     * @param string $tmpAudioDir
     * @param string $tmpTransDir
     * @param string $webTransDir
     * @param RandomStringGenerator $randomStringGenerator
     * @param Filesystem $filesystem
     */
    public function __construct(
        AudioUploadRepository $audioUploadRepository,
        Bucket $googleCloudStorageBucket,
        SpeechClient $googleCloudSpeechClient,
        EntityManagerInterface $em,
        string $tmpAudioDir,
        string $tmpTransDir,
        string $webTransDir,
        RandomStringGenerator $randomStringGenerator,
        Filesystem $filesystem
    ) {
        parent::__construct();

        $this->audioUploadRepository = $audioUploadRepository;
        $this->googleCloudStorageBucket = $googleCloudStorageBucket;
        $this->googleCloudSpeechClient = $googleCloudSpeechClient;
        $this->em = $em;
        $this->tmpAudioDir = $tmpAudioDir;
        $this->tmpTransDir = $tmpTransDir;
        $this->webTransDir = $webTransDir;
        $this->randomStringGenerator = $randomStringGenerator;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('app:create-transcript');
        $this->setDescription('Create a text transcript from an audio file');
        $this->addArgument('audioUploadId', InputArgument::REQUIRED, 'The audio to transcribe');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $audioUpload = $this->audioUploadRepository->find($input->getArgument('audioUploadId'));

        if (!$audioUpload) {
            $output->writeln('Invalid audio upload to transcribe');

            return 1;
        }

        $object = $this->googleCloudStorageBucket->object($audioUpload->getFilename());

        if (!$object->exists()) {
            $output->writeln('Audio file to transcribe is missing');

            return 1;
        }

        // Temporary files container
        $tmpFiles = [];

        $tmpFiles['audio'] = $this->tmpAudioDir . '/local-' . $audioUpload->getFilename();
        $object->downloadToFile($tmpFiles['audio']);

        $tmpFiles['flac'] = $this->tmpAudioDir . '/' . $this->generateRandomFileName(20, 'flac');
        $converter = new Process(
            sprintf('ffmpeg -i %s -c:a flac %s', $tmpFiles['audio'], $tmpFiles['flac'])
        );

        $converter->run();

        if (!$converter->isSuccessful()) {
            throw new ProcessFailedException($converter);
        }

        $flacObject = $this->googleCloudStorageBucket->upload(fopen($tmpFiles['flac'], 'r'));
        $operation = $this->googleCloudSpeechClient->beginRecognizeOperation($flacObject);

        // Wait for the operation to complete
        $backoff = new ExponentialBackoff(10);
        $backoff->execute(function () use ($operation) {
            print('Waiting for operation to complete' . PHP_EOL);
            $operation->reload();
            if (!$operation->isComplete()) {
                throw new Exception('Job has not yet completed', 500);
            }
        });

        if (!$operation->isComplete()) {
            throw new Exception('Audio could not be transcribed');
        }

        /** @var Result[] $results */
        $results = $operation->results();
        $textResults = [];

        foreach ($results as $result) {
            $textInfo = $result->alternatives()[0] ?? [];
            $textResults[] = $textInfo['transcript'];
        }

        $transFilename = $this->generateRandomFileName(20, 'txt');
        $transPath = $this->tmpTransDir . '/' . $transFilename;
        $this->filesystem->dumpFile($transPath, implode("\n", $textResults));

        // Upload to Google storage
        $this->googleCloudStorageBucket->upload(fopen($transPath, 'r'));

        // Move to public dir
        $this->filesystem->copy($transPath, $this->webTransDir . '/' . $transFilename);
        $this->filesystem->remove($transPath);

        $audioUpload->setStatus(AudioUpload::UPLOAD_STATUS_TRANSCRIBED);
        $audioUpload->setTranscriptionFilename($transFilename);
        $this->em->flush();

        // Cleanup
        $this->filesystem->remove($tmpFiles);
    }

    private function generateRandomFileName($length, $extension)
    {
        return implode(
            '.',
            [
                implode(
                    '',
                    [
                    $this->randomStringGenerator->generate($length * 0.7, 'abcdefghijklmnopqrstuvwxyz'),
                    $this->randomStringGenerator->generate($length * 0.3, '0123456789')
                    ]
                ),
                $extension
            ]
        );
    }
}