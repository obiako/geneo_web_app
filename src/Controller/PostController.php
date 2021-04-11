<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class PostController
 * @package App\Controller
 * @Route("/api", name="apis")
 */

class PostController extends AbstractController
{

    /**
     * @param PostRepository $postRepository
     * @return JsonResponse
     * @Route("/posts", name="posts", methods={"GET"})
     */
    public function getPosts(PostRepository $postRepository):JsonResponse
    {
        $data = $postRepository->findAll();
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     * @param TagRepository $tagRepository
     * @return JsonResponse
     * @Route("/posts", name="posts_add", methods={"POST"})
     */
    public function addPost(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, UserRepository $userRepository,TagRepository $tagRepository)
    {

        try{
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('title') || !$request->get('content')){
                throw new \Exception();
            }
            $user= $userRepository->find($this->getUser()->getId());
            $post = new Post();
            $post->setTitle($request->get('name'));
            $post->setContent($request->get('description'));
            $post->setUser($user);
            $post->setPublished(false);
            $entityManager->persist($post);
            $tags=$request->get('tags');
            if(count($tags)>1){
                foreach($tags as $tag){
                    $new_tag = $tagRepository->findOneBy(['name' => $tag]);
                    if(!$new_tag){
                        $new_tag = new Tag();
                        $new_tag->setName($tag);
                    }

                    $new_tag->addPost($post);
                    $entityManager->persist($new_tag);

                }
            }



            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Post added successfully",
            ];
            return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data not valid",
            ];
            return $this->response($data, 422);
        }

    }
    /**
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/posts/{id}", name="posts_get", methods={"GET"})
     */
    public function getPost(PostRepository $postRepository, $id)
    {
        $post = $postRepository->find($id);

        if (!$post){
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return $this->response($data, 404);
        }
        return $this->response($post);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/posts/{id}", name="posts_put", methods={"PUT"})
     */
    public function updatePost(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, $id){

        try{
            $post = $postRepository->find($id);
            $loggedInUser=$this->security->getUser();

            if (!$post){
                $data = [
                    'status' => 404,
                    'errors' => "Post not found",
                ];
                return $this->response($data, 404);
            }
            if($post->getUser()->getId()!=$this->getUser()->getId() || $this->isGranted(['ROLE_ADMIN'])){
                $data = [
                    'status' => 422,
                    'errors' => "User is not Post owner",
                ];
                return $this->response($data, 422);

            }
            if($post->getPublished()==true){
                $data = [
                    'status' => 422,
                    'errors' => "Post published and cannot be modified",
                ];
                return $this->response($data, 422);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('title') || !$request->get('content')){
                throw new \Exception();
            }

            $post->setTitle($request->get('title'));
            $post->setContent($request->get('content'));
            $entityManager->flush();

            $data = [
                'status' => 200,
                'errors' => "Post updated successfully",
            ];
            return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }

    }

    /**
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/posts/{id}", name="posts_delete", methods={"DELETE"})
     */
    public function deletePost(EntityManagerInterface $entityManager, PostRepository $postRepository, $id){
        $post = $postRepository->find($id);

        if (!$post){
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return $this->response($data, 404);
        }
        if ($post->getUser()!=$this->getUser() or $this->isGranted('ROLE_ADMIN')){
            $data = [
                'status' => 404,
                'errors' => "You do not have the permission to modify this post",
            ];
            return $this->response($data, 404);
        }


        $entityManager->remove($post);
        $entityManager->flush();
        $data = [
            'status' => 200,
            'errors' => "Post deleted successfully",
        ];
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("/post-comments", name="comments_add", methods={"POST"})
     */
    public function addComment(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, UserRepository $userRepository)
    {

        try{
            $request = $this->transformJsonBody($request);
            if (!$request || !$request->get('content') || !$request->get('post_id')){
                throw new \Exception();
            }
            $user= $userRepository->find($this->getUser()->getId());
            $post = $postRepository->find($request->get('post_id'));
            if($post->getPublished()==false){
                $data = [
                    'status' => 422,
                    'errors' => "Post has not been published and cannot be commented on",
                ];
                return $this->response($data, 422);
            }
            if (!$post->getUser()->getMyFollowers()->contains($user) || !$post->getUser()->getId()==$user->getId()){
                $data = [
                    'status' => 422,
                    'errors' => "You do not follow this user and cannot comment on their post.",
                ];
                return $this->response($data, 422);
            }



            $comment = new Comment();
            $comment->setContent($request->get('content'));
            $comment->setUser($user);
            $comment->setPost($post);
            $entityManager->persist($comment);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Post added successfully",
            ];
            return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data not valid",
            ];
            return $this->response($data, 422);
        }

    }


    /**
     * Returns a JSON response
     *
     * @param array $data
     * @param $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data, $status = 200, $headers = []):JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function transformJsonBody(Request $request):Request
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}
