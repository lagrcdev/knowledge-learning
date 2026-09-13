<?php

namespace App\Controller;

use App\Repository\CursusValidationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CLIENT')]
class CertificationController extends AbstractController
{
    #[Route('/certifications', name: 'app_certifications')]
    public function index(CursusValidationRepository $cursusValidationRepository): Response
    {
        $certifications = $cursusValidationRepository->findByUser($this->getUser());

        return $this->render('certification/index.html.twig', [
            'certifications' => $certifications,
        ]);
    }
}
