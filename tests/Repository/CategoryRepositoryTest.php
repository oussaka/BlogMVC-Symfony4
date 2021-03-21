<?php

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryRepositoryTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $this->createSchema();
        parent::setUp();
    }

    protected function createCategory($i = 1): Category
    {
        $category = new Category();
        $category->setName("Category $i");
        $category->setSlug("category-$i");

        return $category;
    }

    protected function createPost($i = 1): Post
    {
        $post = new Post();
        $post->setName("Post $i");
        $post->setSlug("post-$i");
        $post->setContent("some fake content");

        return $post;
    }

    public function testCategoryPostCountOnCreate()
    {
        $category  = $this->createCategory();
        $category2 = $this->createCategory(2);
        $this->entityManager->persist($category);
        $this->entityManager->persist($category2);
        $this->entityManager->flush();
        $this->entityManager->refresh($category);
        $this->assertEquals(0, $category->getPostCount());

        $post = $this->createPost();
        $post->setCategory($category);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->entityManager->refresh($category);
        $this->assertEquals(1, $category->getPostCount());
    }

    public function testCategoryPostCountOnDelete()
    {
        $category = $this->createCategory();
        $this->entityManager->persist($category);
        $post = $this->createPost();
        $post->setCategory($category);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->entityManager->refresh($category);
        $this->assertEquals(1, $category->getPostCount());

        // We delete the post
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        $this->entityManager->refresh($category);
        $this->assertEquals(0, $category->getPostCount());
    }

    public function testCategoryPostCountOnUpdate()
    {
        $category  = $this->createCategory();
        $category2 = $this->createCategory(2);
        $this->entityManager->persist($category);
        $this->entityManager->persist($category2);
        $post = $this->createPost();
        $post->setCategory($category);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->entityManager->refresh($category);
        $this->entityManager->refresh($category2);
        // We test the starting point
        $this->assertEquals(1, $category->getPostCount());
        $this->assertEquals(0, $category2->getPostCount());

        // We update the post
        $post->setCategory($category2);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->entityManager->refresh($category);
        $this->entityManager->refresh($category2);
        $this->assertEquals(0, $category->getPostCount());
        $this->assertEquals(1, $category2->getPostCount());
    }

    public function sssstestCompleteScenario()
    {
        // Create a new client to browse the application
        $client = self::createClient();


        $category  = $this->createCategory();
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $this->entityManager->refresh($category);
        $this->assertEquals(0, $category->getPostCount());

        $user = new User();
        $user->setUsername("usertest");
        $user->setPassword(uniqid());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->refresh($user);

        $post = $this->createPost();
        $post->setCategory($category);
        $post->setUser($user);

        $this->entityManager->persist($post);
        $this->entityManager->flush();



        // Go to the list view
        $url = "/";
        $crawler = $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /posts");

        // Go to the show view
        $crawler = $client->click($crawler->selectLink('Read more...')->link());
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // $this->dropSchema();
        $this->entityManager->close();
        $this->entityManager = null; // Avoid memory leaks
    }

    protected function createSchema()
    {
        // Get the metadata of the application to create the schema.
        $metadata = $this->getMetadata();

        if (!empty($metadata)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->entityManager);
            $tool->createSchema($metadata);
        } else {
            throw new SchemaException('No Metadata Classes to process.');
        }
    }

    protected function dropSchema()
    {
        // Get the metadata of the application to create the schema.
        $metadata = $this->getMetadata();

        if (!empty($metadata)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->entityManager);
            $tool->dropSchema($metadata);
        } else {
            throw new SchemaException('No Metadata Classes to process.');
        }
    }

    protected function getMetadata()
    {
        return $this->entityManager->getMetadataFactory()->getAllMetadata();
    }
}
