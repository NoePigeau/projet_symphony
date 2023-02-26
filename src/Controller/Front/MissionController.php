<?php

namespace App\Controller\Front;

use App\Entity\Message;
use App\Entity\Mission;
use App\Entity\Rating;
use App\Form\MessageType;
use App\Form\MissionType;
use App\Form\RatingType;
use App\Repository\MessageRepository;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\RatingRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Security\Voter\MissionVoter;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[Route('/mission')]
#[Security("is_granted('ROLE_USER')")]
class MissionController extends AbstractController
{
	
	/**
	 * @param MissionRepository $missionRepository
	 * @return Response
	 */
	#[Route('/', name: 'mission_index', methods: ['GET'])]
	#[Security("is_granted('ROLE_AGENT')")]
	public function index(MissionRepository $missionRepository): Response
	{
		$user = $this->getUser();
		$missions = $missionRepository->createQueryBuilder('m')
			->where('m.status = :status')
			->andWhere('m.type IN (:types)')
			->setParameter('types', $user->getType())
			->setParameter('status', 'free')
			->getQuery()
			->getResult();
		
		return $this->render('front/mission/index.html.twig', [
			'missions' => $missions,
			'user' => $user
		]);
	}
	
	/**
	 * @param MissionRepository $missionRepository
	 * @return Response
	 */
	#[Route('/my-missions', name: 'mission_my_missions', methods: ['GET'])]
	public function myMissions(MissionRepository $missionRepository): Response
	{
		$user = $this->getUser();
		$missions = $missionRepository->findBy([in_array('ROLE_CLIENT', $user->getRoles()) ? 'client' : 'agent' => $user]);
		return $this->render('front/mission/index.html.twig', [
			'missions' => $missions,
			'user' => $user
		]);
	}
	
	/**
	 * @param Mission           $mission
	 * @param Request           $request
	 * @param MissionRepository $missionRepository
	 *
	 * @return Response
	 */
    #[Route('/{slug}/update', name: 'mission_update', methods: ['GET', 'POST'])]
    #[IsGranted(MissionVoter::EDIT, 'mission')]
    public function update(Mission $mission, Request $request, MissionRepository $missionRepository): Response
    {
        $form = $this->createForm(MissionType::class, $mission);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $missionRepository->save($mission, true);

            return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
        }

