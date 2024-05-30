<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Repository\SongRepository;
use App\Entity\Song;

class SongController extends AbstractController
{

    #[Route('/api/song/{idSong}', name: 'song.getOne', methods: ['GET'])]
    public function getSong(int $idSong, SongRepository $songRepository, SerializerInterface $serializer): JsonResponse
    {
        $song = $songRepository->find($idSong);
        $jsonSong = $serializer->serialize($song, 'json');
        return new JsonResponse($jsonSong, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/songs', name: 'song.getAll', methods: ['GET'])]
    public function getAllSongs(SongRepository $songRepository, SerializerInterface $serializer): JsonResponse
    {
        $songs = $songRepository->findAll();
        $jsonSongs = $serializer->serialize($songs, 'json');
        return new JsonResponse($jsonSongs, Response::HTTP_OK, [], true);
    }

    #[Route('/api/songs', name: 'song.create', methods: ['POST'])]
    public function createSong(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $song = $serializer->deserialize($request->getContent(), Song::class, 'json');
        $entityManager->persist($song);
        $entityManager->flush();

        $jsonSong = $serializer->serialize($song, 'json');
        return new JsonResponse($jsonSong, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/song/{idSong}', name: 'song.update', methods: ['PUT'])]
    public function updateSong(int $idSong, SongRepository $songRepositor, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $updatedSong = $serializer->deserialize($request->getContent(), Song::class, 'json');
        $song = $songRepositor->find($idSong);
        $song->getTitle($updatedSong->getTitle());
        $song->setArtist($updatedSong->getArtist());
        $song->setAlbum($updatedSong->getAlbum());
        $song->setStatus($updatedSong->getStatus());
        $entityManager->flush();

        $jsonSong = $serializer->serialize($song, 'json');
        return new JsonResponse($jsonSong, Response::HTTP_OK, [], true);
    }

    #[Route('/api/song/{idSong}', name: 'song.delete', methods: ['DELETE'])]
    public function deleteSong(Song $song, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($song);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/api/song/{idSong}/status', name: 'song.updateStatus', methods: ['PATCH'])]
    public function updateStatus(int $idSong, SongRepository $songRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Status field is missing'], Response::HTTP_BAD_REQUEST);
        }
        $status = $data['status'];

        $song = $songRepository->find($idSong);
        
        if (!$song) {
            return new JsonResponse(['error' => 'Song not found'], Response::HTTP_NOT_FOUND);
        }

        $song->setStatus($status);
        $entityManager->flush();

        $jsonSong = $serializer->serialize($song, 'json');

        return new JsonResponse($jsonSong, Response::HTTP_OK, [], true);
    }

}
