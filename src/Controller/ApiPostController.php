<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiPostController extends AbstractController
{
    /**
     * Permet de transformer nos données en objets json
     * 
     * @Route("/api/post", name="api_post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository)
    {
        // $posts = $postRepository->findAll();

        // $postsNormalizer = $normalazer->normalize($posts, null, ['groups' => 'post:read']);

        // $json = json_encode($postsNormalizer);

        // $response = new Response($json, 200, [
        //     "Content-Type" => "application/json"
        // ]);

        // $json = $serializer->serialize($posts, 'json', ['groups' => 'post:read']);
        // $response = new JsonResponse($json, 200, [], true);

        // $response = $this->json($postRepository->findAll(), 200, [], ['groups' => 'post:read']);
        // return $response;

        return $this->json($postRepository->findAll(), 200, [], ['groups' => 'post:read']);
    }

    /**
     * Permet de transformer du json en entité pour notre application
     * 
     * @Route("/api/post", name="api_post_store", methods={"POST"})
     */
    public function store(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $jsonRecu = $request->getContent();

        try {
            $post = $serializer->deserialize($jsonRecu, Post::class, 'json');
            $post->setCreatedAt(new \DateTime());

            $errors = $validator->validate($post);

            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }

            $manager->persist($post);
            $manager->flush();

            return $this->json($post, 201, [], ['groups' => 'post:read']);

        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
