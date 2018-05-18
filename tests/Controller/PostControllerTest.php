<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class PostControllerTest extends WebTestCase
{

    public function testLoginPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /* public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = self::createClient();

        // Go to the list view
        $crawler = $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /posts");

        // Go to the show view
        $crawler = $client->click($crawler->selectLink('show')->link());
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code");
    } */

}
