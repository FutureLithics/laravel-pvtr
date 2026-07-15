<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyLicenseRequest;
use App\Models\LicenseRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(): View
    {
        $license = null;

        if ($licenseId = session('verification.license_id')) {
            $license = LicenseRecord::query()->find($licenseId);
        }

        return view('verification.index', [
            'result' => session('verification.result'),
            'license' => $license,
        ]);
    }

    public function verify(VerifyLicenseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $license = LicenseRecord::query()
            ->where('is_current', true)
            ->where('license_number', trim($validated['license_number']))
            ->first();

        $isValid = $license !== null
            && $this->matchesCorroboratingDetail($license, $validated)
            && $license->isValidForVerification();

        $redirect = redirect()
            ->route('verification.index')
            ->withInput($request->only(['license_number', 'license_prefix', 'email', 'entity_name']))
            ->with('verification.result', $isValid ? 'valid' : 'invalid');

        if ($isValid) {
            $redirect->with('verification.license_id', $license->id);
        }

        return $redirect;
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
