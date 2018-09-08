<?php
/**
 * Created by PhpStorm.
 * User: czar
 * Date: 08/09/2018
 * Time: 9:44 AM
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 *
 * @Route("/api")
 */
class ApiController extends AbstractController
{

    /**
     * Initiate audio upload
     *
     * @Route("/audio/upload", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function audioUpload(Request $request)
    {
        // TODO Create database entry audio_uploads
        return new JsonResponse([
            'id' => '1'
        ]);
    }

    /**
     * Upload audio chunks
     *
     * @Route("/audio/chunk", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function audioChunk(Request $request)
    {
        // TODO Create database entry audio_chunks with order & filenames
        return new JsonResponse($request->request->all());
    }

    /**
     * Merge uploaded chunks
     *
     * @Route("/audio/merge", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function audioMerge(Request $request)
    {
        // TODO Merge files
        // TODO On success, upload to cloud storage
        // TODO Update audio_uploads status 0: uploading, 1: uploaded, 2: transcribed
        return new JsonResponse([
            'id' => '1',
            'location' => 'http://example.com/resource'
        ]);
    }
}
