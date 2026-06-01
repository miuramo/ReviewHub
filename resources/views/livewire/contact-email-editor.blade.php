<div>
    <!-- livewire.contact-email-editor -->
    <div class="mx-6 my-0">
        <div class="mx-10 mb-1">
            <label for="contact_lw"
                class="block text-sm font-medium text-gray-900 dark:text-white">
                投稿連絡用メールアドレス（必要があれば修正・更新してください。1件は必須、最大{{ env('CONTACTEMAILS_MAX', 5) }}件まで、1行に1件ずつ）
            </label>

            @if (!empty($validationErrors))
                <div class="mx-2 mt-2 px-1">
                    @foreach ($validationErrors as $err)
                        <p class="text-sm text-red-600 dark:text-red-400 font-semibold">{{ $err }}</p>
                    @endforeach
                </div>
            @endif

            @if ($saved)
                <p class="mx-2 mt-2 px-1 text-sm text-green-700 dark:text-green-400 font-semibold">
                    投稿連絡用メールアドレスを更新しました。
                </p>
            @endif

            <textarea id="contact_lw"
                wire:model.live="contactemails"
                rows="5"
                class="mb-1 block p-2.5 w-3/4 text-md text-gray-900 bg-gray-50 rounded-lg border
                       {{ !empty($validationErrors) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}
                       dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="your-secondary@email.com&#10;coauthor1@email.com&#10;coauthor2@email.com"
            ></textarea>

            <button
                wire:click="save"
                type="button"
                class="inline-flex justify-center py-1 px-2 mb-0.5 border border-transparent shadow-sm text-md font-medium
                       rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none
                       focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500
                       dark:bg-yellow-700 dark:hover:bg-yellow-500 dark:hover:text-yellow-700
                       disabled:opacity-50"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="save">投稿連絡用メールアドレスを更新</span>
                <span wire:loading wire:target="save">更新中...</span>
            </button>
        </div>
    </div>
</div>
