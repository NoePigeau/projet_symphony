<?php

namespace App\Controller\Front;

use App\Entity\Mission;
use App\Repository\MissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    #[Route('/{slug}', name: 'mission_show', methods: ['GET'])]
    #[Security("is_granted('ROLE_ORGANIZER') or mission.getParticipants().contains(user)")]
    public function show(Mission $mission): Response
    {
        //$this->denyAccessUnlessGranted(MissionVoter::VIEW, $mission);

        return $this->render('front/mission/show.html.twig', [
            'mission' => $mission
        ]);
    }
}
