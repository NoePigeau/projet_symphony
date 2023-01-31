<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/', name: 'user_index', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->search($request);  

        $adapter = new QueryAdapter($users);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            12
        );

        return $this->render('back/user/index.html.twig', [
            'pager' => $pager
        ]);
    }
    
    #[Route('/create', name: 'user_create', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function create(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('back/user/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function show(User $user): Response
    {
        return $this->render('back/user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/{id}/update', name: 'user_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function update(user $user, Request $request, userRepository $userRepository): Response
    {
        $form = $this->createForm(userType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('admin_user_show', ['slug' => $user->getId()]);
        }

        return $this->render('back/user/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/{id}/delete/{token}', name: 'user_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function delete(User $user, string $token, UserRepository $userRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $userRepository->remove($user, true);

        return $this->redirectToRoute('admin_user_index');
    }

}
