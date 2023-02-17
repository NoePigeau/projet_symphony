<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ChallengeType;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(Request $request): Response
    {
        $challengePassed = $request->getSession()->get('challenge_passed', false);
        if (!$challengePassed) {
            return $this->redirectToRoute('front_default_cat');
        }
        return $this->render('front/default/index.html.twig');
    }

    #[Route('/cut-cats', name: 'default_cat')]
    public function cat(): Response
    {
        return $this->render('front/default/cat.html.twig');
    }

    #[Route('/are-you-worthy', name: 'default_challenge')]
    public function game(Request $request): Response
    {
        $challengePassed = $request->getSession()->get('challenge_passed', false);
        if ($challengePassed) {
            return $this->render('front/default/index.html.twig');
        }

        $form = $this->createForm(ChallengeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $answer = $data['answer'];

            if ($answer === 'feur') {
                $request->getSession()->set('challenge_passed', true);
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('front/default/challenge.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
