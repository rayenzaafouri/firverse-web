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
use Stripe\Checkout\Session as StripeSession;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/shop/cart')]
class CheckoutController extends AbstractController
{
    private const CART_SESSION_KEY = 'cart';
    private const CURRENCY         = 'usd';   // ← Charge in Tunisian dinar

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
        if (! $this->isCsrfTokenValid('checkout', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('cart_index');
        }

        $cart   = $session->get(self::CART_SESSION_KEY, []);
        $coupon = $session->get('coupon');

        if (empty($cart)) {
            $this->addFlash('warning', 'Your cart is empty.');
            return $this->redirectToRoute('cart_index');
        }

        // Build Stripe line items with both product & coupon discounts
        $lineItems = $this->createLineItems($cart, $coupon);

        // Create the stripe checkout session
        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $this->generateUrl('checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $this->generateUrl('checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'customer_email'       => $this->getUser()?->getEmail(),
        ]);

        return $this->redirect($checkoutSession->url);
    }

    private function createLineItems(array $cart, ?array $coupon): array
    {
        $lineItems = [];

        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (! $product) {
                continue;
            }

            // 1) Base price
            $unitPrice = $product->getPrice();

            // 2) Apply product-level discount if any
            $discounts = $product->getProductDiscounts();
            if (! $discounts->isEmpty()) {
                $pd = $discounts->first();
                $unitPrice *= (1 - $pd->getDiscountPercentage() / 100);
            }

            // 3) Apply coupon discount on top
            if (! empty($coupon['discount'])) {
                $unitPrice *= (1 - $coupon['discount'] / 100);
            }

            // Stripe wants the amount in the smallest currency unit
            $unitAmount = (int) round($unitPrice * 100);

            $lineItems[] = [
                'price_data' => [
                    'currency'     => self::CURRENCY,
                    'product_data' => ['name' => $product->getName()],
                    'unit_amount'  => $unitAmount,
                ],
                'quantity' => $quantity,
            ];
        }

        return $lineItems;
    }

    #[Route('/checkout/success', name: 'checkout_success')]
    public function success(SessionInterface $session): Response
    {
        if (! $session->has(self::CART_SESSION_KEY)) {
            return $this->redirectToRoute('cart_index');
        }

        $cart     = $session->get(self::CART_SESSION_KEY, []);
        $coupon   = $session->get('coupon');
        $discount = $coupon['discount'] ?? 0;

        if (! empty($cart)) {
            // 1) Build & save Order
            $order = new Order();
            $order->setOrderDate(new \DateTime());
            $order->setStatus('paid');
            if ($this->getUser()) {
                $order->setUser($this->getUser());
            }

            $totalPrice  = 0;
            $orderDetails = [];

            foreach ($cart as $productId => $quantity) {
                $product = $this->productRepository->find($productId);
                if (! $product) {
                    continue;
                }

                // Mirror Stripe pricing: product discount then coupon
                $unitPrice = $product->getPrice();
                $discounts = $product->getProductDiscounts();
                if (! $discounts->isEmpty()) {
                    $pd = $discounts->first();
                    $unitPrice *= (1 - $pd->getDiscountPercentage() / 100);
                }
                if ($discount > 0) {
                    $unitPrice *= (1 - $discount / 100);
                }

                $detail = new OrderDetail();
                $detail->setOrder($order);
                $detail->setProduct($product);
                $detail->setQuantity($quantity);
                $detail->setPriceAtPurchase($unitPrice);

                $this->entityManager->persist($detail);
                $orderDetails[] = $detail;
                $totalPrice    += $unitPrice * $quantity;
            }

            $order->setTotalPrice($totalPrice);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            // 2) Clear session
            $session->remove(self::CART_SESSION_KEY);
            $session->remove('coupon');

            // 3) Send confirmation emails
            $emailContent = $this->renderView('emails/order_confirmation.html.twig', [
                'order'        => $order,
                'orderDetails' => $orderDetails,
                'user'         => $this->getUser(),
            ]);

            $customerEmail = (new Email())
                ->from('boutarhamza32@gmail.com')
                ->to($this->getUser()->getEmail())
                ->subject('FitVerse - Order Confirmation #' . $order->getId())
                ->html($emailContent);

<<<<<<< HEAD
            $adminEmail = (new Email())
                ->from('boutarhamza32@gmail.com')
                ->to('boutarhamza32@gmail.com')
                ->subject('New Order #' . $order->getId() . ' Received')
                ->html($emailContent);

            try {
                $this->mailer->send($customerEmail);
                $this->mailer->send($adminEmail);
=======
            try {
                $this->mailer->send($customerEmail);
>>>>>>> shop
            } catch (\Exception $e) {
                error_log('Email sending failed: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Order completed! Check your email for confirmation.');
        }

        return $this->render('front/shop/success.html.twig');
    }

    #[Route('/checkout/cancel', name: 'checkout_cancel')]
    public function cancel(): Response
    {
        return $this->render('front/shop/cancel.html.twig');
    }

    #[Route('/stripe-test')]
    public function testStripe(): Response
    {
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => self::CURRENCY,
                    'product_data' => ['name' => 'Test Item'],
                    'unit_amount'  => 1000,
                ],
                'quantity' => 1,
            ]],
            'mode'       => 'payment',
            'success_url'=> 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ]);

        return $this->redirect($session->url);
    }
}
