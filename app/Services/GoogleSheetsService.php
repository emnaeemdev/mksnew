<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSheetsService
{
    private $client;
    private $service;

    public function __construct()
    {
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName('Monthly Newsletter System');
            $this->client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
            $this->client->setAccessType('offline');

            $this->service = new Sheets($this->client);
        } catch (Exception $e) {
            Log::error('خطأ في تهيئة Google Sheets Client: ' . $e->getMessage());
            throw new Exception('فشل في الاتصال بخدمة Google Sheets');
        }
    }

    public function cacheKey(string $spreadsheetId): string
    {
        return 'google_sheet_payload_' . $spreadsheetId;
    }

    public function cacheTtl()
    {
        $minutes = max(60, (int) config('google_sheets.cache_ttl_minutes', 60 * 24 * 30));

        return now()->addMinutes($minutes);
    }

    /**
     * جلب بيانات الجدول من الكاش المشترك (أو من Google عند أول زيارة / بعد التحديث).
     *
     * @return array{data: array, cached_at: string|null, from_cache: bool}
     */
    public function getCachedSheetPayload(string $spreadsheetId, bool $forceRefresh = false): array
    {
        $cacheKey = $this->cacheKey($spreadsheetId);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $payload = Cache::get($cacheKey);

            return [
                'data' => $payload['data'] ?? [],
                'cached_at' => $payload['cached_at'] ?? null,
                'from_cache' => true,
            ];
        }

        $rawData = $this->fetchAllSheetsData($spreadsheetId);
        $payload = [
            'data' => $this->formatDataForDisplay($rawData),
            'cached_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $payload, $this->cacheTtl());

        return [
            'data' => $payload['data'],
            'cached_at' => $payload['cached_at'],
            'from_cache' => false,
        ];
    }

    /**
     * @deprecated استخدم getCachedSheetPayload
     */
    public function getSheetData($spreadsheetId, $range = null)
    {
        if ($range) {
            return $this->fetchSheetRange($spreadsheetId, $range);
        }

        return $this->getCachedSheetPayload($spreadsheetId)['data'];
    }

    public function clearSheetCache(string $spreadsheetId): void
    {
        Cache::forget($this->cacheKey($spreadsheetId));
        // مفاتيح قديمة
        Cache::forget("google_sheet_{$spreadsheetId}_" . md5('all'));
        Cache::forget("google_sheet_{$spreadsheetId}_" . md5($spreadsheetId));
    }

    public function refreshSheetCache(string $spreadsheetId): array
    {
        return $this->getCachedSheetPayload($spreadsheetId, true);
    }

    private function fetchSheetRange(string $spreadsheetId, string $range): array
    {
        try {
            $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);

            return $response->getValues() ?? [];
        } catch (Exception $e) {
            Log::error('خطأ في جلب بيانات Google Sheet: ' . $e->getMessage());
            throw new Exception('فشل في جلب البيانات من Google Sheets');
        }
    }

    private function fetchAllSheetsData(string $spreadsheetId): array
    {
        try {
            $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();

            $allData = [];

            foreach ($sheets as $sheet) {
                $sheetTitle = $sheet->getProperties()->getTitle();

                try {
                    $response = $this->service->spreadsheets_values->get(
                        $spreadsheetId,
                        $sheetTitle . '!A2:H200'
                    );

                    $values = $response->getValues();

                    if (!empty($values)) {
                        $allData[$sheetTitle] = $values;
                    }
                } catch (Exception $e) {
                    Log::warning("تعذر جلب بيانات الورقة {$sheetTitle}: " . $e->getMessage());
                    continue;
                }
            }

            return $allData;
        } catch (Exception $e) {
            Log::error('خطأ في جلب جميع أوراق Google Sheet: ' . $e->getMessage());
            throw new Exception('فشل في جلب البيانات من Google Sheets: ' . $e->getMessage());
        }
    }

    public function getSheetNames($spreadsheetId)
    {
        try {
            $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();

            $sheetNames = [];
            foreach ($sheets as $sheet) {
                $sheetNames[] = $sheet->getProperties()->getTitle();
            }

            return $sheetNames;
        } catch (Exception $e) {
            Log::error('خطأ في جلب أسماء الأوراق: ' . $e->getMessage());
            throw new Exception('فشل في جلب أسماء الأوراق: ' . $e->getMessage());
        }
    }

    public function validateSpreadsheetId($spreadsheetId)
    {
        try {
            $this->service->spreadsheets->get($spreadsheetId);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function extractSpreadsheetId($url)
    {
        $pattern = '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function formatDataForDisplay($data)
    {
        $formattedData = [];

        foreach ($data as $sheetName => $sheetData) {
            if (empty($sheetData)) {
                continue;
            }

            $cleanedData = [];
            foreach ($sheetData as $row) {
                $hasData = false;
                foreach ($row as $cell) {
                    if (!empty(trim((string) $cell))) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $cleanedData[] = $row;
                }
            }

            if (!empty($cleanedData)) {
                $formattedData[$sheetName] = $cleanedData;
            }
        }

        return $formattedData;
    }

    public function convertToJson($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
