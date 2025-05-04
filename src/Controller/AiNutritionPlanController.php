<?php
// src/Controller/AiNutritionPlanController.php

namespace App\Controller;

use App\Form\NutritionPreferencesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class AiNutritionPlanController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, string $geminiApiKey, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->apiKey     = $geminiApiKey;
        $this->logger     = $logger;
    }

    #[Route('/nutrition/preferences', name: 'app_nutrition_preferences')]
    public function preferences(Request $request): Response
    {
        $form = $this->createForm(NutritionPreferencesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('Form submitted and valid');
            $data = $form->getData();
            $sum  = $data['proteinPct'] + $data['fatPct'] + $data['carbPct'];

            if ($sum !== 100) {
                $this->addFlash('error', 'Protein + Fat + Carbs must equal 100%. You currently have ' . $sum . '%.');
            } else {
                $prompt = sprintf(
                    "Generate a detailed %s nutrition plan for a 2700 kcal daily intake, with %d%% protein, %d%% fat, %d%% carbs, avoiding: %s. 
                    The plan should include 4 meals: breakfast, lunch, dinner, and a snack.
                    For each meal, provide:
                    1. Total calories, protein, fat, and carbs for the meal
                    2. List of foods with their:
                       - Quantity/serving size
                       - Calories
                       - Protein (g)
                       - Carbs (g)
                       - Fat (g)
                    Format the response as a JSON object with this structure:
                    {
                        'breakfast': {
                            'total': {'calories': X, 'protein': X, 'carbs': X, 'fat': X},
                            'items': [
                                {'name': 'Food Name', 'quantity': 'X g/ml', 'calories': X, 'protein': X, 'carbs': X, 'fat': X},
                                ...
                            ]
                        },
                        'lunch': {...},
                        'dinner': {...},
                        'snack': {...}
                    }
                    Make sure the total daily calories add up to 2700 kcal and the macronutrient percentages match the requested distribution.
                    Return ONLY the JSON object, without any markdown formatting or additional text.",
                    $data['diet'],
                    $data['proteinPct'],
                    $data['fatPct'],
                    $data['carbPct'],
                    $data['dislikes'] ?: 'none'
                );

                try {
                    $this->logger->info('Making API request to Gemini');
                    $response = $this->httpClient->request(
                        'POST',
                        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
                        [
                            'query' => ['key' => $this->apiKey],
                            'json'  => [
                                'contents' => [
                                    ['parts' => [['text' => $prompt]]]
                                ]
                            ],
                        ]
                    );

                    $this->logger->info('Received API response');
                    $body = $response->toArray();
                    
                    // Debug the response structure
                    if (!isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                        $this->logger->error('Unexpected API response structure', ['response' => $body]);
                        $this->addFlash('error', 'Unexpected API response structure. Please try again.');
                        return $this->render('Front/Nutrition/preferences.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $content = $body['candidates'][0]['content']['parts'][0]['text'];
                    $this->logger->info('Extracted content from API response');
                    
                    if (empty($content)) {
                        $this->logger->error('Empty content received from API');
                        throw new \Exception('Empty response received from the API');
                    }

                    // Clean the content by removing markdown formatting
                    $content = preg_replace('/```json\n|\n```/', '', $content);
                    $content = trim($content);

                    // Try to parse the content as JSON
                    $planData = json_decode($content, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->logger->error('Invalid JSON in API response', ['content' => $content]);
                        $this->addFlash('error', 'Invalid response format from the API. Please try again.');
                        return $this->render('Front/Nutrition/preferences.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $this->logger->info('Successfully parsed nutrition plan');
                    // Store the plan in the session
                    $request->getSession()->set('nutrition_plan', $planData);
                    
                    // Redirect to the plan page
                    $this->logger->info('Redirecting to plan page');
                    return $this->redirectToRoute('app_nutrition_plan');
                } catch (\Exception $e) {
                    $this->logger->error('Error generating nutrition plan', ['error' => $e->getMessage()]);
                    $this->addFlash('error', 'Could not generate plan: ' . $e->getMessage());
                }
            }
        }

        return $this->render('Front/Nutrition/preferences.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/nutrition/plan', name: 'app_nutrition_plan')]
    public function plan(Request $request): Response
    {
        $plan = $request->getSession()->get('nutrition_plan');

        if (!$plan) {
            $this->logger->warning('No nutrition plan found in session');
            $this->addFlash('warning', 'No nutrition plan found. Please generate a new plan.');
            return $this->redirectToRoute('app_nutrition_preferences');
        }

        $this->logger->info('Displaying nutrition plan');
        return $this->render('Front/Nutrition/plan.html.twig', [
            'plan' => $plan,
        ]);
    }
}
