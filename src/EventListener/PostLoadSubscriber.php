<?php

namespace App\EventListener;

use App\Entity\AudioUpload;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Asset\Packages;

class PostLoadSubscriber implements EventSubscriber
{
    /**
     * @var Packages
     */
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof AudioUpload) {
            $url = $this->packages->getUrl('audio/' . $entity->getFilename());
            $entity->setAudioUrl($url);
        }
    }
}