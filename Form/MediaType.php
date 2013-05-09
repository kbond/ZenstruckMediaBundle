<?php

namespace Zenstruck\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MediaType extends AbstractType
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $url = $options['media_url'];

        if (!$url) {
            try {
                $url = $this->router->generate('zenstruck_media_list', array(
                        'layout' => 'iframe',
                        'filesystem' => $options['filesystem']
                    ));
            } catch (RouteNotFoundException $e) {
                throw new \Exception('The route "zenstruck_media_list" is not enabled.');
            }
        }

        $view->vars['media_url'] = $url;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'media_url' => null,
                'filesystem' => null
            ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'zenstruck_media';
    }
}