<?php

namespace App\Controller;

use App\AudioUpload\ChunkMerger;
use App\Entity\AudioUpload;
use App\Entity\AudioUploadChunk;
use App\Form\Type\AudioUploadChunkInputType;
use App\Form\Type\AudioUploadInputType;
use App\Repository\AudioUploadRepository;
use App\Security\Random\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Google\Cloud\Storage\Bucket;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */
    public function audioUpload(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ) {
        $audioUpload = new AudioUpload();
        $form = $this->createForm(AudioUploadInputType::class, $audioUpload);
        $form->handleRequest($request);

        $errors = $validator->validate($audioUpload);
        if (0 < $errors->count()) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $em->persist($audioUpload);
        $em->flush();

        return new JsonResponse([
            'id' => $audioUpload->getId()
        ], 201, [
            'Location' => $this->generateUrl('api_get_audio', ['id' => $audioUpload->getId()])
        ]);
    }

    /**
     * Upload audio chunks
     *
     * @Route("/audio/chunk", methods={"POST"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param RandomStringGenerator $randomStringGenerator
     * @param Filesystem $filesystem
     * @return JsonResponse
     */
    public function audioChunk(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        RandomStringGenerator $randomStringGenerator,
        Filesystem $filesystem
    ) {
        $audioUploadChunk = new AudioUploadChunk();
        $form = $this->createForm(AudioUploadChunkInputType::class, $audioUploadChunk, ['method' => 'POST']);
        $form->handleRequest($request);

        $errors = $validator->validate($audioUploadChunk);
        if (0 < $errors->count()) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $audioUploadChunk->setFilename($randomStringGenerator->generate(
            $this->getParameter('security.random.default_string_length')
        ));

        $filesystem->dumpFile(
            $this->getParameter('filesystem.tmp_chunk_dir') . '/' . $audioUploadChunk->getFilename(),
            $audioUploadChunk->getAudioData()
        );

        $em->persist($audioUploadChunk);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * Merge uploaded chunks
     *
     * @Route("/audio/merge", methods={"POST"})
     *
     * @param Request $request
     * @param AudioUploadRepository $audioUploadRepository
     * @param ChunkMerger $chunkMerger
     * @param Filesystem $filesystem
     * @param EntityManagerInterface $em
     * @param Bucket $googleStorageBucket
     *
     * @return JsonResponse
     */
    public function audioMerge(
        Request $request,
        AudioUploadRepository $audioUploadRepository,
        ChunkMerger $chunkMerger,
        Filesystem $filesystem,
        EntityManagerInterface $em,
        Bucket $googleStorageBucket
    ) {
        $audioUpload = $audioUploadRepository->find($request->request->get('upload_id'));

        if (!$audioUpload) {
            throw $this->createNotFoundException();
        }

        $audioData = $chunkMerger->merge($audioUpload->getAudioUploadChunks()->toArray());
        $rawAudioData = base64_decode($audioData);

        $filepath = $this->getParameter('filesystem.tmp_audio_dir') . '/' . $audioUpload->getFilename();
        $filesystem->dumpFile($filepath, $rawAudioData);

        // Upload to Google storage
        $googleStorageBucket->upload(fopen($filepath, 'r'));

        // Move to public dir
        $filesystem->copy($filepath, $this->getParameter('filesystem.web_audio_dir') . '/' . $audioUpload->getFilename());
        $filesystem->remove($filepath);

        $chunkFiles = [];
        $audioUpload->setStatus(AudioUpload::UPLOAD_STATUS_UPLOADED);
        foreach ($audioUpload->getAudioUploadChunks() as $chunk) {
            $chunkFiles[] = $this->getParameter('filesystem.tmp_chunk_dir') . '/' . $chunk->getFilename();
            $audioUpload->removeAudioUploadChunk($chunk);
        }

        $filesystem->remove($chunkFiles);
        $em->flush();

        return new JsonResponse([], 201, [
            'Location' => $this->generateUrl('api_get_audio', ['id' => $audioUpload->getId()])
        ]);
    }

    /**
     * Get Audio
     *
     * @Route("/audio/{id}", name="api_get_audio", methods={"GET"})
     * @ParamConverter("audioUpload", class="App\Entity\AudioUpload")
     *
     * @param AudioUpload $audioUpload
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    public function getAudio(
        AudioUpload $audioUpload,
        SerializerInterface $serializer
    ) {
        return new JsonResponse(
            $serializer->serialize($audioUpload, 'json', ['groups' => ['rest']]),
            200,
            [],
            true
        );
    }

    /**
     * Delete Audio
     *
     * @Route("/audio/{id}", name="api_get_audio", methods={"DELETE"})
     * @ParamConverter("audioUpload", class="App\Entity\AudioUpload")
     *
     * @param AudioUpload $audioUpload
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function deleteAudio(AudioUpload $audioUpload, EntityManagerInterface $em)
    {
        $chunkFiles = [];
        $audioUpload->setIsDeleted(true);
        foreach ($audioUpload->getAudioUploadChunks() as $chunk) {
            $chunkFiles[] = $this->getParameter('filesystem.tmp_chunk_dir') . '/' . $chunk->getFilename();
            $audioUpload->removeAudioUploadChunk($chunk);
        }

        $em->flush();

        return new JsonResponse();
    }
}
