<?php

namespace App\Controller\Front;

use App\Entity\Mission;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/mission')]
#[Security("is_granted('ROLE_USER')")]
class MissionController extends AbstractController
{

    /**
     * @param MissionRepository $missionRepository
     * @return Response
     */
    #[Route('/', name: 'mission_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('front/mission/index.html.twig');
    }

    /**
     * @param Mission $mission
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
            $mission->setStatus('free');
            $missionRepository->save($mission, true);

            return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
        }

        return $this->render('front/mission/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Mission $mission
     * @return Response
     */
    #[Route('/{slug}', name: 'mission_show', methods: ['GET'])]
    #[Security("is_granted('ROLE_AGENT')")]
    public function show(Mission $mission): Response
    {
        //$this->denyAccessUnlessGranted(MissionVoter::VIEW, $mission);

        return $this->render('front/mission/show.html.twig', [
            'mission' => $mission
        ]);
    }
}
