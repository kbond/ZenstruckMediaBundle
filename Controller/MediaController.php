<?php

namespace Zenstruck\MediaBundle\Controller;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zenstruck\MediaBundle\Exception\DirectoryNotFoundException;
use Zenstruck\MediaBundle\Exception\Exception;
use Zenstruck\MediaBundle\Media\FilesystemFactory;
use Zenstruck\MediaBundle\Media\FilesystemManager;

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
            $manager = $this->factory->getManager($request);
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
                'manager' => $manager,
                'filesystems' => $this->factory->getManagerNames(),
                'default_layout' => $this->factory->getDefaultLayout(),
                'opener' => $opener,
                'opener_param' => $openerParam
            )));
    }

    public function getFilesAction(Request $request)
    {
        try {
            $manager = $this->factory->getManager($request);
        } catch (DirectoryNotFoundException $e) {
            return $this->getMessageResponse(sprintf('Directory "%s" not found.', $request->query->get('path')), 404);
        }

        $files = $manager->getFiles();
        $data = $this->serialize($files);

        return new Response($data);
    }

    public function uploadAction(Request $request)
    {
        $manager = $this->factory->getManager($request);
        $manager->uploadFile($request->files->get('file'));

        return $this->redirect($manager);
    }

    public function deleteFileAction($filename, Request $request)
    {
        $manager = $this->factory->getManager($request);
        $manager->deleteFile($filename);

        return $this->redirect($manager);
    }

    public function deleteDirAction($filename, Request $request)
    {
        $manager = $this->factory->getManager($request);
        $manager->deleteDir($filename);

        return $this->redirect($manager);
    }

    public function renameFileAction($filename, Request $request)
    {
        $manager = $this->factory->getManager($request);
        $manager->renameFile($filename, $request->request->get('new_name'));

        return $this->redirect($manager);
    }

    public function renameDirAction($filename, Request $request)
    {
        $manager = $this->factory->getManager($request);
        $manager->renameDir($filename, $request->request->get('new_name'));

        return $this->redirect($manager);
    }

    public function createDirectoryAction(Request $request)
    {
        try {
            $manager = $this->factory->getManager($request);
            $message = $manager->mkDir($request->query->get('dir_name'));
        } catch (Exception $e) {
            return $this->getMessageResponse($e->getMessage(), 400);
        }

        return $this->getMessageResponse($message);
    }

    protected function redirect(FilesystemManager $manager)
    {
        return new RedirectResponse(
            $this->router->generate('zenstruck_media_list', $manager->getRequestParams())
        );
    }

    protected function serialize($data, $format = 'json')
    {
        if (!$this->serializer) {
            $this->serializer = SerializerBuilder::create()->build();
        }

        return $this->serializer->serialize($data, $format);
    }

    protected function getMessageResponse($message, $statusCode = 201)
    {
        return new JsonResponse(array('message' => $message), $statusCode);
    }
}