<?php

namespace App\EventListener;

use App\Entity\AudioUpload;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Asset\Packages;

class PostLoadSubscriber implements EventSubscriber
{
    private $webAudioDir;

    private $webTransDir;

    /**
     * PostLoadSubscriber constructor.
     * @param string $webAudioDir
     * @param string $webTransDir
     */
    public function __construct(string $webAudioDir, string $webTransDir)
    {
        $this->webAudioDir = $webAudioDir;
        $this->webTransDir = $webTransDir;
    }

    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof AudioUpload) {
            if ($entity->getFilename()) {
                $entity->setAudioUrl(
                    $this->webAudioDir . '/' . $entity->getFilename()
                );
            }

            if ($entity->getTranscriptionFilename()) {
                $entity->setTranscriptionUrl(
                    $this->webTransDir . '/' . $entity->getTranscriptionFilename()
                );
            }
        }
    }
}