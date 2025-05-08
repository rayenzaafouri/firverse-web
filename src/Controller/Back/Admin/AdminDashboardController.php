<?php

namespace App\Controller\Back\Admin;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $today = new \DateTime();
        $startDate = (clone $today)->modify('-29 days')->setTime(0, 0);

        // Fetch basic stats
        $totalProducts = $em->getRepository(Product::class)->count([]);
        $totalOrders = $em->getRepository(Order::class)->count([]);

        $totalRevenue = (float) $em->createQuery('SELECT SUM(o.total_price) FROM App\Entity\Order o')->getSingleScalarResult();
        $totalProductsSold = (int) $em->createQuery('SELECT SUM(od.quantity) FROM App\Entity\OrderDetail od')->getSingleScalarResult();

        // Sales per day - native SQL
        $connection = $em->getConnection();
        $sql = "
            SELECT DATE(order_date) AS day, SUM(total_price) AS revenue
            FROM orders
            WHERE order_date >= :startDate
            GROUP BY day
            ORDER BY day ASC
        ";

        $salesData = $connection->executeQuery($sql, [
            'startDate' => $startDate->format('Y-m-d H:i:s')
        ])->fetchAllAssociative();

        $dailySales = [];
        for ($i = 0; $i < 30; $i++) {
            $date = (clone $startDate)->modify("+{$i} days")->format('Y-m-d');
            $dailySales[$date] = 0;
        }

        foreach ($salesData as $sale) {
            $dailySales[$sale['day']] = (float) $sale['revenue'];
        }

        // Top 5 best-selling products - native SQL
        $topProductsSql = "
            SELECT p.name as product, SUM(od.quantity) as quantity
            FROM order_details od
            JOIN product p ON p.id = od.product_id
            JOIN orders o ON o.id = od.order_id
            WHERE o.order_date >= :startDate
            GROUP BY p.id
            ORDER BY quantity DESC
            LIMIT 5
        ";

        $topProducts = $connection->executeQuery($topProductsSql, [
            'startDate' => $startDate->format('Y-m-d H:i:s')
        ])->fetchAllAssociative();

        return $this->render('back/admin/dashboard.html.twig', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalProductsSold' => $totalProductsSold,
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
        ]);
    }
}
