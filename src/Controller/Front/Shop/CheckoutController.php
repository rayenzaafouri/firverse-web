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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/shop/cart')]
class CheckoutController extends AbstractController
{
    private const CART_SESSION_KEY = 'cart';
    private const CURRENCY = 'usd';

    public function __construct(
        private string $stripeSecretKey,
        private string $stripePublicKey,
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer 
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(Request $request, SessionInterface $session): Response
    {
        try {
            // Debug output
            error_log('Checkout method called');
            
            // Validate CSRF token
            $token = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('checkout', $token)) {
                error_log('Invalid CSRF token');
                $this->addFlash('error', 'Invalid token');
                return $this->redirectToRoute('cart_index');
            }

            $cart = $session->get(self::CART_SESSION_KEY, []);
            error_log('Cart contents: ' . json_encode($cart));
            
            if (empty($cart)) {
                error_log('Cart is empty');
                $this->addFlash('warning', 'Your cart is empty');
                return $this->redirectToRoute('cart_index');
            }

            $lineItems = $this->createLineItems($cart);
            error_log('Line items: ' . json_encode($lineItems));

            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'customer_email' => $this->getUser()?->getEmail(),
            ]);

            error_log('Stripe session created: ' . $checkoutSession->id);
            return $this->redirect($checkoutSession->url);

        } catch (\Exception $e) {
            error_log('Error in checkout: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $this->addFlash('error', 'Payment error: ' . $e->getMessage());
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
        if (!$session->has(self::CART_SESSION_KEY)) {
            return $this->redirectToRoute('cart_index');
        }
        
        $cart = $session->get(self::CART_SESSION_KEY, []);

        if (!empty($cart)) {
            try {
                // Create and persist order
                $order = new Order();
                $order->setOrderDate(new \DateTime());
                $order->setStatus('paid');
                $orderDetails = [];

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
                    $orderDetails[] = $orderDetail;

                    $totalPrice += $product->getPrice() * $quantity;
                }

                $order->setTotalPrice($totalPrice);
                $this->entityManager->persist($order);
                $this->entityManager->flush();

                // Clear cart
                $session->remove(self::CART_SESSION_KEY);
                $session->remove('coupon');

                // Prepare email content
                $emailContent = $this->renderView('emails/order_confirmation.html.twig', [
                    'order' => $order,
                    'orderDetails' => $orderDetails,
                    'user' => $this->getUser()
                ]);

                // Send customer email
                $customerEmail = (new Email())
                    ->from('boutarhamza32@gmail.com')
                    ->to($this->getUser()->getEmail())
                    ->subject('FitVerse - Order Confirmation #' . $order->getId())
                    ->html($emailContent);
                
                // Send admin email
                $adminEmail = (new Email())
                    ->from('boutarhamza32@gmail.com')
                    ->to('boutarhamza32@gmail.com')
                    ->subject('New Order #' . $order->getId() . ' Received')
                    ->html($emailContent);

                // Send emails directly
                try {
                    $this->mailer->send($customerEmail);
                    $this->mailer->send($adminEmail);
                } catch (\Exception $e) {
                    error_log('Failed to send email: ' . $e->getMessage());
                    // Continue execution even if email fails
                }

                $this->addFlash('success', 'Order completed successfully! Check your email for confirmation.');

            } catch (\Exception $e) {
                error_log('Error processing order: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                $this->addFlash('error', 'There was an issue processing your order. Please contact support.');
            }
        }

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
