<?php
/**
 * Created by PhpStorm.
 * User: czar
 * Date: 08/09/2018
 * Time: 8:27 AM
 */

namespace App\Controller;


use App\Repository\AudioUploadRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    /**
     * @Route("/")
     *
     * @param Request $request
     * @return Response
     */
    public function show(Request $request, AudioUploadRepository $audioUploadRepository)
    {
        return $this->render('main/show.html.twig', [
            'audioUploads' => $audioUploadRepository->findBy(
                ['isDeleted' => 0],
                ['uploadDate' => 'desc']
            )
        ]);
    }
}