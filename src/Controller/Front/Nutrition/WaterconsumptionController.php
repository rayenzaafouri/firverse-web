<?php

namespace App\Controller\Front\Nutrition;

use App\Entity\Waterconsumption;
use App\Repository\WaterconsumptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/waterconsumption')]
class WaterconsumptionController extends AbstractController
{
    private const DEFAULT_USER_ID = 35; // Hardcoded user id as per requirements

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/{date}', name: 'app_waterconsumption_show', methods: ['GET'])]
    public function show(string $date, WaterconsumptionRepository $waterconsumptionRepository): Response
    {
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            throw $this->createNotFoundException('Invalid date format');
        }

        // Ensure waterconsumption entries for today and the last 9 days
        $today = new \DateTime();
        for ($i = 0; $i < 10; $i++) {
            $loopDate = clone $today;
            $loopDate->modify("-{$i} days");
            $existing = $waterconsumptionRepository->findOneBy([
                'user' => self::DEFAULT_USER_ID,
                'ConsumptionDate' => $loopDate
            ]);
            if (!$existing) {
                $newWater = new Waterconsumption();
                $newWater->setUser($this->entityManager->getReference('App\\Entity\\User', self::DEFAULT_USER_ID));
                $newWater->setConsumptionDate($loopDate);
                $newWater->setAmountConsumed(0);
                $this->entityManager->persist($newWater);
            }
        }
        $this->entityManager->flush();

        $waterconsumption = $waterconsumptionRepository->findOneBy([
            'user' => self::DEFAULT_USER_ID,
            'ConsumptionDate' => $dateObj
        ]);
        if (!$waterconsumption) {
            $waterconsumption = new Waterconsumption();
            $waterconsumption->setUser($this->entityManager->getReference('App\\Entity\\User', self::DEFAULT_USER_ID));
            $waterconsumption->setConsumptionDate($dateObj);
            $waterconsumption->setAmountConsumed(0);
            $this->entityManager->persist($waterconsumption);
            $this->entityManager->flush();
        }
        $last10days = $waterconsumptionRepository->findLast10DaysForUser(self::DEFAULT_USER_ID);
        return $this->render('Front/Nutrition/waterconsumption/show.html.twig', [
            'waterconsumption' => $waterconsumption,
            'last10days' => $last10days,
        ]);
    }

    #[Route('/{date}/update', name: 'app_waterconsumption_update', methods: ['POST'])]
    public function update(string $date, Request $request, WaterconsumptionRepository $waterconsumptionRepository): Response
    {
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            throw $this->createNotFoundException('Invalid date format');
        }
        $waterconsumption = $waterconsumptionRepository->findOneBy([
            'user' => self::DEFAULT_USER_ID,
            'ConsumptionDate' => $dateObj
        ]);
        if (!$waterconsumption) {
            $waterconsumption = new Waterconsumption();
            $waterconsumption->setUser($this->entityManager->getReference('App\\Entity\\User', self::DEFAULT_USER_ID));
            $waterconsumption->setConsumptionDate($dateObj);
        }
        $amount = $request->request->get('amount_consumed', 0);
        $waterconsumption->setAmountConsumed((float)$amount);
        $this->entityManager->persist($waterconsumption);
        $this->entityManager->flush();
        // Redirect to the referring page or to the show page
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_waterconsumption_show', ['date' => $date]));
    }
}
