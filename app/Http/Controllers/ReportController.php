<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\ReportWriterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected ReportWriterService $reportWriterService,
    ) {
    }
    public function uploadRAR(Request $request)
    {
        $request->validate([
            'rar_file' => 'required|file',
            'financial_year' => 'required|string',
            'month' => 'required|string',
        ]);

        $target = $this->createTempUploadFile($request->file('rar_file'));

        try {
            $this->reportService->uploadRAR(
                $target,
                $request->input('financial_year'),
                $request->input('month')
            );
        } catch( \Exception $e) {
            // Handle exception if needed
            Log::error('Error uploading RAR report: ' . $e->getMessage());
            throw $e;
        }
         finally {
            $this->cleanupTempFile($target);
        }

        return to_route('home')->with('success', 'RAR Report uploaded successfully.');
    }

    public function uploadBG(Request $request) {
        $request->validate([
            'bg_file' => 'required|file',
            'financial_year' => 'required|string',
        ]);
        $target = $this->createTempUploadFile($request->file('bg_file'));

        try {
            $this->reportService->uploadBG(
                $target,
                $request->input('financial_year'),
            );
        } catch( \Exception $e) {
            // Handle exception if needed
            Log::error('Error uploading RAR report: ' . $e->getMessage());
            throw $e;
        }
         finally {
            $this->cleanupTempFile($target);
        }

        return to_route('home')->with('success', 'BG uploaded successfully.');
    }


    public function download(Request $request)
    {
         $request->validate([
            'year' => 'required|string',
            'month' => 'required|string',
        ]);

        return $this->reportWriterService->export($request->input('year'), $request->input('month'));
    }
}
