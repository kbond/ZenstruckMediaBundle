<?php

namespace Zenstruck\MediaBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Zenstruck\MediaBundle\Tests\Media\BaseFilesystemTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MediaControllerTest extends BaseFilesystemTest
{
    public function testIndexAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/media/');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertCount(1, $crawler->filter('#zenstruck-media'));
    }

    public function testGetFilesAction()
    {
        $client = static::createClient();

        $client->request('GET', '/media/files');

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertCount(6, $content);
    }

    public function testMkdirAction()
    {
        $this->assertFileNotExists($this->getTempFixtureDir().'Foo');

        $client = static::createClient();
        $client->request('POST', '/media/mkdir?dir_name=Foo');

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals('Directory "Foo" created.', $content['message']);
        $this->assertFileExists($this->getTempFixtureDir().'Foo');
    }

    public function testMkdirErrorAction()
    {
        $client = static::createClient();

        $client->request('POST', '/media/mkdir');

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertTrue($response->isClientError());
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals('You didn\'t enter a directory name.', $content['message']);
    }

    public function testRenameAction()
    {
        $this->assertFileNotExists($this->getTempFixtureDir().'foo.txt');

        $client = static::createClient();
        $client->request('PUT', '/media/rename?old_name=dolor.txt&new_name=foo.txt');

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals('File "dolor.txt" renamed to "foo.txt".', $content['message']);
        $this->assertFileExists($this->getTempFixtureDir().'foo.txt');
    }

    public function testRenameErrorAction()
    {
        $client = static::createClient();
        $client->request('PUT', '/media/rename');

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertTrue($response->isClientError());
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals('You didn\'t specify an old file/directory name.', $content['message']);
    }

    public function testDeleteAction()
    {
        $this->assertFileExists($this->getTempFixtureDir().'dolor.txt');

        $client = static::createClient();
        $client->request('DELETE', '/media/delete?filename=dolor.txt');

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals('File "dolor.txt" deleted.', $content['message']);
        $this->assertFileNotExists($this->getTempFixtureDir().'dolor.txt');
    }

    public function testDeleteErrorAction()
    {
        $client = static::createClient();
        $client->request('DELETE', '/media/delete');

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertTrue($response->isClientError());
        $this->assertTrue(
            $response->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals('No file/directory specified.', $content['message']);
    }
}
