<?php

namespace App\Services;

use App\Contracts\RzdDocumentServiceInterface;
use App\Dto\ActDataDto;
use App\Helpers\FileHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RzdDocumentService implements RzdDocumentServiceInterface
{

    private const ACT_TEMPLATE_PATH = 'template/acts.docx';
    private const TABLE_ROW_TEMPLATE_PATH = 'template/table_row.xml';

    private const TEMP_DIRECTORY = 'temp';

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function generateActs(UploadedFile $tableFile): string
    {
        $time = time();

        if (!Storage::exists(self::TEMP_DIRECTORY)) {
            Storage::makeDirectory(self::TEMP_DIRECTORY);
        }

        $loadFileName = self::TEMP_DIRECTORY . "/" . Str::uuid() . '.' . $tableFile->getClientOriginalExtension();
        Storage::put($loadFileName, $tableFile->getContent());
        $loadFilePath = storage_path('app/' . $loadFileName);
        $excel = IOFactory::load($loadFilePath);
        $worksheet = $excel->getActiveSheet();
        $dataList = $worksheet->toArray();
        unset($excel);
        Storage::delete($loadFileName);

        $actDataList = $this->getActDataArray($dataList);

        $saveDir = self::TEMP_DIRECTORY . '/' . Str::uuid();
        if (!Storage::exists($saveDir)) {
            Storage::makeDirectory($saveDir);
        }
        $saveDirPath = storage_path('app/' . $saveDir);

        $this->generateActsByTemplate($actDataList, $saveDirPath);

        $zipArchiveFile = $time . '/' . Str::uuid();
        if (!Storage::disk('public')->exists($zipArchiveFile)) {
            Storage::disk('public')->makeDirectory($zipArchiveFile);
        }

        $zipArchivePath = storage_path("app/public/$zipArchiveFile/acts.zip");
        $zipArchiveFile .= '/acts.zip';

        FileHelper::zip($saveDirPath, $zipArchivePath);
        Storage::deleteDirectory($saveDir);

        return asset('storage/' . $zipArchiveFile);
    }

    private function getActDataArray(array $dataList): array
    {
        $actDataList = [];

        $cells = [
            'Номер акта' => null,
            'Услуга' => null,
            'Дополнение к названию услуги' => null,
            'Цена' => null,
            'Исполнитель' => null,
            'Заказчик' => null,
            'Дата заселения' => null,
            'Дата выселения' => null,
            'Основание' => null,
            'Подпись, Должность исполнителя' => null,
            'Подпись, ФИО исполнителя' => null,
            'День оформления акта' => null
        ];

        $oldBaseNumber = null;
        $oldService = null;
        $oldServiceAddition = null;
        $oldPerformerFullInfo = null;
        $performerSignaturePosition = null;
        $performerSignatureFIO = null;

        foreach ($dataList as $rowNumber => $row) {
            if ($rowNumber === 0) {
                foreach ($row as $index => $cell) {
                    $cells[trim($cell)] = $index;
                }
                continue;
            }
            if (empty($row[0])) {
                break;
            }
            if (is_null($oldBaseNumber)) {
                $oldBaseNumber = $row[$cells['Основание']];
            }
            if (is_null($oldService)) {
                $oldService = $row[$cells['Услуга']];
            }
            if (is_null($oldServiceAddition)) {
                $oldServiceAddition = $row[$cells['Дополнение к названию услуги']] ?? '';
            }
            if (is_null($oldPerformerFullInfo)) {
                $oldPerformerFullInfo = $row[$cells['Исполнитель']];
            }
            if (is_null($performerSignaturePosition)) {
                $performerSignaturePosition = $row[$cells['Подпись, Должность исполнителя']];
            }
            if (is_null($performerSignatureFIO)) {
                $performerSignatureFIO = $row[$cells['Подпись, ФИО исполнителя']];
            }

            $actDataList[] = new ActDataDto(
                actNumber: $row[$cells['Номер акта']],
                createActDate: empty($row[$cells['День оформления акта']]) ? Carbon::now() : Carbon::parse($row[$cells['День оформления акта']]),
                consumerFIO: $row[$cells['Заказчик']],
                serviceName: $row[$cells['Услуга']] ?: $oldService,
                serviceNameAddition: $row[$cells['Дополнение к названию услуги']] ?? $oldServiceAddition,
                baseNumber: $row[$cells['Основание']] ?? $oldBaseNumber,
                startDate: Carbon::parse($row[$cells['Дата заселения']]),
                endDate: Carbon::parse($row[$cells['Дата выселения']]),
                performerFullInfo: $row[$cells['Исполнитель']] ?: $oldPerformerFullInfo,
                performerSignaturePosition: $row[$cells['Подпись, Должность исполнителя']] ?: $performerSignaturePosition,
                performerSignatureFIO: $row[$cells['Подпись, ФИО исполнителя']] ?: $performerSignatureFIO,
                price: (float)$row[$cells['Цена']],
            );
        }

        return $actDataList;
    }

    /**
     * @param array<ActDataDto> $actDataList
     * @param string $saveDir
     * @return void
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    private function generateActsByTemplate(array $actDataList, string $saveDir): void
    {
        $templatePath = storage_path('app/' . self::ACT_TEMPLATE_PATH);
        foreach ($actDataList as $actData) {
            $word = new TemplateProcessor($templatePath);
            $word->setValue('title', $actData->getTitle());
            $word->setValue('performerFullInfo', $actData->performerFullInfo);
            $word->setValue('consumerFIO', $actData->consumerFIO);
            $word->setValue('baseNumber', $actData->actNumber);
            $word->setValue('table', $this->generateTable($actData));
            $word->setValue('amount', $actData->getAmount());
            $word->setValue('amountString', $actData->getAmountString());
            $word->setValue('serviceResult', $actData->getServiceResult());
            $word->setValue('performerSignaturePosition', $actData->performerSignaturePosition);
            $word->setValue('performerSignatureFIO', $actData->performerSignatureFIO);
            $word->setValue('consumerFIO', $actData->consumerFIO);

            $word->saveAs($saveDir . '/' . $actData->actNumber . '.docx');
        }
    }

    private function generateTable(ActDataDto $actTableRowDto): string
    {
        $table = '';

        $rowTemplate = Storage::get(self::TABLE_ROW_TEMPLATE_PATH);

        for ($i = 1; $i <= $actTableRowDto->getDayCount(); $i++) {
            $table .= str_replace(
                search: ['${number}', '${serviceName}', '${price}'],
                replace: [$i, $actTableRowDto->getFullService($i), $actTableRowDto->getPrice()],
                subject: $rowTemplate
            );
        }
        return $table;
    }

    public function forgetOldDocuments(): void
    {
        Storage::deleteDirectory(self::TEMP_DIRECTORY);

        $time = now()->subHour()->getTimestamp();

        $directories = Storage::disk('public')->directories();

        foreach ($directories as $directory) {
            if ($directory <= $time) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        }
    }
}