<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Entity\User;

#[Route('/api/user')]
class UserController extends AbstractController
{

    #[Route('/{user}', name: 'user.getOne', methods: ['GET'])]
    public function getUserById(User $user, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json');
        return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('', name: 'user.getAll', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        $jsonUsers = $serializer->serialize($users, 'json');
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'user.create', methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $entityManager->persist($user);
        $entityManager->flush();

        $jsonUser = $serializer->serialize($user, 'json');
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{user}', name: 'user.update', methods: ['PUT'])]
    public function updateUser(User $user, UserRepository $userRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $updatedUser = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setUsername($updatedUser->getUsername());
        $user->setPassword($updatedUser->getPassword());
        $user->setRoles($updatedUser->getRoles());
        $user->setEmail($updatedUser->getEmail());
        $entityManager->flush();

        $jsonUser = $serializer->serialize($user, 'json');
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/{user}', name: 'user.delete', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
