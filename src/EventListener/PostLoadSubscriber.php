<?php

namespace App\EventListener;

use App\Entity\AudioUpload;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Asset\Packages;

class PostLoadSubscriber implements EventSubscriber
{
    private $package;

    /**
     * PostLoadSubscriber constructor.
     * @param Packages $package
     */
    public function __construct(Packages $package)
    {
        $this->package = $package;
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
                    $this->package->getUrl("audio/{$entity->getFilename()}")
                );
            }

            if ($entity->getTranscriptionFilename()) {
                $entity->setTranscriptionUrl(
                    $this->package->getUrl("trans/{$entity->getTranscriptionFilename()}")
                );
            }
        }
    }
}