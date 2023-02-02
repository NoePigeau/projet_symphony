<?php

namespace App\Controller\Back;

use App\Entity\Type;
use App\Form\TypeType;
use App\Repository\TypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/type')]
#[Security("is_granted('ROLE_ADMIN')")]
class TypeController extends AbstractController
{
    #[Route('/', name: 'type_index', methods: ['GET'])]
    public function index(TypeRepository $typeRepository, Request $request): Response
    {
        $types = $typeRepository->search($request);  
        $adapter = new QueryAdapter($types);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            $request->request->get('limit', 10)
        );
        
        return $this->render('back/type/index.html.twig', [
            'pager' => $pager,
            'limit' => $request->request->get('limit', 10)
        ]);
    }

    #[Route('/create', name: 'type_create', methods: ['GET', 'POST'])]
    public function new(Request $request, TypeRepository $typeRepository): Response
    {
        $type = new Type();
        $form = $this->createForm(TypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeRepository->save($type, true);

            return $this->redirectToRoute('admin_type_index');
        }

        return $this->renderForm('back/type/create.html.twig', [
            'type' => $type,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'type_show', methods: ['GET'])]
    public function show(Type $type): Response
    {
        return $this->render('back/type/show.html.twig', [
            'type' => $type,
        ]);
    }

    #[Route('/{slug}/update', name: 'type_update', methods: ['GET', 'POST'])]
    public function edit(Request $request, Type $type, TypeRepository $typeRepository): Response
    {
        $form = $this->createForm(TypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeRepository->save($type, true);

            return $this->redirectToRoute('admin_type_index');
        }

        return $this->renderForm('back/type/update.html.twig', [
            'type' => $type,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete/{token}', name: 'type_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(Request $request, string $token, Type $type, TypeRepository $typeRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $type->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $typeRepository->remove($type, true);

        return $this->redirectToRoute('admin_type_index');
    }
}
