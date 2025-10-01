<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
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
    
    /**
     * تهيئة Google Client
     */
    private function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName('Monthly Newsletter System');
            $this->client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $this->client->setAuthConfig(storage_path('app/credentials.json'));
            $this->client->setAccessType('offline');
            
            $this->service = new Sheets($this->client);
        } catch (Exception $e) {
            Log::error('خطأ في تهيئة Google Sheets Client: ' . $e->getMessage());
            throw new Exception('فشل في الاتصال بخدمة Google Sheets');
        }
    }
    
    /**
     * جلب بيانات من Google Sheet
     * 
     * @param string $spreadsheetId معرف الجدول
     * @param string $range النطاق (اختياري)
     * @return array
     */
    public function getSheetData($spreadsheetId, $range = null)
    {
        try {
            // إذا لم يتم تحديد النطاق، جلب جميع الأوراق
            if (!$range) {
                return $this->getAllSheetsData($spreadsheetId);
            }
            
            $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();
            
            return $values ?? [];
        } catch (Exception $e) {
            Log::error('خطأ في جلب بيانات Google Sheet: ' . $e->getMessage());
            throw new Exception('فشل في جلب البيانات من Google Sheets: ' . $e->getMessage());
        }
    }
    
    /**
     * جلب بيانات جميع الأوراق في الجدول
     * 
     * @param string $spreadsheetId
     * @return array
     */
    public function getAllSheetsData($spreadsheetId)
    {
        try {
            // جلب معلومات الجدول للحصول على أسماء الأوراق
            $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();
            
            $allData = [];
            
            foreach ($sheets as $sheet) {
                $sheetTitle = $sheet->getProperties()->getTitle();
                
                try {
                    // جلب بيانات كل ورقة بالنطاق المحدد A2:H200
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
    
    /**
     * جلب أسماء الأوراق في الجدول
     * 
     * @param string $spreadsheetId
     * @return array
     */
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
    
    /**
     * التحقق من صحة معرف Google Sheet
     * 
     * @param string $spreadsheetId
     * @return bool
     */
    public function validateSpreadsheetId($spreadsheetId)
    {
        try {
            $this->service->spreadsheets->get($spreadsheetId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * استخراج معرف Google Sheet من الرابط
     * 
     * @param string $url
     * @return string|null
     */
    public static function extractSpreadsheetId($url)
    {
        // نمط الرابط: https://docs.google.com/spreadsheets/d/SPREADSHEET_ID/edit...
        $pattern = '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * تنسيق البيانات للعرض
     * 
     * @param array $data
     * @return array
     */
    public function formatDataForDisplay($data)
    {
        $formattedData = [];
        
        foreach ($data as $sheetName => $sheetData) {
            if (empty($sheetData)) {
                continue;
            }
            
            // تنظيف البيانات وإزالة الصفوف الفارغة
            $cleanedData = [];
            foreach ($sheetData as $row) {
                // التحقق من وجود بيانات في الصف
                $hasData = false;
                foreach ($row as $cell) {
                    if (!empty(trim($cell))) {
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
    
    /**
     * تحويل البيانات إلى JSON
     * 
     * @param array $data
     * @return string
     */
    public function convertToJson($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}