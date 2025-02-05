<?php

namespace App\DataFixtures\Provider;

class BulgarianProvider
{
    private array $industrialAreas = [
        'Индустриална зона',
        'Промишлена зона',
        'Логистичен парк',
        'Бизнес парк',
        'Индустриален парк',
    ];

    private array $bulgarianCities = [
        'София',
        'Пловдив',
        'Варна',
        'Бургас',
        'Русе',
        'Стара Загора',
        'Плевен',
        'Добрич',
        'Сливен',
        'Шумен',
        'Перник',
        'Хасково',
        'Ямбол',
        'Пазарджик',
        'Благоевград',
        'Велико Търново',
        'Враца',
        'Габрово',
        'Асеновград',
        'Видин',
    ];

    public function industrialArea(): string
    {
        return $this->industrialAreas[array_rand($this->industrialAreas)];
    }

    public function bulgarianCity(): string
    {
        return $this->bulgarianCities[array_rand($this->bulgarianCities)];
    }
}
