<?php

namespace App\Controller\Back;

use App\Entity\Order;
use App\Repository\EquipmentRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/order')]
class OrderController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/', name: 'order_index', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        $orders = $orderRepository->search($request);  
        $adapter = new QueryAdapter($orders);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            $request->request->get('limit', 10)
        );
        
        return $this->render('back/order/index.html.twig', [
            'pager' => $pager,
            'limit' => $request->request->get('limit', 10)
        ]);
    }

    /**
     * @param Order $order
     * @return Response
     */
    #[Route('/{id}/accept', name: 'order_accept', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function accept(Order $order, OrderRepository $orderRepository, EquipmentRepository $equipmentRepository, MailerInterface $mailer): Response
    {
        $equipment = $order->getEquipment();

        if ($equipment->getStock() < $order->getAmount()) {
            throw new \Exception('Not enough stock to fulfill this order');
        }

        $equipment->setStock($equipment->getStock() - $order->getAmount());
        $order->setStatus('accepted');

        $equipmentRepository->save($equipment, true);
        $orderRepository->save($order, true);

        $email = (new Email())
            ->from('hello@example.com')
            ->to($order->getAgent()->getEmail())
            ->subject('Your order has been accepted')
            // ->text('The order for: ' . $order->getEquipment()->getName() . 'has been accepted and will be delivered to you')
            ->html('<p>The order for: ' . $order->getEquipment()->getName() . ' has been accepted and will be delivered to you</p>');

        $mailer->send($email);

        return $this->redirectToRoute('admin_order_index');
    }

     /**
     * @param Order $order
     * @return Response
     */
    #[Route('/{id}/refuse', name: 'order_refuse', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function refuse(Order $order, OrderRepository $orderRepository, MailerInterface $mailer): Response
    {
        $order->setStatus('refused');

        $orderRepository->save($order, true);

        $email = (new Email())
            ->from('hello@example.com')
            ->to($order->getAgent()->getEmail())
            ->subject('Your order has been accepted')
            // ->text('The order for: ' . $order->getEquipment()->getName() . 'has been accepted and will be delivered to you')
            ->html('<p>The order for: ' . $order->getEquipment()->getName() . ' has been refused. Sorry :(/p>');

        $mailer->send($email);

        return $this->redirectToRoute('admin_order_index');
    }

    /**
     * @param Order $order
     * @param string $token
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/{id}/delete/{token}', name: 'order_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function delete(Order $order, string $token, OrderRepository $orderRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $order->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $orderRepository->remove($order, true);

        return $this->redirectToRoute('admin_order_index');
    }
}
