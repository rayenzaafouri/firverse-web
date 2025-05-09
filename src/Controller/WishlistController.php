<?php

namespace App\Controller;

use App\Entity\Wishlist;
use App\Form\WishlistType;
use App\Repository\WishlistRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
<<<<<<< HEAD

=======
use Knp\Component\Pager\PaginatorInterface;
>>>>>>> shop

#[Route('/wishlist')]
final class WishlistController extends AbstractController{
    #[Route(name: 'app_wishlist_index', methods: ['GET'])]
<<<<<<< HEAD
    public function index(WishlistRepository $repo, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
=======
    public function index(WishlistRepository $repo, CategoryRepository $categoryRepository, ProductRepository $productRepository,PaginatorInterface $paginator,Request $request): Response
>>>>>>> shop
    {
        $user = $this->getUser();
        $wishlists = $repo->findBy(['user' => $user]);

        // Get products from wishlist to display (optional but better UX)
<<<<<<< HEAD
        $products = array_map(fn($w) => $w->getProduct(), $wishlists);

=======
        $productsQuery = $productRepository->createQueryBuilder('p')
        ->join('p.wishlists', 'w')
        ->andWhere('w.user = :user')
        ->setParameter('user', $user)
        ->getQuery();
    
    $products = $paginator->paginate(
        $productsQuery,
        $request->query->getInt('page', 1),
        8
    );
    
>>>>>>> shop
        return $this->render('front/shop/index.html.twig', [
            'wishlists' => $wishlists,
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_wishlist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wishlist = new Wishlist();
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($wishlist);
            $entityManager->flush();

            return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wishlist/new.html.twig', [
            'wishlist' => $wishlist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wishlist_show', methods: ['GET'])]
    public function show(Wishlist $wishlist): Response
    {
        return $this->render('wishlist/show.html.twig', [
            'wishlist' => $wishlist,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_wishlist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wishlist $wishlist, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wishlist/edit.html.twig', [
            'wishlist' => $wishlist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wishlist_delete', methods: ['POST'])]
    public function delete(Request $request, Wishlist $wishlist, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wishlist->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($wishlist);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/wishlist/toggle/{productId}', name: 'wishlist_toggle', methods: ['GET'])]
public function toggle(int $productId, ProductRepository $productRepository, WishlistRepository $wishlistRepository, EntityManagerInterface $em): JsonResponse
{
    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['status' => 'unauthorized'], 403);
    }

    $product = $productRepository->find($productId);
    if (!$product) {
        return new JsonResponse(['status' => 'not found'], 404);
    }

    $wishlist = $wishlistRepository->findOneBy([
        'user' => $user,
        'product' => $product
    ]);

    if ($wishlist) {
        $em->remove($wishlist);
        $status = 'removed';
    } else {
        $wishlist = new Wishlist();
        $wishlist->setUser($user);
        $wishlist->setProduct($product);
        $wishlist->setAddedAt(new \DateTime());
        $em->persist($wishlist);
        $status = 'added';
    }

    $em->flush();

    return new JsonResponse(['status' => $status]);
}

#[Route('/admin/wishlist', name: 'admin_wishlist_index')]
public function adminIndex(WishlistRepository $repo): Response
{
    $wishlists = $repo->createQueryBuilder('w')
        ->join('w.user', 'u')
        ->orderBy('u.last_name', 'ASC')
        ->addOrderBy('u.first_name', 'ASC')
        ->getQuery()
        ->getResult();

    return $this->render('back/shop/wishlist/index.html.twig', [
        'wishlists' => $wishlists,
    ]);
}


}
