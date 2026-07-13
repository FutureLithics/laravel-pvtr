<?php

namespace App\Http\Controllers;

use App\Models\LicenseRecord;
use Illuminate\Http\Request;
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

    public function verify(Request $request): View
    {
        $validated = $request->validate([
            'license_number' => ['required', 'string', 'max:255'],
            'license_prefix' => ['nullable', 'required_without_all:email,entity_name', 'string', 'max:255'],
            'email' => ['nullable', 'required_without_all:license_prefix,entity_name', 'email', 'max:255'],
            'entity_name' => ['nullable', 'required_without_all:license_prefix,email', 'string', 'max:255'],
        ], [
            'license_prefix.required_without_all' => 'Enter a license prefix, email, or entity name to verify this license.',
            'email.required_without_all' => 'Enter a license prefix, email, or entity name to verify this license.',
            'entity_name.required_without_all' => 'Enter a license prefix, email, or entity name to verify this license.',
        ]);

        $license = LicenseRecord::query()
            ->where('is_current', true)
            ->where('license_number', trim($validated['license_number']))
            ->first();

        $isValid = $license !== null
            && $this->matchesCorroboratingDetail($license, $validated)
            && strtolower($license->license_status) === 'active'
            && $license->expiration_date !== null
            && $license->expiration_date->toDateString() >= now()->toDateString();

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
