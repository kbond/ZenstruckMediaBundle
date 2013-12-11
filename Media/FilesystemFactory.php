<?php

namespace Zenstruck\MediaBundle\Media;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zenstruck\MediaBundle\Media\Filter\FilenameFilterInterface;
use Zenstruck\MediaBundle\Media\Permission\PermissionProviderInterface;
use Zenstruck\MediaBundle\Media\Permission\BooleanPermissionProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilesystemFactory
{
    protected $permissions;
    protected $filesystemClass;
    protected $router;
    protected $responseFactory;
    protected $filenameFilters = array();
    protected $filesystems = array();

    public function __construct(
        UrlGeneratorInterface $router,
        $filesystemClass = 'Zenstruck\MediaBundle\Media\Filesystem',
        PermissionProviderInterface $permissions = null
    )
    {
        if (!$permissions) {
            $permissions = new BooleanPermissionProvider();
        }

        $this->filesystemClass = $filesystemClass;
        $this->permissions = $permissions;
        $this->router = $router;
    }

    public function addFilenameFilter(FilenameFilterInterface $filter)
    {
        $this->filenameFilters[] = $filter;
    }

    public function addFilesystem($name, array $config)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(array(
                'root_dir',
                'web_prefix'
            )
        );
        $resolver->setDefaults(array(
                'allowed_extensions' => null,
                'secure' => false
            )
        );

        $this->filesystems[$name] = $resolver->resolve($config);
    }

    /**
     * @param Request $request
     *
     * @return Filesystem
     */
    public function getFilesystem(Request $request)
    {
        $managers = $this->filesystems;
        $path = $request->query->get('path');
        $name = $request->query->get('filesystem');
        $securePath = null;

        if (array_key_exists($name, $managers)) {
            $config = $this->filesystems[$name];
        } else {
            // return 1st by default
            $config = array_shift($managers);
            $names = array_keys($this->filesystems);
            $name = array_shift($names);
        }

        if ($config['secure']) {
            $securePath = $this->router->generate('zenstruck_media_read', array(
                    'filesystem' => $name
                )
            );
        }

        /** @var Filesystem $filesystem */
        $filesystem = new $this->filesystemClass(
            $name,
            $path,
            $config['root_dir'],
            $config['web_prefix'],
            $this->permissions,
            $config['allowed_extensions'],
            $securePath
        );

        foreach ($this->filenameFilters as $filter) {
            $filesystem->addFilenameFilter($filter);
        }

        return $filesystem;
    }

    public function getFilesystemNames()
    {
        return array_keys($this->filesystems);
    }
}
