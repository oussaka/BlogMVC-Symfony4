<?php
namespace App\EventListener;


use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CounterSubscriber implements EventSubscriber
{

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::preUpdate,
        ];
    }

    public function preUpdate(PreUpdateEventArgs $event) {
        if ($event->getObject() instanceof Post) {
            if ($event->hasChangedField('category')) {
                $event
                    ->getObjectManager()
                    ->getRepository(Category::class)
                    ->decrementCount($event->getOldValue('category'))
                    ->incrementCount($event->getNewValue('category'))
                ;
            }
        }
    }

    public function postRemove(LifecycleEventArgs $event) {
        if ($event->getObject() instanceof Post) {
            $event
                ->getObjectManager()
                ->getRepository(Category::class)
                ->decrementCount($event->getObject()->getCategory())
            ;
        }
    }

    public function postPersist(LifecycleEventArgs $event) {
        if ($event->getObject() instanceof Post) {
            $event
                ->getObjectManager()
                ->getRepository(Category::class)
                ->incrementCount($event->getObject()->getCategory())
            ;
        }
    }

}