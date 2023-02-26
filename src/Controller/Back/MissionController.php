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
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/mission')]
#[Security("is_granted('ROLE_ADMIN')")]
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

    #[Route('/{id}/validate/{validate}/{token}', name: 'mission_validate', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function validate(Mission $mission, MissionRepository $missionRepository, string $validate, string $token, MailerInterface $mailer): Response
    {
        if (!$this->isCsrfTokenValid('validate' . $mission->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        if ($mission->getStatus() == $mission::STATUS_IN_DEMAND) {
            $mission->setStatus($validate == 'true' ? $mission::STATUS_FREE : $mission::STATUS_REFUSED);

            if ($mission->getClient()->getEmailNotify()) {
                $email = (new Email())
                    ->from('mission-bot@kgbytes.com')
                    ->to($mission->getClient()->getEmail());
                    
                if ($validate === 'true') {
                    $email
                        ->subject('Your mission demand has been validated')
                        ->html('<p>You mission ' . $mission->getName() . ' has been validated by our administration. An agent will be soon take care of it !! Thanks to trust our company.</p>');
                } else {
                    $email
                        ->subject('Your mission demand has been refused')
                        ->html('<p>You mission ' . $mission->getName() . ' has been refused by our administration. This mission doesn\'t respect the terms of conditions. Sorry for the disagrement</p>');
                }  
                $mailer->send($email);
            }
        }

        $missionRepository->save($mission, true);

        return $this->redirectToRoute('admin_mission_index');
    }
}
