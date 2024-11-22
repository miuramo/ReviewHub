@php
$reviews = App\Models\Review::where("user_id", auth()->id())->get();
@endphp

<!-- components.role.reviewer -->
<div class="px-6 py-4">
    <x-element.h1>
        担当査読一覧
    </x-element.h1>

    <div class="px-6 py-2 pb-6">
        @foreach($reviews as $rev)
        <div class="mx-4">
            {{ $rev->paper->title }}
            {{ $rev->paper->id }}
            {{ $rev->accept_id }}
            {{ $rev->ismeta }}
            <x-element.linkbutton href="{{ route('review.edit', ['review'=>$rev]) }}" color="lime">
                査読
            </x-element.linkbutton>
    
        </div>
        @endforeach
    </div>

    <x-element.h1>
        過去の担当査読
    </x-element.h1>
    <div class="mx-6 my-4">
        <x-element.linkbutton href="{{ route('review.index') }}" color="lime">
            査読を担当していただく投稿の一覧
        </x-element.linkbutton>
    </div>

</div>
