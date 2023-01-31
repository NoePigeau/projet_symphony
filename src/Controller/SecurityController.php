<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// use Mailgun\Mailgun;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function create(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(\App\Form\UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setValidationToken(bin2hex(random_bytes(10)));


            $url = $this->generateUrl('profile_token_validation', ['token' => $user->getValidationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

            $transport = Transport::fromDsn($_ENV["MAILER_DSN"]);
            $mailer = new Mailer($transport);

            $email = (new Email())->from('support@'.$_ENV["MAILGUN_DOMAIN"]);
    
            $email->to($user->getEmail());
            $email->subject('KGB Agent recruitment');
            $email->html('<h1>Thanks you for your registration</h1>
            <span>To verify your account, please click on the following <a href='.$url.'> link </a> or go on the following link: '.$url.'</span>');

            $userRepository->save($user, true);

            return $this->redirectToRoute('front_default_index');
        }
        

        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/profile/token-validation', name: 'profile_token_validation', methods: ['GET'])]
    public function tokenValidation(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->findOneBy(array('validationToken' => $request->query->get('token')));
        if (!is_null($user)) {
            $user->setStatus(true);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('front_default_index');
        } 

        // TODO RETURN ERROR PAGE

        return $this->redirectToRoute('front_default_index');


    }

    #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(\App\Form\UserType::class, $this->getUser());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('front_default_index');
        }

        return $this->render('security/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
