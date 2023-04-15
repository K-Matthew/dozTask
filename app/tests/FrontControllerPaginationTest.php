<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerPaginationTest extends WebTestCase
{
    public function testPagination(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $client->clickLink('Next');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Previous', $client->getResponse()->getContent());
    }
}
