<!-- role.top -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ $role->desc }} Toppage 
            {{-- &nbsp;
            <x-element.linkbutton href="{{ route('file.create') }}" color="cyan">
                Upload New File</x-element.linkbutton>

            <x-element.deletebutton action="{{ route('file.delall') }}" color="red" confirm="全部削除してよいですか？"> Delete All
            </x-element.deletebutton> --}}
            <span class="mx-4"></span>
            @if ($role->name == 'ec')
                <x-element.linkbutton
                    href="https://scrapbox.io/reviewhub/%E7%B7%A8%E9%9B%86%E9%95%B7%E3%81%AE%E4%BD%9C%E6%A5%AD"
                    color="cyan" size="sm" target="_blank">
                    編集管理マニュアル (Cosense/Scrapbox)</x-element.linkbutton>
            @endif
            @if ($role->name == 'rev')
                <x-element.linkbutton
                    href="https://scrapbox.io/reviewhub/%E6%9F%BB%E8%AA%AD%E3%83%9E%E3%83%8B%E3%83%A5%E3%82%A2%E3%83%AB"
                    color="pink" size="sm" target="_blank">
                    査読マニュアル (Cosense/Scrapbox)</x-element.linkbutton>
            @endif
            @if ($role->name == 'pub')
                <x-element.linkbutton
                    href="https://scrapbox.io/reviewhub/%E5%87%BA%E7%89%88%E3%81%AB%E5%90%91%E3%81%91%E3%81%9F%E4%BD%9C%E6%A5%AD"
                    color="lime" size="sm" target="_blank">
                    出版マニュアル (Cosense/Scrapbox)</x-element.linkbutton>
            @endif
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @can('role_any', 'meta|rev|ec|aec|pub|award|acc|demo|web|wc|admin')
        @if ($role->name == 'rev')
            <x-role.rev :role="$role">
            </x-role.rev>
        @endif
        @if ($role->name == 'cm')
            <x-role.cm :role="$role">
            </x-role.cm>
        @endif
        @if ($role->name == 'ec' || $role->name == 'aec')
            <x-role.ec :role="$role">
            </x-role.ec>
        @endif
        @if ($role->name == 'web')
            <x-role.web :role="$role">
            </x-role.web>
        @endif
        @if ($role->name == 'wc')
            <x-role.pcsub :role="$role">
            </x-role.pcsub>
        @endif
        @if ($role->name == 'pub')
            <x-role.pub :role="$role">
            </x-role.pub>
        @endif
        @if ($role->name == 'award')
            <x-role.award :role="$role">
            </x-role.award>
        @endif
        @if ($role->name == 'acc')
            <x-role.acc :role="$role">
            </x-role.acc>
        @endif
        @if ($role->name == 'demo')
            <x-role.demo :role="$role">
            </x-role.demo>
        @endif
        @if ($role->name == 'admin')
            <x-role.admin :role="$role">
            </x-role.admin>
        @endif
    @endcan


</x-app-layout>
