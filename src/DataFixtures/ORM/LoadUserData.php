<?php
namespace App\DataFixtures\ORM;

use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {

        $userAdmin = new User();
        $encoder = $this->container->get('security.password_encoder');

        $userAdmin->setUsername('admin');
        $userAdmin->setPassword($encoder->encodePassword($userAdmin, 'admin'));

        $manager->persist($userAdmin);
        $manager->flush();
    }
}