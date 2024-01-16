<?php

namespace App\Modules\InformantBot\Services;

use App\Modules\InformantBot\Contracts\InformantBotServiceInterface;
use App\Modules\InformantBot\Models\InformantBotData;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Revolution\Google\Sheets\Facades\Sheets;

class InformantBotService implements InformantBotServiceInterface
{
    private const SPREADSHEET_ID = '1WuKG7ppSTlpEiRkyhrL5ZXFkqFaOUu9GEox_LcpVn9M';

    public function saveTable(InformantBotData $informantBotData): void
    {
        $list = Sheets::spreadsheet(self::SPREADSHEET_ID)
            ->sheet('Ответы')
            ->all();

        $index = count($list);

        foreach ($list as $rowIndex => $item) {
            if ($item[0] === $informantBotData->chat_id) {
                $index = $rowIndex;
                break;
            }
        }

        ++$index;

        Sheets::spreadsheet(self::SPREADSHEET_ID)
            ->sheet('Ответы')
            ->range('A' . $index)
            ->update([[
                $informantBotData->chat_id,
                $informantBotData->step->getStepName(),
                $informantBotData->test_points ?? '',
                $informantBotData->review ?? '',
                Carbon::now()->format('d.m.Y H:i')
            ]]);
    }
}