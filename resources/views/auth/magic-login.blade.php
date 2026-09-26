<x-guest-layout>
    <div class="space-y-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Magic Linkでログイン</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">このリンクでログインします。リンクは一度だけ使用できます。</p>
        <form method="POST" action="{{ route('magic-login.consume', ['token' => $token]) }}">
            @csrf
            <x-primary-button>ログイン</x-primary-button>
        </form>
    </div>
</x-guest-layout>