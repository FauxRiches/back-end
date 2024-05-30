<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\SongRepository;
use App\Entity\Song;

class SongController extends AbstractController
{

    #[Route('/api/song/{idSong}', name: 'song.getOne', methods: ['GET'])]
    public function getEvent(Song $song, SerializerInterface $serializer): JsonResponse
    {
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
    public function updateSong(Song $song, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $updatedSong = $serializer->deserialize($request->getContent(), Song::class, 'json');
        $song->setTitle($updatedSong->getTitle());
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
}
