<x-layouts.app title="Verify License">
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="max-w-2xl">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Logo license verification</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">Check whether a logo license is valid</h1>
                <p class="mt-3 text-slate-600">
                    Enter the license number and one corroborating detail from your license record.
                </p>
            </div>

            <form method="POST" action="{{ route('verification.verify') }}" class="mt-8 space-y-6">
                @csrf

                <div>
                    <label for="license_number" class="block text-sm font-medium text-slate-700">License #</label>
                    <input
                        id="license_number"
                        name="license_number"
                        type="text"
                        value="{{ old('license_number') }}"
                        placeholder="###-###"
                        required
                        class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-slate-900 focus:outline-none"
                    >
                    @error('license_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="license_prefix" class="block text-sm font-medium text-slate-700">License prefix</label>
                        <input
                            id="license_prefix"
                            name="license_prefix"
                            type="text"
                            value="{{ old('license_prefix') }}"
                            placeholder="#####"
                            class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-slate-900 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="name@example.com"
                            class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-slate-900 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="entity_name" class="block text-sm font-medium text-slate-700">Entity name</label>
                        <input
                            id="entity_name"
                            name="entity_name"
                            type="text"
                            value="{{ old('entity_name') }}"
                            placeholder="Organization or individual"
                            class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-slate-900 focus:outline-none"
                        >
                    </div>
                </div>

                @if ($errors->has('license_prefix') || $errors->has('email') || $errors->has('entity_name'))
                    <p class="text-sm text-red-600">Enter at least one corroborating detail: license prefix, email, or entity name.</p>
                @endif

                <button type="submit" class="rounded-md bg-slate-950 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                    Verify license
                </button>
            </form>
        </section>

        <aside class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Verification result</h2>

            @if ($result === 'valid' && $license)
                <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-4">
                    <p class="font-medium text-emerald-900">License is valid.</p>
                    <dl class="mt-4 space-y-3 text-sm text-emerald-900">
                        <div>
                            <dt class="font-medium">Entity</dt>
                            <dd>{{ $license->entity_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium">Status</dt>
                            <dd>{{ $license->license_status }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium">Expiration</dt>
                            <dd>{{ $license->expiration_date?->format('M j, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            @elseif ($result === 'invalid')
                <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-4">
                    <p class="font-medium text-red-900">No valid matching license was found.</p>
                    <p class="mt-2 text-sm text-red-800">Check the license details and try again.</p>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-600">Submit the form to see whether a license is currently valid.</p>
            @endif
        </aside>
    </div>
</x-layouts.app>
