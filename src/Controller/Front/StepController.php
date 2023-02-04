<?php

namespace App\Controller\Front;

use App\Entity\Mission;
use App\Entity\Step;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use App\Repository\StepRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Security\Voter\MissionVoter;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/step')]
#[Security("is_granted('ROLE_USER')")]
class StepController extends AbstractController
{
    #[Route('/{slug}/update', name: 'mission_steps_update', methods: ['POST'])]
    #[Security("mission.getClient() == user")]
    public function updateSteps(Mission $mission, Request $request, StepRepository $stepRepository): JsonResponse
    {   
        if (!$mission->getStatus() == $mission::STATUS_FREE && !$mission->getStatus() == $mission::STATUS_IN_DEMAND) {
            return $this->json([], 400);
        }

        if(count($mission->getSteps()) > 0) {
            $stepRepository->deleteByMission($mission->getId());
        }
        $steps = json_decode($request->getContent(), false);
        
        foreach ($steps as $step) {
            $newStep = new Step();
            $newStep
                ->setName($step->name)
                ->setStatus($step->status == "true")
                ->setPosition($step->position)
                ->setMission($mission);

            $stepRepository->save($newStep, true);
        }

        return $this->json([], 200);
    }

    #[Route('/{id}/toggle', name: 'step_toggle_status', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("step.getMission().getClient() == user || step.getMission().getAgent() == user")]
    public function toogleStatus(Step $step, StepRepository $stepRepository): Response
    {
        $step->setStatus(!$step->isStatus());
        $stepRepository->save($step, true);

        return new Response();
    }

    #[Route('/{slug}', name: 'mission_steps', methods: ['GET'])]
    #[IsGranted(MissionVoter::VIEW, 'mission')]
    public function getSteps(Mission $mission): JsonResponse
    {
        $jsonMission = [];
        foreach ($mission->getSteps() as $step) {
            $jsonMission [] = [
                'id' => $step->getId(),
                'status' => $step->isStatus(),
                'name' => $step->getName(),
                'position' => $step->getPosition()
            ];
        }
        return $this->json($jsonMission);
    }  
}
