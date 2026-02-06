<?php

declare(strict_types=1);

namespace Disjfa\UserBundle\Controller\Admin;

use Disjfa\UserBundle\Entity\User;
use Disjfa\UserBundle\Form\Type\UserFormType;
use Disjfa\UserBundle\Security\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/admin/user', name: 'disjfa_user_admin_user_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW_ALL, User::class);

        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('@DisjfaUser/admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/create', name: 'disjfa_user_admin_user_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::CREATE, User::class);

        $user = new User();
        $user->setPassword($this->passwordHasher->hashPassword($user, uniqid('', true)));
        $form = $this->createForm(UserFormType::class, $user);

        return $this->handleForm($form, $request, true);
    }

    #[Route('/admin/user/{user}/edit', name: 'disjfa_user_admin_user_edit')]
    public function edit(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::UPDATE, $user);

        $form = $this->createForm(UserFormType::class, $user);

        return $this->handleForm($form, $request, false);
    }

    private function handleForm(FormInterface $form, Request $request, bool $isNew): Response
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'user.flash.user_saved');

            return $this->redirectToRoute('disjfa_user_admin_user_index');
        }

        return $this->render('@DisjfaUser/admin/user/form.html.twig', [
            'form' => $form->createView(),
            'user' => $form->getData(),
            'is_new' => $isNew,
        ]);
    }
}
