<x-guest-layout>
    <div class="mb-4 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 mb-4">
            <svg class="w-8 h-8 text-brand-darkgreen" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Two-Factor Authentication</h2>
        <p class="text-sm text-gray-600">
            A verification code has been sent to your email. Please enter it below to continue.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    @error('code')
        <div class="mb-4 font-medium text-sm text-red-600 bg-red-50 p-3 rounded-md">
            {{ $message }}
        </div>
    @enderror

    @error('recovery_code')
        <div class="mb-4 font-medium text-sm text-red-600 bg-red-50 p-3 rounded-md">
            {{ $message }}
        </div>
    @enderror

    <!-- OTP Code Form -->
    <form method="POST" action="{{ route('two-factor.verify') }}" id="codeForm">
        @csrf

        <div>
            <label for="code" class="block font-medium text-sm text-gray-700">Verification Code</label>
            <input id="code" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen text-center text-2xl tracking-[0.5em] font-mono" type="text" name="code" maxlength="6" placeholder="000000" autofocus autocomplete="one-time-code" />
        </div>

        <button type="submit" class="w-full mt-4 inline-flex items-center justify-center px-4 py-2 bg-brand-darkgreen border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-amber hover:text-brand-darkgreen transition ease-in-out duration-150">
            Verify Code
        </button>
    </form>

    <div class="mt-4 flex items-center justify-between">
        <!-- Resend Code -->
        <form method="POST" action="{{ route('two-factor.send') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-600 hover:text-brand-darkgreen underline">
                Resend Code
            </button>
        </form>

        <!-- Toggle Recovery Code -->
        <button type="button" onclick="toggleRecovery()" class="text-sm text-gray-600 hover:text-brand-darkgreen underline">
            Use Recovery Code
        </button>
    </div>

    <!-- Recovery Code Form (hidden by default) -->
    <div id="recoveryForm" class="hidden mt-4 pt-4 border-t border-gray-200">
        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <div>
                <label for="recovery_code" class="block font-medium text-sm text-gray-700">Recovery Code</label>
                <input id="recovery_code" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen font-mono" type="text" name="recovery_code" placeholder="Enter recovery code" />
            </div>

            <button type="submit" class="w-full mt-4 inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                Verify Recovery Code
            </button>
        </form>
    </div>

    <script>
        function toggleRecovery() {
            const form = document.getElementById('recoveryForm');
            const codeForm = document.getElementById('codeForm');
            form.classList.toggle('hidden');
            codeForm.classList.toggle('hidden');
        }
    </script>
</x-guest-layout>
