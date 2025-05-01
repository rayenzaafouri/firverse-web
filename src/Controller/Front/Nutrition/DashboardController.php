<?php

namespace App\Controller\Front\Nutrition;

use App\Repository\NutritionRepository;
use App\Repository\WaterconsumptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ColumnChart;

#[Route('/nutrition/user/dashboard')]
class DashboardController extends AbstractController
{
    private const DEFAULT_USER_ID = 33;
    private const DAYS_TO_SHOW = 7;

    public function __construct(
        private NutritionRepository $nutritionRepository,
        private WaterconsumptionRepository $waterRepository
    ) {}

    #[Route('/', name: 'app_nutrition_dashboard')]
    public function index(): Response
    {
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->modify('-' . (self::DAYS_TO_SHOW - 1) . ' days');

        // Get nutrition data for the last 7 days
        $nutritionData = $this->nutritionRepository->findByDateRange(
            self::DEFAULT_USER_ID,
            $startDate,
            $endDate
        );

        // Get water consumption data for the last 7 days
        $waterData = $this->waterRepository->findByDateRange(
            self::DEFAULT_USER_ID,
            $startDate,
            $endDate
        );

        // 1. Caloric Intake Line Chart
        $caloricChart = new LineChart();
        $caloricChart->getData()->setArrayToDataTable([
            ['Date', 'Calories'],
            ...array_map(function($day) {
                return [
                    $day['date']->format('M d'),
                    $day['total_calories']
                ];
            }, $nutritionData)
        ]);
        $caloricChart->getOptions()->setTitle('Daily Caloric Intake');
        $caloricChart->getOptions()->setHeight(400);
        $caloricChart->getOptions()->setWidth(800);
        $caloricChart->getOptions()->getHAxis()->setTitle('Date');
        $caloricChart->getOptions()->getVAxis()->setTitle('Calories');

        // 2. Macronutrient Distribution Donut Chart
        $lastDay = end($nutritionData);
        $macronutrientChart = new PieChart();
        $macronutrientChart->getData()->setArrayToDataTable([
            ['Macronutrient', 'Percentage'],
            ['Protein', $lastDay['total_protein'] * 4 / $lastDay['total_calories'] * 100],
            ['Carbs', $lastDay['total_carbs'] * 4 / $lastDay['total_calories'] * 100],
            ['Fats', $lastDay['total_fats'] * 9 / $lastDay['total_calories'] * 100]
        ]);
        $macronutrientChart->getOptions()->setTitle('Macronutrient Distribution');
        $macronutrientChart->getOptions()->setPieHole(0.4);
        $macronutrientChart->getOptions()->setHeight(400);
        $macronutrientChart->getOptions()->setWidth(800);
        $macronutrientChart->getOptions()->setColors(['#4285F4', '#34A853', '#FBBC05']);

        // 3. Macronutrient Bar Chart
        $macronutrientBarChart = new ColumnChart();
        $macronutrientBarChart->getData()->setArrayToDataTable([
            ['Date', 'Protein', 'Carbs', 'Fats'],
            ...array_map(function($day) {
                return [
                    $day['date']->format('M d'),
                    $day['total_protein'],
                    $day['total_carbs'],
                    $day['total_fats']
                ];
            }, $nutritionData)
        ]);
        $macronutrientBarChart->getOptions()->setTitle('Daily Macronutrient Intake');
        $macronutrientBarChart->getOptions()->setHeight(400);
        $macronutrientBarChart->getOptions()->setWidth(800);
        $macronutrientBarChart->getOptions()->getHAxis()->setTitle('Date');
        $macronutrientBarChart->getOptions()->getVAxis()->setTitle('Grams');
        $macronutrientBarChart->getOptions()->setColors(['#4285F4', '#34A853', '#FBBC05']);

        // 4. Water Consumption Line Chart
        $waterChart = new LineChart();
        $waterChart->getData()->setArrayToDataTable([
            ['Date', 'Water (ml)'],
            ...array_map(function($day) {
                return [
                    $day['date']->format('M d'),
                    $day['amount']
                ];
            }, $waterData)
        ]);
        $waterChart->getOptions()->setTitle('Daily Water Consumption');
        $waterChart->getOptions()->setHeight(400);
        $waterChart->getOptions()->setWidth(800);
        $waterChart->getOptions()->getHAxis()->setTitle('Date');
        $waterChart->getOptions()->getVAxis()->setTitle('Milliliters');

        return $this->render('Front/Nutrition/dashboard.html.twig', [
            'caloricChart' => $caloricChart,
            'macronutrientChart' => $macronutrientChart,
            'macronutrientBarChart' => $macronutrientBarChart,
            'waterChart' => $waterChart
        ]);
    }
} 