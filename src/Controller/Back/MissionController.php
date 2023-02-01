<?php

namespace App\Controller\Back;

use App\Entity\Mission;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use App\Repository\TypeRepository;
use App\Security\Voter\MissionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
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

        $adapter = new QueryAdapter($missions);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            $request->request->get('limit', 10)
        );

        return $this->render('back/mission/index.html.twig', [
            'types' => $types,
            'pager' => $pager,
            'limit' => $request->request->get('limit', 10)
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
    public function show(Mission $mission): Response
    {
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
    public function delete(Mission $mission, string $token, MissionRepository $missionRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $mission->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $missionRepository->remove($mission, true);

        return $this->redirectToRoute('admin_mission_index');
    }
}
