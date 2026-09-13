<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\CursusRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ThemeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Back-office: reserved to administrators, to manage user accounts,
// content (themes/cursus/lessons) and purchases.
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/admin/content', name: 'app_admin_content')]
    public function content(ThemeRepository $themeRepository): Response
    {
        return $this->render('admin/content.html.twig', [
            'themes' => $themeRepository->findAll(),
        ]);
    }

    #[Route('/admin/purchases', name: 'app_admin_purchases')]
    public function purchases(PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('admin/purchases.html.twig', [
            'purchases' => $purchaseRepository->findAll(),
        ]);
    }

    #[Route('/admin/users/{id}/toggle-admin', name: 'app_admin_user_toggle', methods: ['POST'])]
    public function toggleAdmin(
        User $user,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $this->checkCsrf($request, $csrfTokenManager, 'admin_user_toggle');

        $roles = $user->getRoles();
        $user->setRoles(in_array('ROLE_ADMIN', $roles, true) ? [] : ['ROLE_ADMIN']);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(
        User $user,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $this->checkCsrf($request, $csrfTokenManager, 'admin_user_delete');

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/theme/add', name: 'app_admin_theme_add', methods: ['POST'])]
    public function addTheme(
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $this->checkCsrf($request, $csrfTokenManager, 'admin_theme_add');

        $theme = new Theme();
        $theme->setName($request->request->get('name'));
        $entityManager->persist($theme);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_content');
    }

    #[Route('/admin/cursus/add', name: 'app_admin_cursus_add', methods: ['POST'])]
    public function addCursus(
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
        ThemeRepository $themeRepository,
    ): RedirectResponse {
        $this->checkCsrf($request, $csrfTokenManager, 'admin_cursus_add');

        $theme = $themeRepository->find((int) $request->request->get('themeId'));

        if (!$theme) {
            throw $this->createNotFoundException('Theme not found.');
        }

        $cursus = new Cursus();
        $cursus->setName($request->request->get('name'));
        $cursus->setPrice((float) $request->request->get('price'));
        $cursus->setTheme($theme);
        $entityManager->persist($cursus);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_content');
    }

    #[Route('/admin/lesson/add', name: 'app_admin_lesson_add', methods: ['POST'])]
    public function addLesson(
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
        CursusRepository $cursusRepository,
    ): RedirectResponse {
        $this->checkCsrf($request, $csrfTokenManager, 'admin_lesson_add');

        $cursus = $cursusRepository->find((int) $request->request->get('cursusId'));

        if (!$cursus) {
            throw $this->createNotFoundException('Cursus not found.');
        }

        $lesson = new Lesson();
        $lesson->setTitle($request->request->get('title'));
        $lesson->setPrice((float) $request->request->get('price'));
        $lesson->setContent($request->request->get('content'));
        $lesson->setVideoUrl($request->request->get('videoUrl'));
        $lesson->setCursus($cursus);
        $entityManager->persist($lesson);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_content');
    }

    #[Route('/admin/lesson/{id}/edit', name: 'app_admin_lesson_edit', methods: ['POST'])]
    public function editLesson(
        Lesson $lesson,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $this->checkCsrf($request, $csrfTokenManager, 'admin_lesson_edit');

        $lesson->setTitle($request->request->get('title'));
        $lesson->setPrice((float) $request->request->get('price'));
        $lesson->setContent($request->request->get('content'));
        $lesson->setVideoUrl($request->request->get('videoUrl'));
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_content');
    }

    #[Route('/admin/lesson/{id}/delete', name: 'app_admin_lesson_delete', methods: ['POST'])]
    public function deleteLesson(
        Lesson $lesson,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $this->checkCsrf($request, $csrfTokenManager, 'admin_lesson_delete');

        $entityManager->remove($lesson);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_content');
    }

    private function checkCsrf(Request $request, CsrfTokenManagerInterface $csrfTokenManager, string $tokenId): void
    {
        $token = $request->request->get('_csrf_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken($tokenId, $token))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
