<?php

namespace Zenstruck\MediaBundle\Controller;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as SymfonyAccessDeniedException;
use Zenstruck\MediaBundle\Exception\AccessDeniedException;
use Zenstruck\MediaBundle\Exception\DirectoryNotFoundException;
use Zenstruck\MediaBundle\Exception\Exception;
use Zenstruck\MediaBundle\Exception\FileNotFoundException;
use Zenstruck\MediaBundle\Media\FilesystemFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MediaController
{
    protected $defaultLayout;
    protected $factory;
    protected $templating;
    protected $router;
    protected $serializer;

    public function __construct(
        $defaultLayout,
        FilesystemFactory $factory,
        EngineInterface $templating,
        UrlGeneratorInterface $router,
        Serializer $serializer = null
    )
    {
        $this->defaultLayout = $defaultLayout;
        $this->factory = $factory;
        $this->templating = $templating;
        $this->router = $router;
        $this->serializer = $serializer;
    }

    public function indexAction(Request $request)
    {
        try {
            $filesystem = $this->factory->getFilesystem($request);
        } catch (DirectoryNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $opener = $request->query->get('opener');
        $openerParam = $request->query->get('opener_param');
        $lock = $request->query->has('lock');
        $layout = $request->query->get('layout');

        if ($layout) {
            $layout = sprintf('ZenstruckMediaBundle:Twitter:%s.html.twig', $layout);
        }

        if (!$opener && $param = $request->query->get('CKEditorFuncNum')) {
            $opener = 'ckeditor';
            $openerParam = $param;
        }

        return new Response($this->templating->render('ZenstruckMediaBundle:Twitter:index.html.twig', array(
                'filesystem' => $filesystem,
                'filesystems' => $this->factory->getFilesystemNames(),
                'default_layout' => $this->defaultLayout,
                'layout' => $layout,
                'opener' => $opener,
                'opener_param' => $openerParam,
                'lock' => $lock
            )));
    }

    public function getFilesAction(Request $request)
    {
        try {
            $filesystem = $this->factory->getFilesystem($request);
        } catch (DirectoryNotFoundException $e) {
            return $this->getMessageResponse(sprintf('Directory "%s" not found.', $request->query->get('path')), $e->getCode());
        }

        $files = $filesystem->getFiles();

        if (!$this->serializer) {
            $this->serializer = SerializerBuilder::create()->build();
        }

        return new Response($this->serializer->serialize($files, 'json'));
    }

    public function uploadAction(Request $request)
    {
        try {
            $filesystem = $this->factory->getFilesystem($request);
            $message = $filesystem->uploadFile($request->files->get('file'));
        } catch (Exception $e) {
            return $this->getMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->getMessageResponse($message, 201);
    }

    public function deleteAction(Request $request)
    {
        $filename = $request->query->get('filename');

        try {
            $filesystem = $this->factory->getFilesystem($request);
            $message = $filesystem->delete($filename);
        } catch (Exception $e) {
            return $this->getMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->getMessageResponse($message);
    }

    public function renameAction(Request $request)
    {
        $oldName = $request->query->get('old_name');
        $newName = $request->query->get('new_name');

        try {
            $filesystem = $this->factory->getFilesystem($request);
            $message = $filesystem->rename($oldName, $newName);

        } catch (Exception $e) {
            return $this->getMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->getMessageResponse($message, 200);
    }

    public function mkdirAction(Request $request)
    {
        try {
            $filesystem = $this->factory->getFilesystem($request);
            $message = $filesystem->mkdir($request->query->get('dir_name'));
        } catch (Exception $e) {
            return $this->getMessageResponse($e->getMessage(), $e->getCode());
        }

        return $this->getMessageResponse($message);
    }

    public function readAction(Request $request)
    {
        try {
            $filesystem = $this->factory->getFilesystem($request);
            $file = $filesystem->get($request->get('file'));
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (AccessDeniedException $e) {
            throw new SymfonyAccessDeniedException($e->getMessage());
        }

        return new BinaryFileResponse($file);
    }

    protected function getMessageResponse($message, $statusCode = 200)
    {
        return new Response(json_encode(array('message' => $message, 'code' => $statusCode)), $statusCode);
    }
}