        return $this->render('front/mission/update.html.twig', [
            'form' => $form->createView(),
            'mission' => $mission
        ]);
    }
	
	/**
	 * @param Request           $request
	 * @param MissionRepository $missionRepository
	 *
	 * @return Response
	 */
	#[Route('/create', name: 'mission_create', methods: ['GET', 'POST'])]
	#[Security("is_granted('ROLE_CLIENT')")]
	public function create(Request $request, MissionRepository $missionRepository): Response
	{
		$user = $this->getUser();
		$mission = new Mission();
		$form = $this->createForm(MissionType::class, $mission);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$mission->setClient($user);
			$mission->setStatus($mission::STATUS_IN_DEMAND);
			$missionRepository->save($mission, true);
			return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
		}
		
		return $this->render('front/mission/create.html.twig', [
			'form' => $form->createView()
		]);
	}
	
	/**
	 * @param Mission           $mission
	 * @param RatingRepository  $ratingRepository
	 * @param PaymentRepository $paymentRepository
	 *
	 * @return Response
	 */
	#[Route('/{slug}', name: 'mission_show', methods: ['GET'])]
	#[IsGranted(MissionVoter::VIEW, 'mission')]
	public function show(Mission $mission, RatingRepository $ratingRepository, PaymentRepository $paymentRepository): Response
	{
		$rating = $ratingRepository->findOneBy(['mission' => $mission]);
		$form = $this->createForm(RatingType::class, $rating ?: new Rating());
		
		try{
			$averageRating = $ratingRepository
				->createQueryBuilder('r')
				->select('AVG(r.rate) as average')
				->where('r.agent = :agent')
				->setParameter('agent', $mission->getAgent())
				->getQuery()
				->getSingleScalarResult()
			;
		}catch(NoResultException|NonUniqueResultException){
			$averageRating = 0;
		}
		
		$messageForm = $this->createForm(MessageType::class);
		
		$payment = $paymentRepository->findOneBy(['mission' => $mission]);
		
		return $this->render('front/mission/show.html.twig', [
			'mission' => $mission,
			'form' => $form->createView(),
			'rating' => $rating,
			'averageRating' => $averageRating,
			'formMessage' => $messageForm->createView(),
			'payment' => $payment
		]);
	}
	
	/**
	 * @param Mission           $mission
	 * @param string            $token
	 * @param MissionRepository $missionRepository
	 *
	 * @return Response
	 */
    #[Route('/{id}/delete/{token}', name: 'mission_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(MissionVoter::EDIT, 'mission')]
    public function delete(Mission $mission, string $token, MissionRepository $missionRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $mission->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $missionRepository->remove($mission, true);

        return $this->redirectToRoute('front_mission_my_missions');
    }
	
	/**
	 * @param Request           $request
	 * @param Mission           $mission
	 * @param HubInterface      $hub
	 * @param MessageRepository $mr
	 *
	 * @return Response
	 */
	#[Route('/{id}/message', name: 'mission_send_messages', methods: ['POST'])]
	public function sendMessage(Request $request, Mission $mission, HubInterface $hub, MessageRepository $mr): Response
	{
		$user = $this->getUser();
		if ($user->getId() !== $mission->getAgent()->getId() && $user->getId() !== $mission->getClient()->getId()) {
			return new Response('Not authorized');
		}
		
		$message = new Message();
		
		$missionId = $mission->getId();
		
		$reqContent = json_decode($request->getContent(), true);
		$message->setContent($reqContent['message[content]']);
		$message->setFromId($user);
		$message->setMissionId($mission);
		$mr->save($message, true);
		$update = new Update(
			"/messages/$missionId",
			json_encode(['message' => $message->getContent(), 'userId' => $user->getId()])
		);
		
		$hub->publish($update);
		return new Response($reqContent['message[content]']);
	}
	
	/**
	 * @param Mission           $mission
	 * @param string            $token
	 * @param MissionRepository $missionRepository
	 * @param MailerInterface   $mailer
	 *
	 * @return Response
	 * @throws TransportExceptionInterface
	 */
	#[Route('/{id}/asign/{token}', name: 'mission_asign', requirements: ['id' => '\d+'], methods: ['GET'])]
	#[Security("is_granted('ROLE_AGENT')")]
	public function asign(Mission $mission, string $token, MissionRepository $missionRepository, MailerInterface $mailer): Response
	{
		if (!$this->isCsrfTokenValid('asign' . $mission->getId(), $token)) {
			throw $this->createAccessDeniedException('Error token!');
		}
		
		$mission->setAgent($this->getUser());
		$mission->setStatus('in_progress');
		$missionRepository->save($mission, true);
		
		$email = (new Email())
			->from('mission-bot@kgbytes.com')
			->to($mission->getClient()->getEmail())
			->subject('Your mission has been accepted')
			->html('<p>' . $mission->getAgent()->getNickname() . ' accepted your mission: ' . $mission->getName() .'. You will be noticed when our agent will finish the mission.</p>');
		
		$mailer->send($email);
		
		return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
	}
	
	/**
	 * @param Mission           $mission
	 * @param string            $token
	 * @param UserRepository    $userRepository
	 * @param MissionRepository $missionRepository
	 * @param TypeRepository    $typeRepository
	 *
	 * @return RedirectResponse
	 */
	#[Route('/{id}/give-up/{token}', name: 'give_up', requirements: ['id' => '\d+'], methods: ['GET'])]
	#[Security("is_granted('ROLE_AGENT')")]
	public function giveUp(Mission $mission, string $token, UserRepository $userRepository, MissionRepository $missionRepository, TypeRepository $typeRepository): RedirectResponse {
		if (!$this->isCsrfTokenValid('give_up' . $mission->getId(), $token)) {
			throw $this->createAccessDeniedException('Error token!');
		}
		
		$adminUser = $userRepository->findOneBy(['email' => 'admin@user.fr']);
		$type = $typeRepository->findOneBy(['name' => 'Murder']);
		
		$murderMission = new Mission();
		$murderMission->setType($type);
		$murderMission->setClient($adminUser);
		$murderMission->setName("Eliminate traitor");
		$murderMission->setDescription(
			"Eliminate the traitor named : " .
			$mission->getAgent()->getFirstname() . ' "' .
			$mission->getAgent()->getNickname() . '" ' .
			$mission->getAgent()->getLastname()
		);
		$murderMission->setStatus($mission::STATUS_FREE);
		$murderMission->setReward($mission->getReward());
		$murderMission->setCreatedAt(new DateTime());
		$murderMission->setUpdatedAT(new DateTime());
		
		$missionRepository->save($murderMission, true);
		
		$agent = $this->getUser();
		if ($agent === $mission->getAgent()){
			$agent->setRoles(['ROLE_BANNED']);
			$agent->setDescription('Banned agent');
			$agent->setStatus(false);
			$agent->setUpdatedAT(new DateTime());
			$userRepository->save($agent, true);
		}
		
		//find all mission where $this->getUser() is the agent and set the status to free and set the agent to null
		$missions = $missionRepository->findBy(['agent' => $this->getUser()]);
		foreach ($missions as $mission) {
			$mission->setAgent(null);
			$mission->setStatus('free');
			$missionRepository->save($mission, true);
		}
		return $this->redirectToRoute('front_mission_my_missions');
	}
	
	/**
	 * @param Mission           $mission
	 * @param string            $token
	 * @param MissionRepository $missionRepository
	 * @param MailerInterface   $mailer
	 *
	 * @return Response
	 * @throws TransportExceptionInterface
	 */
	#[Route('/{id}/finish/{token}', name: 'mission_finish', requirements: ['id' => '\d+'], methods: ['GET'])]
	#[Security("is_granted('ROLE_AGENT')")]
	public function finish(Mission $mission, string $token, MissionRepository $missionRepository, MailerInterface $mailer): Response
	{
		if (!$this->isCsrfTokenValid('finish' . $mission->getId(), $token)) {
			throw $this->createAccessDeniedException('Error token!');
		}
		
		$mission->setStatus('finished');
		$missionRepository->save($mission, true);
		
		$email = (new Email())
			->from('mission-bot@kgbytes.com')
			->to($mission->getClient()->getEmail())
			->subject('Our agent completed your mission !')
			->html('<p>' . $mission->getAgent()->getNickname() . ' completed your mission: ' . $mission->getName() .'. You can now send him his reward. Thank you for trusting our services.</p>');
		
		$mailer->send($email);
		
		return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
	}
}