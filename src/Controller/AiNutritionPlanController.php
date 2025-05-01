<?php
// src/Controller/AiNutritionPlanController.php

namespace App\Controller;

use App\Form\NutritionPreferencesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

class AiNutritionPlanController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $geminiApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey     = $geminiApiKey;
    }

    #[Route('/nutrition/preferences', name: 'nutrition_preferences')]
    public function preferences(Request $request): Response
    {
        $form = $this->createForm(NutritionPreferencesType::class);
        $form->handleRequest($request);

        $plan = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $sum  = $data['proteinPct'] + $data['fatPct'] + $data['carbPct'];

            if ($sum !== 100) {
                $this->addFlash('error', 'Protein + Fat + Carbs must equal 100%. You currently have ' . $sum . '%.');
            } else {
                $prompt = sprintf(
                    "Generate a %s nutrition plan for a 2700 kcal daily intake, with %d%% protein, %d%% fat, %d%% carbs, avoiding: %s.",
                    $data['diet'],
                    $data['proteinPct'],
                    $data['fatPct'],
                    $data['carbPct'],
                    $data['dislikes'] ?: 'none'
                );

                try {
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

                    $body = $response->toArray();
                    $raw  = $body['candidates'][0]['content'] ?? 'No plan received.';

                    // If Gemini gives us an array, implode it; otherwise leave as-is.
                    if (is_array($raw)) {
                        $plan = implode("\n\n", array_map(
                            fn($piece) => is_string($piece) ? $piece : json_encode($piece),
                            $raw
                        ));
                    } else {
                        $plan = $raw;
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Could not generate plan: ' . $e->getMessage());
                }
            }
        }

        return $this->render('Front/Nutrition/preferences.html.twig', [
            'form' => $form->createView(),
            'plan' => $plan,
        ]);
    }
}
