<?php

namespace App\Controller\Front;

use App\Entity\Mission;
use App\Entity\Rating;
use App\Form\MissionType;
use App\Form\RatingType;
use App\Repository\MissionRepository;
use App\Repository\RatingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
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
	 * @param Mission          $mission
	 * @param RatingRepository $ratingRepository
	 *
	 * @return Response
	 */
	#[Route('/{slug}', name: 'mission_show', methods: ['GET'])]
	#[IsGranted(MissionVoter::VIEW, 'mission')]
	public function show(Mission $mission, RatingRepository $ratingRepository): Response
	{
		$rating = $ratingRepository->findOneBy(['mission' => $mission]);
		$form = $this->createForm(RatingType::class, $rating ?: new Rating());
		
		return $this->render('front/mission/show.html.twig', [
			'mission' => $mission,
			'form' => $form->createView(),
			'rating' => $rating
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
	
	/**
	 * @throws ApiErrorException
	 */
	public function StripeCheckout(){
		require_once '../../../vendor/autoload.php';
		
		Stripe::setApiKey($_ENV["STRIPE_PUBLIC_KEY"]);
		header('Content-Type: application/json');
		
		$YOUR_DOMAIN = 'http://localhost';
		
		$checkout_session = Session::create([
			'line_items' => [[
				# Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
				'price' => '{{PRICE_ID}}',
				'quantity' => 1,
			]],
			'mode' => 'payment',
			'success_url' => $YOUR_DOMAIN . '/success.html',
			'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
		]);
		
		header("HTTP/1.1 303 See Other");
		header("Location: " . $checkout_session->url);
	}
}
