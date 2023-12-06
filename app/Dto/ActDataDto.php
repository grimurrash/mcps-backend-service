<?php

namespace App\Dto;

use App\Helpers\PriceHelper;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ActDataDto
{
    private int $dayCount;

    public function __construct(
        public string $actNumber,
        public Carbon $createActDate,
        public string $consumerFIO,
        public string $serviceName,
        public string $serviceNameAddition,
        public string $baseNumber,
        public Carbon $startDate,
        public Carbon $endDate,
        public string $performerFullInfo,
        public string $performerSignaturePosition,
        public string $performerSignatureFIO,
        public float  $price,
    )
    {
        $this->dayCount = $this->endDate->diffInDays($this->startDate);
    }

    public function getDayCount(): int
    {
        return $this->dayCount;
    }

    public function getFullService(int $day): string
    {
        $dateFromString = $this->startDate->clone()->addDays($day - 1)->format('d.m.Y');
        $dateToString = $this->startDate->clone()->addDays($day)->format('d.m.Y');

        $fullService = "$this->serviceName с $dateFromString по $dateToString";

        if (!empty($this->serviceNameAddition)) {
            $fullService .= ", $this->serviceNameAddition";
        }

        return $fullService;
    }

    public function getTitle(): string
    {
        $date = $this->createActDate->locale('ru')->translatedFormat('j F Y');

        return "Акт № $this->actNumber от $date г.";
    }

    public function getPrice(): string
    {
        return number_format($this->price, 2, ',', '');
    }

    public function getServiceResult(): string
    {
        $amount = number_format(round($this->price * $this->dayCount, 2), 2, ',', ' ');
        return "Всего оказано услуг $this->dayCount, на сумму $amount руб.";
    }

    public function getAmount(): string
    {
        return number_format(round($this->price * $this->dayCount, 2), 2, ',', '');
    }

    public function getAmountString(): string
    {
        $amount = round($this->price * $this->dayCount, 2);
        return Str::ucfirst(PriceHelper::num2str($amount));
    }
}