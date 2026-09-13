<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Repository\LessonValidationRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Public browsing of themes, cursus and lessons.
class CatalogController extends AbstractController
{
    #[Route('/themes', name: 'app_themes')]
    public function themes(ThemeRepository $themeRepository): Response
    {
        return $this->render('catalog/themes.html.twig', [
            'themes' => $themeRepository->findAll(),
        ]);
    }

    #[Route('/cursus/{id}', name: 'app_cursus_show')]
    public function cursus(Cursus $cursus, PurchaseRepository $purchaseRepository): Response
    {
        $user = $this->getUser();
        $hasAccess = $user && $purchaseRepository->hasAccessToCursus($user, $cursus);

        return $this->render('catalog/cursus.html.twig', [
            'cursus' => $cursus,
            'hasAccess' => $hasAccess,
        ]);
    }

    #[Route('/lesson/{id}', name: 'app_lesson_show')]
    public function lesson(
        Lesson $lesson,
        PurchaseRepository $purchaseRepository,
        LessonValidationRepository $lessonValidationRepository,
    ): Response {
        $user = $this->getUser();
        $hasAccess = $user && $purchaseRepository->hasAccessToLesson($user, $lesson);
        $isValidated = $user && $lessonValidationRepository->isLessonValidatedByUser($user, $lesson);

        return $this->render('catalog/lesson.html.twig', [
            'lesson' => $lesson,
            'hasAccess' => $hasAccess,
            'isValidated' => $isValidated,
        ]);
    }
}
