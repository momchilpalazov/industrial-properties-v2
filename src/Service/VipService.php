<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;

class VipService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Активира VIP статус на имот въз основа на промоция
     */
    public function activateVipFromPromotion(Promotion $promotion): void
    {
        if ($promotion->getType() !== 'vip') {
            throw new \InvalidArgumentException('Промоцията трябва да бъде от тип VIP');
        }

        if (!$promotion->isPaid()) {
            throw new \InvalidArgumentException('Промоцията трябва да бъде платена');
        }

        $property = $promotion->getProperty();
        $property->setIsVip(true);
        $property->setVipExpiration(\DateTimeImmutable::createFromMutable($promotion->getEndDate()));
        
        $this->entityManager->flush();
    }

    /**
     * Деактивира VIP статус на имот
     */
    public function deactivateVip(Property $property): void
    {
        $property->setIsVip(false);
        $property->setVipExpiration(null);
        
        $this->entityManager->flush();
    }

    /**
     * Проверява дали имотът има активни VIP промоции
     */
    public function hasActiveVipPromotion(Property $property): bool
    {
        $now = new \DateTime();
        
        foreach ($property->getPromotions() as $promotion) {
            if ($promotion->getType() === 'vip' && 
                $promotion->isPaid() && 
                $promotion->getStartDate() <= $now && 
                $promotion->getEndDate() >= $now) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Синхронизира VIP статуса на имот с неговите активни промоции
     */
    public function syncVipStatusWithPromotions(Property $property): void
    {
        if ($this->hasActiveVipPromotion($property)) {
            // Намери най-дългата активна VIP промоция
            $latestEndDate = null;
            $now = new \DateTime();
            
            foreach ($property->getPromotions() as $promotion) {
                if ($promotion->getType() === 'vip' && 
                    $promotion->isPaid() && 
                    $promotion->getStartDate() <= $now && 
                    $promotion->getEndDate() >= $now) {
                    
                    if ($latestEndDate === null || $promotion->getEndDate() > $latestEndDate) {
                        $latestEndDate = $promotion->getEndDate();
                    }
                }
            }
            
            if ($latestEndDate) {
                $property->setIsVip(true);
                $property->setVipExpiration(\DateTimeImmutable::createFromMutable($latestEndDate));
            }
        } else {
            $property->setIsVip(false);
            $property->setVipExpiration(null);
        }
        
        $this->entityManager->flush();
    }
}
