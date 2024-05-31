<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Link;
use App\Entity\Song;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;

class LinkController extends AbstractController
{


    #[Route('/api/links', name: 'link.getAll', methods: ['GET'])]
    public function getAllLinks(LinkRepository $linkRepository, SerializerInterface $serializer): JsonResponse
    {
        $links = $linkRepository->findAll();
        $jsonLinks = $serializer->serialize($links, 'json');
        return new JsonResponse($jsonLinks, Response::HTTP_OK, [], true);
    }

    #[Route('/api/links/{link}', name: 'link.get', methods: ['GET'])]
    public function getLink(Link $link, LinkRepository $linkRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonLink =  $serializer->serialize($link, 'json');
        return new JsonResponse($jsonLink, Response::HTTP_OK, [], true);
    }

    #[Route('/api/links', name: 'link.create', methods: ['POST'])]
    public function createLink(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $link = $serializer->deserialize($request->getContent(), Link::class, 'json');


        $entityManager->persist($link);
        $entityManager->flush();

        $jsonLink = $serializer->serialize($link, "json");
        $location = $urlGenerator->generate("link.get", ["link" => $link->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonLink, Response::HTTP_CREATED, ["Location" => $location],  true);
    }


    #[Route('/api/links/addSong', name: 'link.addSong', methods: ['POST'])]
    public function addSongToLink(Request $request, SerializerInterface $serializer, LinkRepository $linkRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $song = $entityManager->getRepository(Song::class)->find($data['songId']);

        $link = $entityManager->getRepository(Link::class)->find($data['linkId']);

        $link->addSong($song);

        $entityManager->persist($link);
        $entityManager->flush();

        return new JsonResponse(json_encode($link), Response::HTTP_OK, [], true);
    }


    #[Route('/api/qr-code/{link}', name: 'link.qrCode', methods: ['GET'])]
    public function qrcodes(Link $link): JsonResponse
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($link->getUrl())
            ->setEncoding(new Encoding('UTF-8'))
            // ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(120)
            ->setMargin(0)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $logo = Logo::create('img/logo.png')
            ->setResizeToWidth(60);
        $label = Label::create('')->setFont(new NotoSans(8));
 
        $qrCodes = [];
        // $qrCodes['img'] = $writer->write($qrCode, $logo)->getDataUri();
        $qrCodes['img'] = $writer->write($qrCode)->getDataUri();
        $qrCodes['simple'] = $writer->write(
                                $qrCode,
                                null,
                                $label->setText('Simple')
                            )->getDataUri();
 
        $qrCode->setForegroundColor(new Color(255, 0, 0));
        $qrCodes['changeColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Color Change')
        )->getDataUri();
 
        $qrCode->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 0, 0));
        $qrCodes['changeBgColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Background Color Change')
        )->getDataUri();
 
        $qrCode->setSize(200)->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 255, 255));
        $qrCodes['withImage'] = $writer->write(
            $qrCode,
            $logo,
            $label->setText('With Image')->setFont(new NotoSans(20))
        )->getDataUri();
 
        // return $this->render('qr_code_generator/deuxdex.html.twig', $qrCodes);
        return new JsonResponse($qrCodes, Response::HTTP_OK, [], false);
    }
}
