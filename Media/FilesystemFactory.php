<?php

namespace Zenstruck\MediaBundle\Media;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zenstruck\MediaBundle\Media\Alert\AlertProviderInterface;
use Zenstruck\MediaBundle\Media\Alert\NullAlertProvider;
use Zenstruck\MediaBundle\Media\Filter\FilenameFilterInterface;
use Zenstruck\MediaBundle\Media\Permission\PermissionProviderInterface;
use Zenstruck\MediaBundle\Media\Permission\BooleanPermissionProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilesystemFactory
{
    const FILESYSTEM_MANAGER_CLASS  = 'Zenstruck\MediaBundle\Media\FilesystemManager';
    const FILESYSTEM_CLASS          = 'Zenstruck\MediaBundle\Media\Filesystem';

    protected $alerts;
    protected $permissions;
    protected $defaultLayout;
    protected $filenameFilters = array();
    protected $managers = array();

    public function __construct($defaultLayout, AlertProviderInterface $alerts = null, PermissionProviderInterface $permissions = null)
    {
        if (!$alerts) {
            $alerts = new NullAlertProvider();
        }

        if (!$permissions) {
            $permissions = new BooleanPermissionProvider();
        }

        $this->defaultLayout = $defaultLayout;
        $this->alerts = $alerts;
        $this->permissions = $permissions;
    }

    public function addFilenameFilter(FilenameFilterInterface $filter)
    {
        $this->filenameFilters[] = $filter;
    }

    public function addManager($name, array $config)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(array(
                'root_dir',
                'web_prefix'
            )
        );
        $resolver->setDefaults(array(
                'filesystem_manager_class' => static::FILESYSTEM_MANAGER_CLASS,
                'filesystem_class'=> static::FILESYSTEM_CLASS,
                'allowed_extensions' => null
            )
        );

        $this->managers[$name] = $resolver->resolve($config);
    }

    /**
     * @param Request $request
     *
     * @return FilesystemManager
     */
    public function getManager(Request $request)
    {
        $managers = $this->managers;
        $path = $request->query->get('path');
        $name = $request->query->get('filesystem');

        if (array_key_exists($name, $managers)) {
            $config = $this->managers[$name];
        } else {
            // return 1st by default
            $config = array_shift($managers);
            $names = array_keys($this->managers);
            $name = array_shift($names);
        }

        $filesystem = new $config['filesystem_class']($path, $config['root_dir'], $config['web_prefix']);

        /** @var FilesystemManager $manager */
        $manager = new $config['filesystem_manager_class']($name, $request->query->all(), $filesystem, $this->alerts, $this->permissions, $config['allowed_extensions']);

        foreach ($this->filenameFilters as $filter) {
            $manager->addFilenameFilter($filter);
        }

        return $manager;
    }

    public function getManagerNames()
    {
        return array_keys($this->managers);
    }

    public function getDefaultLayout()
    {
        return $this->defaultLayout;
    }
}