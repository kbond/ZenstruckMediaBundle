<?php

namespace Zenstruck\MediaBundle\Tests\Form;

use Symfony\Component\Form\FormView;
use Zenstruck\MediaBundle\Form\MediaType;
use Zenstruck\MediaBundle\Tests\Media\BaseFilesystemTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MediaTypeTest extends BaseFilesystemTest
{
    public function testDefaults()
    {
        $client = parent::createClient();

        /** @var $formView FormView */
        $formView = $client->getContainer()->get('form.factory')->create(
            new MediaType($client->getContainer()->get('router'))
        )->createView();

        $this->assertTrue($formView instanceof FormView);
        $this->assertEquals('/media/?opener=media-widget&layout=popup', $formView->vars['media_url']);
    }

    public function testCustomFilesystem()
    {
        $client = parent::createClient();

        /** @var $formView FormView */
        $formView = $client->getContainer()->get('form.factory')->create(
            new MediaType($client->getContainer()->get('router')), null,
            array(
                'filesystem' => 'foo'
            )
        )->createView();

        $this->assertEquals('/media/?opener=media-widget&filesystem=foo&layout=popup', $formView->vars['media_url']);
    }

    public function testCustomUrl()
    {
        $client = parent::createClient();

        /** @var $formView FormView */
        $formView = $client->getContainer()->get('form.factory')->create(
            new MediaType($client->getContainer()->get('router')), null,
            array(
                'media_url' => '/foo'
            )
        )->createView();

        $this->assertEquals('/foo', $formView->vars['media_url']);
    }
}
