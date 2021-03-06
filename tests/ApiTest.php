<?php
namespace Instagra\tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use Instagram\Api;
use Instagram\Exception\InstagramException;
use Instagram\Hydrator\Feed;
use Instagram\Hydrator\Media;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $validClient;

    /**
     * @var Client
     */
    private $invalidClient;

    public function setUp()
    {
        $validFixtures = file_get_contents(__DIR__ . '/fixtures/instagram_feed.json');

        $response          = new Response(200, [], $validFixtures);
        $mock              = new MockHandler([$response]);
        $handler           = HandlerStack::create($mock);
        $this->validClient = new Client(['handler' => $handler]);

        $invalidFixtures = '';

        $response            = new Response(200, [], $invalidFixtures);
        $mock                = new MockHandler([$response]);
        $handler             = HandlerStack::create($mock);
        $this->invalidClient = new Client(['handler' => $handler]);
    }

    public function testValidFeedReturn()
    {
        $api = new Api($this->validClient);
        $api->setUserName('pgrimaud');
        $feed = $api->getFeed();

        $this->assertInstanceOf(Feed::class, $feed);
    }

    public function testValidEmptyFeedReturn()
    {
        $this->expectException(InstagramException::class);

        $api = new Api($this->invalidClient);
        $api->setUserName('pgrimaud');
        $api->getFeed();
    }

    public function testValidFeedWithMaxIdReturn()
    {
        $api = new Api($this->validClient);
        $api->setUserName('pgrimaud');
        $api->setMaxId(1);
        $feed = $api->getFeed();

        $this->assertInstanceOf(Feed::class, $feed);
    }

    public function testValidFeedWithoutUserNameReturn()
    {
        $this->expectException(InstagramException::class);

        $api = new Api($this->validClient);
        $api->getFeed();
    }

    public function testFeedContent()
    {
        $api = new Api($this->validClient);
        $api->setUserName('pgrimaud');

        /** @var Feed $feed */
        $feed = $api->getFeed();

        $this->assertInstanceOf(Feed::class, $feed);

        // test feed
        $this->assertInstanceOf(Feed::class, $feed);

        $this->assertSame('184263228', $feed->getId());
        $this->assertSame('pgrimaud', $feed->getUserName());
        $this->assertSame('Gladiator retired - ESGI 14\'', $feed->getBiography());
        $this->assertSame('Pierre G', $feed->getFullName());

        $this->assertSame(true, $feed->getHasNextPage());
        $this->assertSame(false, $feed->getisVerified());

        $this->assertSame('https://scontent-cdg2-1.cdninstagram.com/t51.2885-19/10483606_1498368640396196_604136733_a.jpg', $feed->getProfilePicture());
        $this->assertSame('https://scontent-cdg2-1.cdninstagram.com/t51.2885-19/10483606_1498368640396196_604136733_a.jpg', $feed->getProfilePictureHd());

        $this->assertSame(336, $feed->getFollowers());
        $this->assertSame(110, $feed->getFollowing());

        $this->assertSame('https://p.ier.re/', $feed->getExternalUrl());
        $this->assertSame(30, $feed->getMediaCount());

        $this->assertCount(12, $feed->getMedias());
    }

    public function testMediaContent()
    {
        $api = new Api($this->validClient);
        $api->setUserName('pgrimaud');

        /** @var Feed $feed */
        $feed = $api->getFeed();

        $this->assertInstanceOf(Feed::class, $feed);

        /** @var Media $media */
        $media = $feed->getMedias()[0];

        $this->assertInstanceOf(Media::class, $media);

        $this->assertSame('1676900800864278214', $media->getId());
        $this->assertSame('GraphImage', $media->getTypeName());

        $this->assertSame(1080, $media->getWidth());
        $this->assertSame(1080, $media->getHeight());

        $this->assertSame('https://scontent-cdg2-1.cdninstagram.com/t51.2885-15/s640x640/sh0.08/e35/25024600_726096737595175_9198105573181095936_n.jpg', $media->getThumbnailSrc());
        $this->assertSame('https://scontent-cdg2-1.cdninstagram.com/t51.2885-15/e35/25024600_726096737595175_9198105573181095936_n.jpg', $media->getDisplaySrc());
        $this->assertSame(1080, $media->getHeight());

        $this->assertCount(5, $media->getThumbnailResources());

        $this->assertSame('BdFjGTPFVbG', $media->getCode());
        $this->assertSame('https://www.instagram.com/p/BdFjGTPFVbG/', $media->getLink());

        $this->assertInstanceOf(\DateTime::class, $media->getDate());

        $this->assertSame('🎄🎅💸🙃 #casino #monaco', $media->getCaption());

        $this->assertSame(0, $media->getComments());
        $this->assertSame(28, $media->getLikes());
    }
}
