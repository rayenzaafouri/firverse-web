<?php

namespace App\Controller\Front\Shop;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class CartController extends AbstractController
{
    #[Route('shop/cart', name: 'cart_index')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            }
        }

        $total = array_reduce($cartWithData, function ($total, $item) {
            return $total + ($item['product']->getPrice() * $item['quantity']);
        }, 0);

        return $this->render('Front/Shop/cart.html.twig', [
            'cartItems' => $cartWithData,
            'total' => $total,
        ]);
    }

    #[Route('/shop/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getId();

        if (!isset($cart[$id])) {
            $cart[$id] = 1;
        } else {
            $cart[$id]++;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', $product->getName() . ' added to cart.');

        return $this->redirectToRoute('shop_home'); // Redirect to your shop page
    }

    #[Route('/shop/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/shop/cart/increase/{id}', name: 'cart_increase')]
    public function increase(Product $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            $cart[$id]++;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/shop/cart/decrease/{id}', name: 'cart_decrease')]
    public function decrease(Product $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }
    #[Route('/shop/cart/apply-coupon', name: 'cart_apply_coupon', methods: ['POST'])]
    public function applyCoupon(Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {
    $couponCode = $request->request->get('coupon_code');

    if (!$couponCode) {
        $this->addFlash('error', 'Please enter a coupon code.');
        return $this->redirectToRoute('cart_index');
    }

    $coupon = $em->getRepository(\App\Entity\Coupon::class)->findOneBy([
        'code' => $couponCode,
        'is_active' => true,
    ]);

    if (!$coupon) {
        $this->addFlash('error', 'Invalid or inactive coupon.');
        return $this->redirectToRoute('cart_index');
    }

    $now = new \DateTime();
    if ($coupon->getValidFrom() > $now || $coupon->getValidUntil() < $now) {
        $this->addFlash('error', 'This coupon is expired.');
        return $this->redirectToRoute('cart_index');
    }

    // Apply the coupon
    $session->set('coupon', [
        'code' => $coupon->getCode(),
        'discount' => $coupon->getDiscountPercentage()
    ]);

    $this->addFlash('success', 'Coupon applied successfully!');

    return $this->redirectToRoute('cart_index');
}
}
