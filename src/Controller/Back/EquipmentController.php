<?php

namespace App\Controller\Back;

use App\Entity\Equipment;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/equipment')]
#[Security("is_granted('ROLE_ADMIN')")]
class EquipmentController extends AbstractController
{
    /**
     * @param EquipmentRepository $equipmentRepository
     * @return Response
     */
    #[Route('/', name: 'equipment_index', methods: ['GET', 'POST'])]
    public function index(EquipmentRepository $equipmentRepository, Request $request): Response
    {
        $equipments = $equipmentRepository->search($request);  
        $adapter = new QueryAdapter($equipments);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            $request->request->get('limit', 10)
        );
        
        return $this->render('back/equipment/index.html.twig', [
            'pager' => $pager,
            'limit' => $request->request->get('limit', 10)
        ]);
    }

    #[Route('/create', name: 'equipment_create')]
    public function create(Request $request, EquipmentRepository $equipmentRepository): Response
    {
        $equipment = new Equipment();
        $form = $this->createForm(EquipmentType::class, $equipment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $equipmentRepository->save($equipment, true);

            return $this->redirectToRoute('admin_equipment_show', ['slug' => $equipment->getSlug()]);
        }

        return $this->render('back/equipment/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

     /**
     * @param Equipment $equipment
     * @return Response
     */
    #[Route('/{slug}/update', name: 'equipment_update', methods: ['GET', 'POST'])]
    public function update(Equipment $equipment, Request $request, EquipmentRepository $equipmentRepository): Response
    {
        $form = $this->createForm(EquipmentType::class, $equipment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $equipmentRepository->save($equipment, true);

            return $this->redirectToRoute('admin_equipment_show', ['slug' => $equipment->getSlug()]);
        }

        return $this->render('back/equipment/update.html.twig', [
            'form' => $form->createView(),
            'equipment' => $equipment
        ]);
    }

    /**
     * @param Equipment $equipment
     * @return Response
     */
    #[Route('/{slug}', name: 'equipment_show', methods: ['GET'])]
    /** #[IsGranted(MissionVoter::VIEW, 'mission')]  */
    public function show(Equipment $equipment): Response
    {
        //$this->denyAccessUnlessGranted(MissionVoter::VIEW, $mission);

        return $this->render('back/equipment/show.html.twig', [
            'equipment' => $equipment
        ]);
    }

    /**
     * @param Equipment $equipment
     * @param $token
     * @param EquipmentRepository $equipmentRepository
     * @return Response
     */
    #[Route('/{id}/delete/{token}', name: 'equipment_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(Equipment $equipment, string $token, EquipmentRepository $equipmentRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $equipment->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $equipmentRepository->remove($equipment, true);

        return $this->redirectToRoute('admin_equipment_index');
    }
}
