<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyLicenseRequest;
use App\Models\LicenseRecord;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(): View
    {
        return view('verification.index', [
            'result' => null,
            'license' => null,
        ]);
    }

    public function verify(VerifyLicenseRequest $request): View
    {
        $validated = $request->validated();

        $license = LicenseRecord::query()
            ->where('is_current', true)
            ->where('license_number', trim($validated['license_number']))
            ->first();

        $isValid = $license !== null && $license->isValidForVerification();

        return view('verification.index', [
            'result' => $isValid ? 'valid' : 'invalid',
            'license' => $isValid ? $license : null,
        ]);
    }
}
