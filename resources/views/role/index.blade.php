<!-- role.top -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            List of Roles and Users
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div>
        <x-paper.psummarytable />
    </div>

    <div class="mx-6">
        <table class="table-auto">
            <thead>
                <tr>
                    <th class="px-4 py-2">Role</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Users</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $role->name }}</td>
                        <td class="border px-2 py-1 text-center">{{ $role->desc }}</td>
                        <td class="border px-2 py-1">
                            @foreach ($role->users as $user)
                                <a href="/login-as/{{ $user->id }}">
                                    <span
                                        class="p-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800 hover:bg-yellow-200 hover:underline">
                                        {{ $user->name }} ({{ $user->id }})
                                    </span>
                                </a>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


</x-app-layout>
