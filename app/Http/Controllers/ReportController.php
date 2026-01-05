<?php

namespace App\Http\Controllers;

use App\Services\ReportReaderService;
use App\Services\ReportWriterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ReportController extends Controller
{
    public function __construct(
        protected ReportReaderService $reportReaderService,
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
            $this->reportReaderService->uploadRAR(
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
            $this->reportReaderService->uploadBG(
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

    public function uploadRevenueSchedule(Request $request)
    {
        $request->validate([
            'revenue_schedule_file' => 'required|file',
            'year' => 'required|string',
            'month' => 'required|string',
        ]);

        $target = $this->createTempUploadFile($request->file('revenue_schedule_file'));

        try {
            $this->reportReaderService->uploadRevenueSchedule(
                $target,
                $request->input(key: 'year'),
                $request->input('month')
            );
        } catch( \Exception $e) {
            // Handle exception if needed
            Log::error('Error uploading Revenue Schedule report: ' . $e->getMessage());
            throw $e;
        }
         finally {
            $this->cleanupTempFile($target);
        }

        return to_route('home')->with('success', 'Revenue Schedule Report uploaded successfully.');
    }

    public function uploadDeptWiseRAR(Request $request)
    {
        $request->validate([
            'dept_wise_rar_file' => 'required|file',
            'financial_year' => 'required|string',
            'month' => 'required|string',
        ]);

        $target = $this->createTempUploadFile($request->file('dept_wise_rar_file'));

        try {
            $this->reportReaderService->uploadDeptWiseRAR(
                $target,
                $request->input('financial_year'),
                $request->input('month')
            );
        } catch( \Exception $e) {
            // Handle exception if needed
            Log::error('Error uploading Department Wise RAR report: ' . $e->getMessage());
            throw $e;
        }
         finally {
            $this->cleanupTempFile($target);
        }

        return to_route('home')->with('success', 'Department Wise RAR Report uploaded successfully.');
    }
}
