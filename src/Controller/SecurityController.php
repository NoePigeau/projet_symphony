<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\User;
use App\Form\SkillType;
use App\Form\UserType;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Repository\DocumentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// use Mailgun\Mailgun;

/**
 * Class SecurityController
 *
 * @package App\Controller
 */
class SecurityController extends AbstractController {
	#[Route(path : '/login', name : 'app_login')]
	public function login( Request $request, AuthenticationUtils $authenticationUtils ): Response {
		$challengePassed = $request->getSession()->get('challenge_passed', false);
		if(!$challengePassed){
			return $this->redirectToRoute('front_default_cat');
		}
		
		$user = $this->getUser();
		if($this->getUser()){
			return $this->redirectToRoute('profile');
		}
		
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();
		
		
		return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
	}
	
	/**
	 * @param Request         $request
	 * @param UserRepository  $userRepository
	 * @param MailerInterface $mailer
	 *
	 * @return Response
	 * @throws TransportExceptionInterface
	 */
	#[Route('/register', name : 'register', methods : ['GET', 'POST'])]
	public function create( Request $request, UserRepository $userRepository, MailerInterface $mailer ): Response {
		$challengePassed = $request->getSession()->get('challenge_passed', false);
		if(!$challengePassed){
			return $this->redirectToRoute('front_default_cat');
		}
		
		if($this->getUser()){
			return $this->redirectToRoute('profile');
		}
		
		$user = new User();
		$form = $this->createForm(\App\Form\UserType::class, $user);
		
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()){
			$user->setValidationToken(bin2hex(random_bytes(10)));
			$user->setRoles(['ROLE_CLIENT']);
			
			$url = $this->generateUrl('profile_token_validation', ['token' => $user->getValidationToken()], UrlGeneratorInterface::ABSOLUTE_URL);
			
			
			$email = new Email();
			
			$email->from('mission-bot@kgbytes.com');
			$email->to($user->getEmail());
			$email->subject('KGB Agent recruitment');
			$email->html('<h1>Thanks you for your registration</h1>
            <span>To verify your account, please click on the following <a href=' . $url . '> link </a> or go on the following link: ' . $url . '</span>');
			
			$mailer->send($email);
			
			$userRepository->save($user, true);
			
			return $this->redirectToRoute('front_default_index');
		}
		
		
		return $this->render('security/register.html.twig', ['form' => $form->createView()]);
	}
	
	/**
	 * @param Request                $request
	 * @param UserRepository         $userRepository
	 * @param EntityManagerInterface $em
	 *
	 * @return Response
	 */
	#[Route('/profile/token-validation', name : 'profile_token_validation', methods : ['GET'])]
	public function tokenValidation( Request $request, UserRepository $userRepository, EntityManagerInterface $em ): Response {
		$user = $userRepository->findOneBy(array('validationToken' => $request->query->get('token')));
		if(!is_null($user)){
			$user->setStatus(true);
			$em->persist($user);
			$em->flush();
			return $this->render('security/token-validation.html.twig');
		}
		
		return $this->render('security/token-validation.html.twig', ['errors' => "Couldn't validate the token."]);
		
		
	}
	
	/**
	 * @param Request                $request
	 * @param EntityManagerInterface $entityManager
	 * @param DocumentRepository     $dr
	 *
	 * @return Response
	 */
	#[Route('/profile/become', name : 'profile_become_agent', methods : ['GET', 'POST'])]
	#[Security("is_granted('ROLE_USER')")]
	public function becomeAgent( Request $request, EntityManagerInterface $entityManager, DocumentRepository $dr ): Response {
		$document = new Document();
		$form = $this->createForm(\App\Form\BecomeType::class, $document);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()){
			$entityManager->flush();
			$document->setSubmitedBy($this->getUser());
			$dr->save($document, true);
			return $this->redirectToRoute('profile');
		}
		
		return $this->render('security/become.html.twig', ['form' => $form->createView(),]);
	}
	
	/**
	 * @Route("/logout", name="app_logout")
	 * @Security("is_granted('ROLE_USER')")
	 * @param Request                $request
	 * @param EntityManagerInterface $entityManager
	 * @param DocumentRepository     $dr
	 *
	 * @return Response
	 */
	#[Route('/profile', name : 'profile', methods : ['GET', 'POST'])]
	#[Security("is_granted('ROLE_USER')")]
	public function profile( Request $request, EntityManagerInterface $entityManager, DocumentRepository $dr ): Response {
		$form = $this->createForm(UserType::class, $this->getUser(), ['isUpdate' => true]);
		
//		$user = new User();
		
		$formSkill = $this->createForm(SkillType::class, $this->getUser());
		
		$hasPendingRequest = $dr->findOneBy(array('submitedBy' => $this->getUser()->getId()));
		
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()){
			$entityManager->flush();
			
			return $this->redirectToRoute('profile');
		}
		
		$formSkill->handleRequest($request);
		if($formSkill->isSubmitted() && $formSkill->isValid()){
			$entityManager->flush();
			return $this->redirectToRoute('profile');
		}
		
		
		return $this->render('security/profile.html.twig', ['form' => $form->createView(), 'hasPending' => $hasPendingRequest, 'formSkill' => $formSkill->createView()]);
	}
	
	/**
	 * @Route("/history", name="history")
	 * @Security("is_granted('ROLE_CLIENT') or is_granted('ROLE_AGENT')")
	 * @param MissionRepository $missionRepository
	 * @param PaymentRepository $paymentRepository
	 *
	 * @return Response
	 */
	#[Security("is_granted('ROLE_CLIENT') or is_granted('ROLE_AGENT')")]
	#[Route('/history', name : 'history')]
	public function history( MissionRepository $missionRepository, PaymentRepository $paymentRepository ): Response {
		$user = $this->getUser();
		$missions = [];
		if($user->getRoles()[0] == 'ROLE_AGENT'){
			$missions = $missionRepository->findBy(['agent' => $user->getId()]);
		}else if($user->getRoles()[0] == 'ROLE_CLIENT'){
			$missions = $missionRepository->findBy(['client' => $user->getId()]);
		}
		
		
		$payments = $paymentRepository->findAll();
		
		return $this->render('security/history.html.twig', ['missions' => $missions, 'payments' => $payments]);
	}
	
	/**
	 * @Route("/logout", name="app_logout")
	 * @Security("is_granted('ROLE_USER')")
	 */
	#[Route(path : '/logout', name : 'app_logout')]
	#[Security("is_granted('ROLE_USER')")]
	public function logout(): void {
		throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}
}