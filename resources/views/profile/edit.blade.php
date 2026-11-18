<x-app-layout>
    <x-slot name="header">
        Profil Pengguna
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <div class="p-6 sm:p-8 bg-white border border-gray-200/70 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white border border-gray-200/70 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white border border-gray-200/70 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
