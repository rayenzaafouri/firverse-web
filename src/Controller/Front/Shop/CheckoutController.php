<?php

namespace App\Controller\Front\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use App\Repository\ProductRepository;
use App\Entity\Product;

class CheckoutController extends AbstractController
{
    private const CART_SESSION_KEY = 'cart';

    #[Route('/shop/cart/checkout', name: 'checkout', methods: ['POST'])]
public function checkout(Request $request, SessionInterface $session, ProductRepository $productRepository): Response
{
    // CSRF protection
    $submittedToken = $request->request->get('token');
    if (!$this->isCsrfTokenValid('checkout', $submittedToken)) {
        throw $this->createAccessDeniedException('Invalid CSRF token');
    }

    $cart = $session->get('cart', []);
    $customerEmail = $this->getUser() ? $this->getUser()->getEmail() : 'test@example.com';

    if (empty($cart)) {
        $this->addFlash('warning', 'Your cart is empty');
        return $this->redirectToRoute('cart_index');
    }

    Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

    $lineItems = [];
    foreach ($cart as $id => $quantity) {
        $product = $productRepository->find($id);
        if (!$product) continue;
        
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd', // or 'tnd' if supported
                'product_data' => [
                    'name' => $product->getName(),
                ],
                'unit_amount' => (int) ($product->getPrice() * 100), // *1000 for TND
            ],
            'quantity' => $quantity,
        ];
    }

    try {
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl(
                'checkout_success',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'cancel_url' => $this->generateUrl(
                'checkout_cancel',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'customer_email' => $customerEmail,
        ]);

        return $this->redirect($checkoutSession->url);

    } catch (ApiErrorException $e) {
        $this->addFlash('error', 'Payment error: '.$e->getMessage());
        return $this->redirectToRoute('cart_index');
    }
}
}