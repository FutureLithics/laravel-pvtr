<x-layouts.app title="Admin Imports">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">License imports</h1>
            <p class="mt-2 text-slate-600">Upload an XLSX, TXT, or TSV file to refresh the current license snapshot.</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm">
            <span class="font-medium">{{ number_format($currentLicenseCount) }}</span>
            current license records
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Upload license file</h2>
            <p class="mt-1 text-sm text-slate-600">Expected headers: License #, License prefix, Entity name, Entity type, License status, Email, Expiration date.</p>

            <form method="POST" action="{{ route('admin.imports.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="license_file" class="block text-sm font-medium text-slate-700">File</label>
                    <input
                        id="license_file"
                        name="license_file"
                        type="file"
                        accept=".xlsx,.txt,.tsv"
                        required
                        class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium"
                    >
                    @error('license_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Upload and import
                </button>
            </form>
        </section>

        <aside class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Last import</h2>
            @if ($latestImport)
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-slate-700">File</dt>
                        <dd class="mt-1 text-slate-600">{{ $latestImport->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Rows</dt>
                        <dd class="mt-1 text-slate-600">
                            {{ $latestImport->imported_rows }} imported, {{ $latestImport->skipped_rows }} skipped
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Uploaded</dt>
                        <dd class="mt-1 text-slate-600">{{ $latestImport->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-4 text-sm text-slate-600">No imports have been uploaded yet.</p>
            @endif
        </aside>
    </div>

    <section class="mt-8 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold">Recent import history</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-6 py-3 font-medium">File</th>
                        <th class="px-6 py-3 font-medium">Type</th>
                        <th class="px-6 py-3 font-medium">Imported</th>
                        <th class="px-6 py-3 font-medium">Skipped</th>
                        <th class="px-6 py-3 font-medium">Uploaded</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($imports as $import)
                        <tr>
                            <td class="px-6 py-4">{{ $import->original_filename }}</td>
                            <td class="px-6 py-4 uppercase text-slate-600">{{ $import->file_type }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $import->imported_rows }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $import->skipped_rows }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $import->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-600">No imports yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
