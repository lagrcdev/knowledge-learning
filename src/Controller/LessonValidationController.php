<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Repository\PurchaseRepository;
use App\Service\CertificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CLIENT')]
class LessonValidationController extends AbstractController
{
    #[Route('/lesson/{id}/validate', name: 'app_lesson_validate', methods: ['POST'])]
    public function validate(
        Lesson $lesson,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        PurchaseRepository $purchaseRepository,
        CertificationService $certificationService,
    ): RedirectResponse {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('validate_lesson', $request->request->get('_csrf_token')))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $user = $this->getUser();

        if (!$purchaseRepository->hasAccessToLesson($user, $lesson)) {
            throw $this->createAccessDeniedException('You need to buy this lesson first.');
        }

        $certificationService->validateLesson($user, $lesson);

        $this->addFlash('success', 'Lesson validated.');

        return $this->redirectToRoute('app_lesson_show', ['id' => $lesson->getId()]);
    }
}
