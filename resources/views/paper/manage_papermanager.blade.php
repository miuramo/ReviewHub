<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 ">
            {{ __('投稿管理者の管理') }}
            <span class="mx-2"></span>
            <x-element.paperid size=1 :paper_id="$paper->id"></x-element.paperid>
            <span class="mx-6"></span>
        </h2>
    </x-slot>
    <!-- paper.create -->

    <div class="py-2">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif


        <div class="py-2 px-6">
            <x-paper.shoshi_list :paper="$paper">
            </x-paper.shoshi_list>
            <div class="py-2 px-4">
                投稿者：<x-element.login_as :user="$paper->paperowner"></x-element.login_as>
            </div>

            <div class="py-2 px-4">
                <x-element.h1c color="yellow"> 現在の投稿管理者：
                @foreach ($paper->managers as $user)
                    <x-element.login_as :user="$user"></x-element.login_as>
                    <x-role.remove_manager_force :submit_id="$paper->currentsubmit->id" :user_id="$user->id"></x-role.remove_manager_force>
                    <span class="mx-2"></span>
                @endforeach
                </x-element.h1c>
            </div>

            <div class="py-2 px-4">投稿管理者候補：
                @foreach ($candidates as $user)
                    <x-element.login_as :user="$user"></x-element.login_as>
                    <x-role.add_manager_force :submit_id="$paper->currentsubmit->id" :user_id="$user->id"></x-role.add_manager_force>
                    <span class="mx-2"></span>
                @endforeach
            </div>

        </div>

    </div>
    <script>
        function CheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = true;
                }
            }
        }

        function UnCheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = false;
                }
            }
        }

        function debug_em() {
            var textarea = document.getElementById('contact');
            textarea.value = "your-secondary@email.com\ncoauthor1@email.com\ncoauthor2@email.com";
        }
    </script>


</x-app-layout>
