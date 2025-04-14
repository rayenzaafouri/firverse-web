<?php

namespace App\Controller\Front\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CheckoutController extends AbstractController
{
    private const CART_SESSION_KEY = 'cart';
    private const CURRENCY = 'usd'; // or 'tnd' if needed

    public function __construct(
        private string $stripeSecretKey,
        private ProductRepository $productRepository
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    #[Route('/shop/cart/checkout', name: 'checkout')]
    public function checkout(SessionInterface $session): Response
    {
        $cart = $session->get(self::CART_SESSION_KEY, []);
        
        if (empty($cart)) {
            $this->addFlash('warning', 'Your cart is empty');
            return $this->redirectToRoute('cart_index');
        }

        try {
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $this->createLineItems($cart),
                'mode' => 'payment',
                'success_url' => $this->generateUrl('checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'customer_email' => $this->getUser()?->getEmail(),
            ]);

            return $this->redirect($checkoutSession->url);

        } catch (ApiErrorException $e) {
            $this->addFlash('error', 'Payment error: '.$e->getMessage());
            return $this->redirectToRoute('cart_index');
        }
    }

    private function createLineItems(array $cart): array
{
    return [
        [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => 'Test Product'],
                'unit_amount' => 1000, // $10.00
            ],
            'quantity' => 1,
        ]
    ];
}

    #[Route('/checkout/success', name: 'checkout_success')]
    public function success(SessionInterface $session): Response
    {
        $session->remove(self::CART_SESSION_KEY);
        return $this->render('Front/Shop/success.html.twig');
    }

    #[Route('/checkout/cancel', name: 'checkout_cancel')]
    public function cancel(): Response
    {
        return $this->render('Front/Shop/cancel.html.twig');
    }
    #[Route('/stripe-test')]
public function testStripe(): Response
{
    try {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Test Item'],
                    'unit_amount' => 1000,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ]);
        
        return $this->redirect($session->url);
    } catch (\Exception $e) {
        return new Response('Stripe Error: '.$e->getMessage());
    }
}
}