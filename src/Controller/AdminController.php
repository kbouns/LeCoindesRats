<?php
namespace App\Controller;

use App\Entity\Deal;
use App\Entity\User;
use App\Entity\Vote;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\DealRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_redirect_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function redirectToDashboard(): Response
    {
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/users.html.twig', ['users' => $users]);
    }

    #[Route('/users/edit/{id}', name: 'admin_edit_user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class)
            ->add('username', TextType::class)
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Modifier Utilisateur'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/delete/{id}', name: 'admin_delete_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        // Supprimer d'abord les votes associés à l'utilisateur
        $votes = $entityManager->getRepository(Vote::class)->findBy(['user' => $user]);
        foreach ($votes as $vote) {
            $entityManager->remove($vote);
        }
        
        // Supprimer d'abord les commentaires associés à l'utilisateur
        $comments = $entityManager->getRepository(Comment::class)->findBy(['user' => $user]);
        foreach ($comments as $comment) {
            $entityManager->remove($comment);
        }

        // Supprimer d'abord les deals associés à l'utilisateur
        $deals = $entityManager->getRepository(Deal::class)->findBy(['user' => $user]);
        foreach ($deals as $deal) {
            $entityManager->remove($deal);
        }

        // Ensuite, supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/deals', name: 'admin_deals', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function manageDeals(DealRepository $dealRepository): Response
    {
        $deals = $dealRepository->findAll();
        return $this->render('admin/deals.html.twig', ['deals' => $deals]);
    }

    #[Route('/deals/toggle/{id}', name: 'admin_toggle_deal', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleDeal(Deal $deal, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('toggle' . $deal->getId(), $request->request->get('_token'))) {
            $deal->setIsActive(!$deal->getIsActive());
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('admin_deals');
    }

    #[Route('/deals/edit/{id}', name: 'admin_edit_deal', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editDeal(Deal $deal, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($deal)
            ->add('title', TextType::class)
            ->add('description', TextType::class)
            ->add('initial_price', TextType::class)
            ->add('reduce_price', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Modifier Deal'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($deal);
            $entityManager->flush();
            return $this->redirectToRoute('admin_deals');
        }

        return $this->render('admin/edit_deal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deals/delete/{id}', name: 'admin_delete_deal', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteDeal(Deal $deal, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($deal);
        $entityManager->flush();
        return $this->redirectToRoute('admin_deals');
    }

    #[Route('/categories', name: 'admin_categories', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function manageCategories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('admin/categories.html.twig', ['categories' => $categories]);
    }

    #[Route('/categories/new', name: 'admin_new_categories', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category created successfully.');

            return $this->redirectToRoute('admin_new_categories');
        }

        return $this->render('admin/new_category.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/edit/{id}', name: 'admin_edit_category', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($category)
            ->add('nameCategory', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Modifier Catégorie'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/edit_category.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/delete/{id}', name: 'admin_delete_category', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_categories');
    }



    #[Route('/comments', name: 'admin_comments', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function manageComments(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findAll();
        return $this->render('admin/comments.html.twig', ['comments' => $comments]);
    }

    #[Route('/comments/edit/{id}', name: 'admin_edit_comment', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editComment(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($comment)
            ->add('content', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Modifier Commentaire'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('admin_comments');
        }

        return $this->render('admin/edit_comment.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/comments/delete/{id}', name: 'admin_delete_comment', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteComment(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();
        return $this->redirectToRoute('admin_comments');
    }

    #[Route('/votes', name: 'admin_votes', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function manageVotes(VoteRepository $voteRepository): Response
    {
        $votes = $voteRepository->findAll();
        return $this->render('admin/votes.html.twig', ['votes' => $votes]);
    }

    #[Route('/votes/delete/{id}', name: 'admin_delete_vote', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteVote(Vote $vote, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($vote);
        $entityManager->flush();
        return $this->redirectToRoute('admin_votes');
    }
}
