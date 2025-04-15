<?php

namespace App\Controller\Front\Shop;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

#[Route('/shop/cart')]
class CheckoutController extends AbstractController
{
    private const CART_SESSION_KEY = 'cart';
    private const CURRENCY = 'usd';

    public function __construct(
        private string $stripeSecretKey,
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager // ✅ Inject EntityManager too
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    #[Route('/checkout', name: 'checkout')]
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
        $lineItems = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            if (!$product) continue;

            $lineItems[] = [
                'price_data' => [
                    'currency' => self::CURRENCY,
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => (int)($product->getPrice() * 100),
                ],
                'quantity' => $quantity,
            ];
        }

        return $lineItems;
    }

    #[Route('/checkout/success', name: 'checkout_success')]
    public function success(SessionInterface $session): Response
    {
        $cart = $session->get(self::CART_SESSION_KEY, []);

        if (!empty($cart)) {
            $order = new Order();
            $order->setOrderDate(new \DateTime());
            $order->setStatus('paid');

            // Set the User if logged in
            if ($this->getUser()) {
                $order->setUser($this->getUser());
            }

            $totalPrice = 0;

            foreach ($cart as $id => $quantity) {
                $product = $this->productRepository->find($id);
                if (!$product) continue;

                $orderDetail = new OrderDetail();
                $orderDetail->setOrder($order);
                $orderDetail->setProduct($product);
                $orderDetail->setQuantity($quantity);
                $orderDetail->setPriceAtPurchase($product->getPrice());

                $this->entityManager->persist($orderDetail);

                $totalPrice += $product->getPrice() * $quantity;
            }

            $order->setTotalPrice($totalPrice);

            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }

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
                        'currency' => self::CURRENCY,
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
