<?php

namespace App\Controller\Back;

use App\Entity\Mission;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use App\Repository\TypeRepository;
use App\Security\Voter\MissionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mission')]
class MissionController extends AbstractController
{
    /**
     * @param MissionRepository $missionRepository
     * @return Response
     */
    #[Route('/', name: 'mission_index', methods: ['GET', 'POST'])]
    public function index(MissionRepository $missionRepository, TypeRepository $typeRepository, Request $request): Response
    {
        $types = $typeRepository->findAll();

        $missions = $missionRepository->search($request);

        return $this->render('back/mission/index.html.twig', [
            'types' => $types,
            'missions' => $missions
        ]);
    }

    /**
     * @param Mission $mission
     * @param string $position
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/{id}/sortable/{position}', name: 'mission_sortable', requirements: ['position' => 'UP|DOWN'], methods: ['GET'])]
    public function sortable(Mission $mission, string $position, EntityManagerInterface $manager): Response
    {
        $position === 'DOWN' ? $mission->setPosition($mission->getPosition() +1) : $mission->setPosition($mission->getPosition() -1);
        $manager->flush();

        return $this->redirectToRoute('admin_mission_index');
    }

    /**
     * @param Mission $mission
     * @return Response
     */
    #[Route('/create', name: 'mission_create', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ORGANIZER')")]
    public function create(Request $request, MissionRepository $missionRepository): Response
    {
        $mission = new Mission();
        $form = $this->createForm(MissionType::class, $mission);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $missionRepository->save($mission, true);

            return $this->redirectToRoute('admin_mission_show', ['slug' => $mission->getSlug()]);
        }

        return $this->render('back/mission/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Mission $mission
     * @return Response
     */
    #[Route('/{id}/update', name: 'mission_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(Mission $mission, Request $request, MissionRepository $missionRepository): Response
    {
        $form = $this->createForm(MissionType::class, $mission);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $missionRepository->save($mission, true);

            return $this->redirectToRoute('admin_mission_show', ['slug' => $mission->getId()]);
        }

        return $this->render('back/mission/update.html.twig', [
            'form' => $form->createView(),
            'mission' => $mission
        ]);
    }

    /**
     * @param Mission $mission
     * @return Response
     */
    #[Route('/{slug}', name: 'mission_show', methods: ['GET'])]
    /** #[IsGranted(MissionVoter::VIEW, 'mission')]  */
    #[Security('is_granted("ROLE_ORGANIZER") or mission.getParticipants().contains(user)')]
    public function show(Mission $mission): Response
    {
        //$this->denyAccessUnlessGranted(MissionVoter::VIEW, $mission);

        return $this->render('back/mission/show.html.twig', [
            'mission' => $mission
        ]);
    }

    /**
     * @param Mission $mission
     * @param $token
     * @param MissionRepository $missionRepository
     * @return Response
     */
    #[Route('/{id}/delete/{token}', name: 'mission_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ORGANIZER')")]
    public function delete(Mission $mission, string $token, MissionRepository $missionRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $mission->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $missionRepository->remove($mission, true);

        return $this->redirectToRoute('admin_mission_index');
    }
}
