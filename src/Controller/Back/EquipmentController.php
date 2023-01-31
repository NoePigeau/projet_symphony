<?php

namespace App\Controller\Back;

use App\Entity\Equipment;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/equipment')]
class EquipmentController extends AbstractController
{
    #[Route('/', name: 'app_equipment')]
    public function index(): Response
    {
        return $this->render('equipment/index.html.twig', [
            'controller_name' => 'EquipmentController',
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
    #[Route('/{slug}', name: 'equipment_show', methods: ['GET'])]
    /** #[IsGranted(MissionVoter::VIEW, 'mission')]  */
    public function show(Equipment $equipment): Response
    {
        //$this->denyAccessUnlessGranted(MissionVoter::VIEW, $mission);

        return $this->render('back/equipment/show.html.twig', [
            'equipment' => $equipment
        ]);
    }
}
