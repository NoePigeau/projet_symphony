<?php
namespace App\Controller\Front;

use App\Entity\Mission;
use App\Entity\Payment;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
	/**
	 * @Route("/pay/{missionId}", name="pay")
	 * @throws ApiErrorException
	 */
	#[Route('/pay/{missionId}', name: 'pay', methods: ['GET'])]
	#[Security("is_granted('ROLE_CLIENT')")]
	public function pay(EntityManagerInterface $entityManager, int $missionId): Response
	{
		// Retrieve the mission from the database using the missionId
		$mission = $entityManager->getRepository(Mission::class)->find($missionId);
		
		// Check if the mission already exists in payment table
		$payment = $entityManager->getRepository(Payment::class)->findOneBy(['mission' => $mission]);
		if ($payment){
			// If the mission already exists in payment table, delete it
			$entityManager->remove($payment);
			$entityManager->flush();
		}
		
		// Create a new payment object
		$payment = new Payment();
		$payment->setMission($mission);
		$payment->setAmount($mission->getReward() * 100);
		$payment->setStatus('unpaid');
		$payment->setCreatedAt(new DateTime());
		$payment->setUpdatedAt(new DateTime());
		
		// Save the payment to the database
		$entityManager->persist($payment);
		$entityManager->flush();
		
		// Configure the Stripe client
		$stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
		
		// Create a new Stripe Checkout session
		$session = $stripe->checkout->sessions->create([
           'payment_method_types' => ['card'],
           'line_items' => [[
               'price_data' => [
                   'currency' => 'eur',
                   'unit_amount' => $payment->getAmount(),
                   'product_data' => [
                       'name' => 'Mission Reward',
                       'description' => $mission->getDescription()
                   ],
               ],
               'quantity' => 1,
           ]],
           'mode' => 'payment',
           'success_url' => $this->generateUrl('front_payment_success', ['paymentId' => $payment->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
           'cancel_url' => $this->generateUrl('front_payment_cancel', ['paymentId' => $payment->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
           'metadata' => [
               'payment_id' => $payment->getId(),
               'mission_id' => $mission->getId()
           ]
        ]);
		
		// Save the Stripe session ID to the Payment object and update it in the database
		$payment->setStripePaymentId($session->id);
		$entityManager->persist($payment);
		$entityManager->flush();
		
		// Redirect the user to the Stripe Checkout page
		return $this->redirect($session->url, 303);
	}
	
	/**
	 * @Route("/payment/success", name="payment_success_page")
	 */
	#[Route('/payment/success', name: 'payment_success_page', methods: ['GET'])]
	public function success(): Response
	{
		return $this->render('front/payment/success.html.twig');
	}
	
	/**
	 * @Route("/payment/success/{paymentId}", name="payment_success")
	 * @throws TransportExceptionInterface
	 */
	#[Route('/payment/success/{paymentId}', name: 'payment_success', methods: ['GET'])]
	#[Security("is_granted('ROLE_CLIENT')")]
	public function paymentSuccess(EntityManagerInterface $entityManager, int $paymentId, MailerInterface $mailer): Response
	{
		// Retrieve the payment from the database using the paymentId
		$payment = $entityManager->getRepository(Payment::class)->find($paymentId);
		
		// Update the payment status to "paid"
		$payment->setStatus('paid');
		$entityManager->flush();
		
		$mission = $payment->getMission();
		
		// Send an e-mail to the client to notify him that the payment is successful
		if ($mission->getClient()->getEmailNotify()) {
			$emailToClient = (new Email())
				->from('mission-bot@kgbytes.com')
				->to($mission->getClient()->getEmail())
				->subject('You have pad for your mission')
				->html('<p>' . $mission->getClient()->getNickname() . ' You have successfully paid for the mission: ' . $mission->getName() .'. Thank you for choosing us !</p>');
			$mailer->send($emailToClient);
		}
		
		// Send an e-mail to the agent to notify him that the payment is successful
		if ($mission->getAgent()->getEmailNotify()) {
			$emailToAgent = (new Email())
				->from('mission-bot@kgbytes.com')
				->to($mission->getAgent()->getEmail())
				->subject('You have received a payment for your mission')
				->html('<p>' . $mission->getAgent()->getNickname() . ' You have successfully received a payment for the mission: ' . $mission->getName() .'. Keep up the good work !</p>');
			$mailer->send($emailToAgent);
		}
		
		// Redirect the user to the payment success page
		return $this->redirectToRoute('front_payment_success_page');
	}
	
	/**
	 * @Route("/payment/cancel/{paymentId}", name="payment_cancel")
	 */
	#[Route('/payment/cancel/{paymentId}', name: 'payment_cancel', methods: ['GET'])]
	#[Security("is_granted('ROLE_CLIENT')")]
	public function paymentCancel(EntityManagerInterface $entityManager, int $paymentId): Response
	{
		// Retrieve the payment from the database using the paymentId
		$payment = $entityManager->getRepository(Payment::class)->find($paymentId);
		
		// Update the payment status to "cancelled"
		$payment->setStatus('cancelled');
		$entityManager->flush();
		
		// Redirect the user to the payment cancel page
		return $this->redirectToRoute('front_payment_cancel_page');
	}
}