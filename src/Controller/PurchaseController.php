<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use App\Service\PurchaseService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Handles buying a whole cursus or a single lesson through Stripe (test mode).
#[IsGranted('ROLE_CLIENT')]
class PurchaseController extends AbstractController
{
    #[Route('/purchase/cursus/{id}', name: 'app_purchase_cursus', methods: ['POST'])]
    public function purchaseCursus(
        Cursus $cursus,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        PurchaseService $purchaseService,
        StripeService $stripeService,
    ): RedirectResponse {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('purchase_cursus', $request->request->get('_csrf_token')))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();

        if (!$purchaseService->canPurchase($user)) {
            $this->addFlash('danger', 'Vous devez vérifier votre adresse email avant de pouvoir acheter.');

            return $this->redirectToRoute('app_cursus_show', ['id' => $cursus->getId()]);
        }

        $successUrl = $this->generateUrl('app_purchase_success', ['type' => 'cursus', 'id' => $cursus->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_cursus_show', ['id' => $cursus->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = $stripeService->createCheckoutSession($cursus->getName(), $cursus->getPrice(), $successUrl, $cancelUrl);

        return $this->redirect($session->url);
    }

    #[Route('/purchase/lesson/{id}', name: 'app_purchase_lesson', methods: ['POST'])]
    public function purchaseLesson(
        Lesson $lesson,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        PurchaseService $purchaseService,
        StripeService $stripeService,
    ): RedirectResponse {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('purchase_lesson', $request->request->get('_csrf_token')))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();

        if (!$purchaseService->canPurchase($user)) {
            $this->addFlash('danger', 'Vous devez vérifier votre adresse email avant de pouvoir acheter.');

            return $this->redirectToRoute('app_lesson_show', ['id' => $lesson->getId()]);
        }

        $successUrl = $this->generateUrl('app_purchase_success', ['type' => 'lesson', 'id' => $lesson->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_lesson_show', ['id' => $lesson->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = $stripeService->createCheckoutSession($lesson->getTitle(), $lesson->getPrice(), $successUrl, $cancelUrl);

        return $this->redirect($session->url);
    }

    #[Route('/purchase/success', name: 'app_purchase_success')]
    public function success(
        Request $request,
        StripeService $stripeService,
        PurchaseService $purchaseService,
        CursusRepository $cursusRepository,
        LessonRepository $lessonRepository,
    ): Response {
        $sessionId = $request->query->get('session_id');
        $type = $request->query->get('type');
        $id = (int) $request->query->get('id');

        if (!$sessionId || !$stripeService->isSessionPaid($sessionId)) {
            $this->addFlash('danger', 'Le paiement n\'a pas pu être vérifié.');

            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();

        if ($type === 'cursus') {
            $cursus = $cursusRepository->find($id);

            if (!$cursus) {
                throw $this->createNotFoundException('Cursus introuvable.');
            }

            $purchaseService->recordCursusPurchase($user, $cursus);

            return $this->render('purchase/success.html.twig', ['label' => $cursus->getName()]);
        }

        $lesson = $lessonRepository->find($id);

        if (!$lesson) {
            throw $this->createNotFoundException('Leçon introuvable.');
        }

        $purchaseService->recordLessonPurchase($user, $lesson);

        return $this->render('purchase/success.html.twig', ['label' => $lesson->getTitle()]);
    }
}
