<?php

namespace App\Controller;

use App\Entity\ProductDiscount;
use App\Form\ProductDiscount1Type;
use App\Repository\ProductDiscountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product/discount')]
final class ProductDiscountController extends AbstractController{
    #[Route(name: 'app_product_discount_index', methods: ['GET'])]
    public function index(ProductDiscountRepository $productDiscountRepository): Response
    {
        return $this->render('product_discount/index.html.twig', [
            'product_discounts' => $productDiscountRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_discount_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $productDiscount = new ProductDiscount();
        $form = $this->createForm(ProductDiscount1Type::class, $productDiscount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($productDiscount);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_discount_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product_discount/new.html.twig', [
            'product_discount' => $productDiscount,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_discount_show', methods: ['GET'])]
    public function show(ProductDiscount $productDiscount): Response
    {
        return $this->render('product_discount/show.html.twig', [
            'product_discount' => $productDiscount,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_discount_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductDiscount $productDiscount, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductDiscount1Type::class, $productDiscount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_discount_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product_discount/edit.html.twig', [
            'product_discount' => $productDiscount,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_discount_delete', methods: ['POST'])]
    public function delete(Request $request, ProductDiscount $productDiscount, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productDiscount->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($productDiscount);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_discount_index', [], Response::HTTP_SEE_OTHER);
    }
}
