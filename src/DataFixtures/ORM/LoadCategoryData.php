<?php
namespace App\DataFixtures\ORM;

use App\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class LoadCategoryData extends AbstractFixture
{

    public function load(ObjectManager $manager)
    {

        $category = new Category();
        $category->setName('Test category 1');
        $category->setslug('category-1');
        $category->setPostCount(0);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Test category 2');
        $category->setslug('category-2');
        $category->setPostCount(0);
        $manager->persist($category);

        $category = new Category();
        $category->setName('Test category 3');
        $category->setslug('category-3');
        $category->setPostCount(0);
        $manager->persist($category);

        $manager->flush();
    }
}