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

        $isValid = $license !== null
            && $this->matchesCorroboratingDetail($license, $validated)
            && $license->isValidForVerification();

        return view('verification.index', [
            'result' => $isValid ? 'valid' : 'invalid',
            'license' => $isValid ? $license : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function matchesCorroboratingDetail(LicenseRecord $license, array $input): bool
    {
        if (($input['license_prefix'] ?? null) && trim((string) $input['license_prefix']) === $license->license_prefix) {
            return true;
        }

        if (($input['email'] ?? null) && strtolower(trim((string) $input['email'])) === strtolower((string) $license->email)) {
            return true;
        }

        if (($input['entity_name'] ?? null) && strtolower(trim((string) $input['entity_name'])) === strtolower($license->entity_name)) {
            return true;
        }

        return false;
    }
}
