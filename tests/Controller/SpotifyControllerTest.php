<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use App\Controller\SpotifyController;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SpotifyControllerTest extends TestCase
{
    private $spotifyApiMock;
    private SpotifyWebAPI\Session $sessionMock;
    private $cacheMock;
    private $spotifyController;

    protected function setUp(): void
    {
        $this->spotifyApiMock = $this->createMock(SpotifyWebAPI::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->cacheMock = $this->createMock(CacheItemPoolInterface::class);

        $this->spotifyController = new SpotifyController($this->spotifyApiMock, $this->sessionMock, $this->cacheMock);
    }

    public function testIndexRedirectsToLoginIfNoAccessToken()
    {
        // Setup mock to return false for hasItem
        $this->cacheMock->method('hasItem')->willReturn(false);

        // Call the index method
        $response = $this->spotifyController->index();

        // Assert that the response is a redirect to the login route
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/redirect', $response->getTargetUrl());
    }

    // Additional tests would follow a similar structure, mocking dependencies and asserting outcomes based on the method being tested.

    protected function tearDown(): void
    {
        unset($this->spotifyApiMock);
        unset($this->sessionMock);
        unset($this->cacheMock);
        unset($this->spotifyController);
    }
}
