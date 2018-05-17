<?php
namespace App\EventListener;


use App\Entity\Post;
use App\Entity\Category;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

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
            'preUpdate',
            'postPersist',
            'postRemove'
        ];
    }

    public function preUpdate(PreUpdateEventArgs $event) {
        if ($event->getObject() instanceof Post) {
            if ($event->hasChangedField('category')) {
                $event
                    ->getEntityManager()
                    ->getRepository(Category::class)
                    ->decrementCount($event->getOldValue('category'))
                    ->incrementCount($event->getNewValue('category'));
            }
        }
    }

    public function postRemove(LifecycleEventArgs $event) {
        if ($event->getObject() instanceof Post) {
            $event
                ->getEntityManager()
                ->getRepository(Category::class)
                ->decrementCount($event->getEntity()->getCategory());
        }
    }

    public function postPersist(LifecycleEventArgs $event) {
        if ($event->getObject() instanceof Post) {
            $event
                ->getEntityManager()
                ->getRepository(Category::class)
                ->incrementCount($event->getEntity()->getCategory());
        }
    }

}