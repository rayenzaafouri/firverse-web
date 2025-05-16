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
    private const DEFAULT_USER_ID = 100;
    private const DEFAULT_DAYS = 7;
    private const TWO_WEEKS = 14;
    private const ONE_MONTH = 30;

    public function __construct(
        private NutritionRepository $nutritionRepository,
        private WaterconsumptionRepository $waterRepository
    ) {}

    #[Route('/{period}', name: 'app_nutrition_dashboard', defaults: ['period' => '7days'])]
    public function index(string $period): Response
    {
        $endDate = new \DateTime();
        $daysToShow = match($period) {
            '7days' => self::DEFAULT_DAYS,
            '2weeks' => self::TWO_WEEKS,
            '1month' => self::ONE_MONTH,
            default => self::DEFAULT_DAYS
        };

        $startDate = (clone $endDate)->modify('-' . ($daysToShow - 1) . ' days');

        // Get nutrition data for the selected period
        $nutritionData = $this->nutritionRepository->findByDateRange(
            self::DEFAULT_USER_ID,
            $startDate,
            $endDate
        );

        // Get water consumption data for the selected period
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

        return $this->render('Front/Nutrition/dashboard.html.twig', [
            'caloricChart' => $caloricChart,
            'macronutrientChart' => $macronutrientChart,
            'macronutrientBarChart' => $macronutrientBarChart,
            'selectedPeriod' => $period
        ]);
    }
} 