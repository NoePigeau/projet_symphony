<?php

namespace App\Controller\Back;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/certification')]
class CertificationController extends AbstractController
{
    /**
     * @param DocumentRepository $DocumentRepository
     * @return Response
     */
    #[Route('/', name: 'certification_index', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function index(DocumentRepository $DocumentRepository, Request $request): Response
    {
        $certifications = $DocumentRepository->search($request);  

        $adapter = new QueryAdapter($certifications);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            $request->request->get('limit', 10)
        );

        return $this->render('back/certification/index.html.twig', [
            'pager' => $pager,
            'limit' => $request->request->get('limit', 10)
        ]);
    }
    
    #[Route('/{id}', name: 'certification_show', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function show(Document $certificate): Response
    {
        return $this->render('back/certification/show.html.twig', [
            'certificate' => $certificate
        ]);
    }

    #[Route('/{id}/accept/{token}', name: 'certification_accept', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function accept(Document $certification, string $token, DocumentRepository $documentRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $certification->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $certification->getSubmitedBy()->setRoles(['ROLE_AGENT']);

        $documentRepository->remove($certification, true);

        return $this->redirectToRoute('admin_certification_index');
    }

    #[Route('/{id}/delete/{token}', name: 'certification_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function delete(Document $certification, string $token, DocumentRepository $documentRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $certification->getId(), $token)) {
            throw $this->createAccessDeniedException('Error token!');
        }

        $documentRepository->remove($certification, true);

        return $this->redirectToRoute('admin_certification_index');
    }

}
