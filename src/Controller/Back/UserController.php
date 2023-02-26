<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserAdminType;
use App\Form\UserUpdateType;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/user')]
#[Security("is_granted('ROLE_ADMIN')")]
class UserController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/', name: 'user_index', methods: ['GET', 'POST'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->search($request);  

        $adapter = new QueryAdapter($users);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            $request->request->get('limit', 10)
        );

        return $this->render('back/user/index.html.twig', [
            'pager' => $pager,
            'limit' => $request->request->get('limit', 10)
        ]);
    }
    
    #[Route('/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(Request $request, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $user = new User();
        $pwd = bin2hex(openssl_random_pseudo_bytes(4));
        $user->setPlainPassword($pwd);
        $user->setStatus(true);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setValidationToken($pwd);
        $form = $this->createForm(UserAdminType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            $url = $this->generateUrl('app_forgot_password_request', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('mission-bot@kgbytes.com')
                ->to($user->getEmail())
                ->subject('Personnal informations for your new admin account')
                ->html('<p>You will found in this email your personnal password for your new admin account: ' . $pwd .' . Don\'t share it with anyone and don\'t forget that you can modify this password by following <a href='.$url.'>this link</a></p>');
            
            $mailer->send($email);

            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('back/user/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('back/user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/{id}/update', name: 'user_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(user $user, Request $request, userRepository $userRepository): Response
    {
        $form = $this->createForm(UserUpdateType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('back/user/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/{id}/delete/{token}', name: 'user_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(User $user, string $token, UserRepository $userRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $userRepository->remove($user, true);

        return $this->redirectToRoute('admin_user_index');
    }

}
