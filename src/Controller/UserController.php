<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api", name="apis")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    /**
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("/my_followers", name="my_followers", methods={"GET"})
     */
    public function getMyFollowers(UserRepository $userRepository):JsonResponse
    {
        $user= $userRepository->find($this->getUser()->getId());
        $data = $user->getMyFollowers();
        return $this->response($data);
    }
    /**
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("/followers_of_me", name="followers_of_me", methods={"GET"})
     */
    public function getFollowersOfMe(UserRepository $userRepository):JsonResponse
    {
        $user= $userRepository->find($this->getUser()->getId());
        $data = $user->getFollowersOfMe();
        return $this->response($data);
    }

    /**
     * @param UserRepository $userRepository
     * @param $user_id
     * @return JsonResponse
     * @Route("/follow_user/{user_id}", name="follow_user", methods={"GET"})
     */
    public function followUser(UserRepository $userRepository,$user_id):JsonResponse
    {
        try{
        $user_to_follow=$userRepository->find($user_id);

        $user= $userRepository->find($this->getUser()->getId());
        $user_to_follow->addFollowersOfMe($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user_to_follow);
        $entityManager->flush();

        $data = [
            'status' => 200,
            'errors' => "User Followed successfully",
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
