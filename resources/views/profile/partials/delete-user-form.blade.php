<section class="space-y-6">
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9F2A2A] dark:text-[#F0A0A0]">{{ __('Hapus Akun') }}</p>
        <h2 class="mt-2 text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Hapus akun secara permanen') }}</h2>
        <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Setelah akun dihapus, seluruh data terkait akun tidak dapat dipulihkan. Pastikan Anda benar-benar ingin melanjutkan.') }}</p>
    </header>

    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('Hapus Akun') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="bg-white p-6 dark:bg-[#161B22]">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Are you sure you want to delete your account?') }}</h2>
            <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
