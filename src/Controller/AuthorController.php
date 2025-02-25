<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthorController extends AbstractController
{
    #[Route('/api/authors', name: 'authors', methods: ['GET'])]
    public function getAuthorList(AuthorRepository $authorRepository,
    SerializerInterface $serializer): JsonResponse
    {
        $authorList = $authorRepository->findAll();
        $jsonAuthorList = $serializer->serialize($authorList, 'json', ['groups' => 'getAuthor']);
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/authors/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getDetailAuthor(Author $author,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthor']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/authors', name: 'createAuthor', methods: ['POST'])]
    public function createAuthor(Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $em, 
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator): JsonResponse
    {
        //gets data in json format from the body, deserialize in an Author object
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');
        
        //Validates data before persist. Returns an error object if not ok
        $errors = $validator->validate($author);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($author);
        $em->flush();
        //gets new author data and serializes in json format
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthors']);
        //gets the idAuthor to generate the url of the new author to populate headers
        $location = $urlGenerator->generate('detailAuthor', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse ($jsonAuthor, Response::HTTP_CREATED, ["location" => $location], true);
    }
    
    #[Route('/api/authors/{id}', name:"updateAuthor", methods:['PUT'])]
    public function updateAuthor(Request $request, 
        SerializerInterface $serializer, 
        Author $currentAuthor, 
        EntityManagerInterface $em): JsonResponse
    {
        $updatedAuthor = $serializer->deserialize($request->getContent(), 
                Author::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]);
        
        $em->persist($updatedAuthor);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    public function deleteAuthor(Author $author,
    EntityManagerInterface $em): JsonResponse
    {
        $em->remove($author);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


}

