<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📦 Pusat Backup & Migrasi Data (CSV)
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <!-- Kolom Kiri: EXPORT -->
            <div class="p-4 border rounded-xl bg-gray-50 dark:bg-gray-900">
                <h3 class="font-bold mb-3 text-gray-700 dark:text-gray-300">📤 Unduh Data (Export)</h3>
                <div class="flex flex-wrap gap-2">
                    {{ $this->exportInstitutionsAction }}
                    {{ $this->exportAuthorsAction }}
                    {{ $this->exportDocumentsAction }}
                </div>
            </div>

            <!-- Kolom Kanan: IMPORT -->
            <div class="p-4 border rounded-xl bg-gray-50 dark:bg-gray-900">
                <h3 class="font-bold mb-3 text-gray-700 dark:text-gray-300">📥 Unggah Data (Import)</h3>
                <div class="flex flex-wrap gap-2">
                    {{ $this->importInstitutionsAction }}
                    {{ $this->importAuthorsAction }}
                    {{ $this->importDocumentsAction }}
                </div>
            </div>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>