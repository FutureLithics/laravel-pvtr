<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLicenseImportRequest;
use App\Models\ImportBatch;
use App\Models\LicenseRecord;
use App\Services\LicenseImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminImportController extends Controller
{
    public function index(): View
    {
        return view('admin.imports.index', [
            'latestImport' => ImportBatch::latest()->first(),
            'imports' => ImportBatch::latest()->limit(10)->get(),
            'currentLicenseCount' => LicenseRecord::where('is_current', true)->count(),
        ]);
    }

    public function store(StoreLicenseImportRequest $request, LicenseImportService $importService): RedirectResponse
    {
        $file = $request->file('license_file');
        $storedPath = $file->store('imports', 'local');

        try {
            $batch = $importService->import(
                path: Storage::disk('local')->path($storedPath),
                originalFilename: $file->getClientOriginalName(),
                user: $request->user(),
                storedPath: $storedPath,
            );
        } catch (InvalidArgumentException $exception) {
            Storage::disk('local')->delete($storedPath);

            return back()
                ->withErrors(['license_file' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('admin.imports.index')
            ->with('status', "Imported {$batch->imported_rows} license rows.");
    }
}
