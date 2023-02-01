<?php

namespace App\Controller\Front;

use App\Repository\OrderRepository;
use App\Entity\Mission;
use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\EquipmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Route('/order')]
class OrderController extends AbstractController
{
    // #[Route('/order', name: 'app_order')]
    // public function index(): Response
    // {
    //     return $this->render('order/index.html.twig', [
    //         'controller_name' => 'OrderController',
    //     ]);
    // }

    /**
     * @param Mission $mission
     * @return Response
     */
    #[Route('/create/{slug}', name: 'order_create', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_AGENT')")]
    public function create(Mission $mission, Request $request, OrderRepository $orderRepository, EquipmentRepository $equipmentRepository): Response
    {
        $user = $this->getUser();
        $equipments = $equipmentRepository->createQueryBuilder('equipment')
            ->where('equipment.stock > :value')
            ->setParameter('value', 0)
            ->getQuery()
            ->getResult();
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order, [
            'equipments' => $equipments
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->setAgent($user);
            $order->setMission($mission);
            $order->setStatus('in_demand');
            $orderRepository->save($order, true);

            return $this->redirectToRoute('front_mission_show', ['slug' => $mission->getSlug()]);
        }

        return $this->render('front/order/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
