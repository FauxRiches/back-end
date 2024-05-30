<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Link;
use App\Entity\Song;
use Endroid\QrCode\QrCode;

class LinkController extends AbstractController
{


    #[Route('/api/links', name: 'link.getAll', methods: ['GET'])]
    public function getAllLinks(LinkRepository $linkRepository, SerializerInterface $serializer): JsonResponse
    {
        $links = $linkRepository->findAll();
        $jsonLinks = $serializer->serialize($links, 'json');
        return new JsonResponse($jsonLinks, Response::HTTP_OK, [], true);
    }

    #[Route('/api/links/{idLink}', name: 'link.getOne', methods: ['GET'])]
    public function getLink(int $idLink, LinkRepository $linkRepository, SerializerInterface $serializer): JsonResponse
    {
        $link = $linkRepository->find($idLink);
        $jsonLink = $serializer->serialize($link, 'json');
        return new JsonResponse($jsonLink, Response::HTTP_OK, [], true);
    }

    #[Route('/api/links', name: 'link.create', methods: ['POST'])]
    public function createLink(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $link = new Link();

        $link->setUrl('');

        $entityManager->persist($link);
        $entityManager->flush();

        $link->setUrl($this->generateUrlForLink($link->getId()));

        $entityManager->persist($link);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($link, 'json'), Response::HTTP_CREATED, [], true);
    }


    #[Route('/api/links/addSong', name: 'link.addSong', methods: ['POST'])]
    public function addSongToLink(Request $request, Link $link, SerializerInterface $serializer, LinkRepository $linkRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $song = $entityManager->getRepository(Song::class)->find($data['songId']);

        $link->addSong($song);

        $entityManager->flush();

        return new JsonResponse($serializer->serialize($link, 'json'), Response::HTTP_OK, [], true);
    }


    #[Route('/api/qr-code/{idLink}', name: 'link.qrCode', methods: ['GET'])]
    public function getQrCode(int $idLink, LinkRepository $linkRepository): Response
    {
        $link = $linkRepository->find($idLink);

        $qrCode = new QrCode($link->getUrl());

        $qrCode->setSize(300);

        return new Response($qrCode->writeString(), Response::HTTP_OK, ['Content-Type' => $qrCode->getContentType()]);
    }

    private function generateUrlForLink(int $linkId): string
    {
        // Customize the domain as needed
        return sprintf('http://localhost:8000/links/%d', $linkId);
    }
}
