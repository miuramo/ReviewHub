<x-app-layout>
    <!-- sub.show -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            <span class="mx-2"></span>
            {{ __('任期役職管理') }}
        </h2>
    </x-slot>
    @section('title', '任期役職管理')

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="mx-6">
        
    </div>


</x-app-layout>
