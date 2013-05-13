<?php

namespace Zenstruck\MediaBundle\Controller;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zenstruck\MediaBundle\Exception\DirectoryNotFoundException;
use Zenstruck\MediaBundle\Exception\Exception;
use Zenstruck\MediaBundle\Media\FilesystemFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MediaController
{
    const ALERT_ERROR   = 'error';
    const ALERT_SUCCESS = 'success';

    protected $factory;
    protected $templating;
    protected $router;
    protected $serializer;

    public function __construct(
        FilesystemFactory $factory,
        EngineInterface $templating,
        UrlGeneratorInterface $router,
        Serializer $serializer = null
    )
    {
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

        if (!$opener && $param = $request->query->get('CKEditorFuncNum')) {
            $opener = 'ckeditor';
            $openerParam = $param;
        }

        return new Response($this->templating->render('ZenstruckMediaBundle:Twitter:index.html.twig', array(
                'filesystem' => $filesystem,
                'filesystems' => $this->factory->getFilesystemNames(),
                'default_layout' => $this->factory->getDefaultLayout(),
                'opener' => $opener,
                'opener_param' => $openerParam
            )));
    }

    public function getFilesAction(Request $request)
    {
        try {
            $filesystem = $this->factory->getFilesystem($request);
        } catch (DirectoryNotFoundException $e) {
            return $this->getMessageResponse(sprintf('Directory "%s" not found.', $request->query->get('path')), 404);
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
            return $this->getMessageResponse($e->getMessage(), 400);
        }

        return $this->getMessageResponse($message);
    }

    public function deleteAction(Request $request)
    {
        $filename = $request->query->get('filename');

        try {
            $filesystem = $this->factory->getFilesystem($request);
            $message = $filesystem->deleteFile($filename);
        } catch(Exception $e) {
            return $this->getMessageResponse($e->getMessage(), 400);
        }

        return $this->getMessageResponse($message);
    }

    public function renameAction(Request $request)
    {
        $oldName = $request->query->get('old_name');
        $newName = $request->query->get('new_name');

        try {
            $filesystem = $this->factory->getFilesystem($request);
            $message = $filesystem->renameFile($oldName, $newName);

        } catch (Exception $e) {
            return $this->getMessageResponse($e->getMessage(), 400);
        }

        return $this->getMessageResponse($message);
    }

    public function createDirectoryAction(Request $request)
    {
        try {
            $filesystem = $this->factory->getFilesystem($request);
            $message = $filesystem->mkDir($request->query->get('dir_name'));
        } catch (Exception $e) {
            return $this->getMessageResponse($e->getMessage(), 400);
        }

        return $this->getMessageResponse($message);
    }

    protected function getMessageResponse($message, $statusCode = 201)
    {
        return new JsonResponse(array('message' => $message), $statusCode);
    }
}