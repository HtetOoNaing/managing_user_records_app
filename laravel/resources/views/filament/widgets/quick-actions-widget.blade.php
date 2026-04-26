<x-filament-widgets::widget class="fi-wi-quick-actions">
    <x-filament::section heading="Quick Actions">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 2rem; padding: 0.5rem 0;">
            <x-filament::button
                tag="a"
                href="{{ url('/admin/users/create') }}"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedUserPlus"
                color="primary"
                size="lg"
                class="w-full"
            >
                Create New User
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ url('/admin/users') }}"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedUsers"
                color="gray"
                size="lg"
                class="w-full"
            >
                View All Users
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ url('/admin/user-activity-logs') }}"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedClipboardDocumentList"
                color="gray"
                size="lg"
                class="w-full"
            >
                View Activity Logs
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
