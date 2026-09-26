<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('admin.dashboard') }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Magic Link発行
        </h2>
    </x-slot>

    <div class="px-6 py-4 max-w-3xl">
        <form method="POST" action="{{ route('admin.magic-links.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="email" value="ログイン対象ユーザーのメールアドレス" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                    :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <x-primary-button>リンクを発行</x-primary-button>
        </form>

        @if (session('magic_link_url'))
            <div class="mt-6 space-y-2" role="status">
                <p>対象: {{ session('magic_link_email') }}</p>
                <p>有効期限: {{ session('magic_link_expires_at') }}</p>
                <label for="magic-link-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    発行したMagic Link
                </label>
                <input id="magic-link-url" type="url" readonly value="{{ session('magic_link_url') }}"
                    class="block w-full rounded border-gray-300 bg-gray-50 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    onclick="this.select()" />
                <a class="break-all text-sm underline" href="{{ session('magic_link_url') }}">
                    リンクを開く
                </a>
                @if (session('password_reset_url'))
                    <label for="password-reset-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        パスワード再設定リンク（メール送信なし）
                    </label>
                    <input id="password-reset-url" type="url" readonly value="{{ session('password_reset_url') }}"
                        class="block w-full rounded border-gray-300 bg-gray-50 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        onclick="this.select()" />
                    <a class="break-all text-sm underline" href="{{ session('password_reset_url') }}">
                        リンクを開く
                    </a>
                @elseif (session('password_reset_status') === \Illuminate\Support\Facades\Password::RESET_THROTTLED)
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        パスワード再設定URLは直前に発行済みのため、新しいリンクを表示できませんでした。しばらく待ってから再度お試しください。
                    </p>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>