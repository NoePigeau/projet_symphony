<?php

namespace App\Controller\Front;

use App\Entity\Mission;
use App\Entity\Rating;
use App\Form\MissionType;
use App\Form\RatingType;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\RatingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Security\Voter\MissionVoter;

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
        $missions = $missionRepository->findBy(['status' => 'free']);
        return $this->render('front/mission/index.html.twig', [
            'missions' => $missions
        ]);
    }
	
	/**
	 * @param MissionRepository $missionRepository
	 * @return Response
	 */
	#[Route('/my-missions', name: 'mission-my-missions', methods: ['GET'])]
	public function myMissions(MissionRepository $missionRepository): Response
	{
		$user = $this->getUser();
		$missions = $missionRepository->findBy([in_array('ROLE_CLIENT', $user->getRoles()) ? 'client' : 'agent' => $user]);
		return $this->render('front/mission/index.html.twig', [
			'missions' => $missions
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
		
		$payment = $paymentRepository->findOneBy(['mission' => $mission]);
		
		return $this->render('front/mission/show.html.twig', [
			'mission' => $mission,
			'form' => $form->createView(),
			'rating' => $rating,
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
    #[Route('/{id}/asign/{token}', name: 'mission_asign', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_AGENT')")]
    public function asign(Mission $mission, string $token, MissionRepository $missionRepository): Response
    {
        if (!$this->isCsrfTokenValid('asign' . $mission->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $mission->setAgent($this->getUser());
        $mission->setStatus('in_progress');
        $missionRepository->save($mission, true);

        return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
    }
	
	/**
	 * @param Mission           $mission
	 * @param string            $token
	 * @param MissionRepository $missionRepository
	 *
	 * @return Response
	 */
    #[Route('/{id}/finish/{token}', name: 'mission_finish', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_AGENT')")]
    public function finish(Mission $mission, string $token, MissionRepository $missionRepository): Response
    {
        if (!$this->isCsrfTokenValid('finish' . $mission->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $mission->setStatus('finished');
        $missionRepository->save($mission, true);

        return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
    }
}
