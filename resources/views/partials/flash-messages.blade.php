@if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5 space-y-3">
        @if(session('success'))
            <x-alert type="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="error" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        @if(session('warning'))
            <x-alert type="warning" dismissible>
                {{ session('warning') }}
            </x-alert>
        @endif

        @if(session('info'))
            <x-alert type="info" dismissible>
                {{ session('info') }}
            </x-alert>
        @endif

        @if($errors->any())
            <x-alert type="error" title="Please check the highlighted fields:" dismissible>
                <ul class="list-disc list-inside space-y-0.5 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>
@endif
