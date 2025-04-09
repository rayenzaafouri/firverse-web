<?php

namespace App\Controller\Front\Shop;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface; // ADD THIS!

class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'checkout_start', methods: ['POST'])]
public function checkout(SessionInterface $session, ProductRepository $productRepository): Response
{
    Stripe::setApiKey('sk_test_51Qwm6yG2v8TGz3VWDphJSGZigzB30TtZMSFJY7kGJdmlVV616fS2xFvb4q4y7paJvO1D0128jqsaaOIq4IptkXxT0063bpVoGc');

    $cart = $session->get('cart', []);
    $lineItems = [];

    foreach ($cart as $id => $quantity) {
        $product = $productRepository->find($id);
        if (!$product) continue;

        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $product->getName(),
                ],
                'unit_amount' => intval($product->getPrice() * 100),
            ],
            'quantity' => $quantity,
        ];
    }

    $checkoutSession = StripeSession::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => 'http://localhost:8000/checkout/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost:8000/shop/cart',

    ]);

    return $this->redirect($checkoutSession->url);
}
}