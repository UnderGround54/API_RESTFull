<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'app_book', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    #[OA\Get(
        path: "/api/books",
        description: "Retrieve a paginated list of books.",
        summary: "Get all books",
        tags: ["Books"],
        parameters: [
            new OA\QueryParameter(name: "page", description: "Page number", required: false, schema: new OA\Schema(type: "integer")),
            new OA\QueryParameter(name: "limit", description: "Number of items per page", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful response",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: new Model(type: Book::class, groups: ['getBooks'])))
            ),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $bookList = $bookRepository->findAllWithPagination($page, $limit);

        $jsonBookList = $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/books/{id}', name: 'detailBook', methods: ['GET'])]
    #[OA\Get(
        path: "/api/books/{id}",
        description: "Retrieve the details of a specific book by its ID.",
        summary: "Get book details",
        tags: ["Books"],
        parameters: [
            new OA\Parameter(name: "id", description: "Book ID", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful response",
                content: new OA\JsonContent(ref: new Model(type: Book::class, groups: ['getBooks']))
            ),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function getDetailBook(Book $book, SerializerInterface $serializer): JsonResponse
    {
        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBook, Response::HTTP_OK, ['accept' => 'json'], true);
   }

    #[Route('/api/books/{id}', name: 'deleteBook', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/books/{id}",
        description: "Delete a specific book by its ID.",
        summary: "Delete a book",
        tags: ["Books"],
        parameters: [
            new OA\Parameter(name: "id", description: "Book ID", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 204, description: "No Content"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function deleteBook(Book $book, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($book);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/books', name:"createBook", methods: ['POST'])]
    #[OA\Post(
        path: "/api/books",
        description: "Create a new book with the provided data.",
        summary: "Create a new book",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: Book::class, groups: ['createBook']))
        ),
        tags: ["Books"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Created",
                content: new OA\JsonContent(ref: new Model(type: Book::class, groups: ['createBook']))
            ),
            new OA\Response(response: 400, description: "Bad Request")
        ]
    )]
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
     UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        
        $errors = $validator->validate($book);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $book->setAuthor($authorRepository->find($idAuthor));

        $em->persist($book);
        $em->flush();

        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'createBook']);
        $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
   }

    #[Route('/api/books/{id}', name: "updateBook", methods: ['PUT'])]
    #[OA\Put(
        path: "/api/books/{id}",
        description: "Update an existing book with the provided data.",
        summary: "Update an existing book",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: Book::class, groups: ['createBook']))
        ),
        tags: ["Books"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful response",
                content: new OA\JsonContent(ref: new Model(type: Book::class, groups: ['getBooks']))
            ),
            new OA\Response(response: 400, description: "Bad Request"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function updateBook(Request $request, SerializerInterface $serializer, Book $currentBook,
                               EntityManagerInterface $em, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        $updatedBook = $serializer->deserialize($request->getContent(), Book::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]);

        $errors = $validator->validate($updatedBook);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $updatedBook->setAuthor($authorRepository->find($idAuthor));

        $em->persist($updatedBook);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
