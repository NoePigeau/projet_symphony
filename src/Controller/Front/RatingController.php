<?php
	namespace App\Controller\Front;
	
	use App\Entity\Mission;
	use App\Entity\Rating;
	use App\Form\RatingType;
	use App\Repository\RatingRepository;
	use Symfony\Component\HttpFoundation\Request;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Routing\Annotation\Route;
	
	class RatingController extends AbstractController {
		/**
		 * @Route("/mission/{slug}", name="rating_submit")
		 * @param Request          $request
		 * @param Mission          $mission
		 * @param RatingRepository $ratingRepository
		 *
		 * @return Response
		 */
		#[Route('/mission/{slug}', name: 'rating_submit', methods: ['GET'])]
		#[Security("is_granted('ROLE_CLIENT')")]
		public function submitAction(Request $request, Mission $mission, RatingRepository $ratingRepository): Response
		{
			$rating = new Rating();
			$form = $this->createForm(RatingType::class, $rating);
			
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$rating->setMission($mission);
				$rating->setAgent($mission->getAgent());
				$ratingRepository->save($rating, true);
				
				return $this->redirectToRoute('front_mission-my-missions');
			}
			
			return $this->render('front/mission/show.html.twig', [
				'mission' => $mission,
				'form' => $form->createView(),
			]);
		}
	}
