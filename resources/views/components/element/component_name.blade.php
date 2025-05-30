@props([
    'type'  => 'div', 
])
@if(env('APP_DEBUG'))
    <{{$type}} class="font-sm text-gray-200 dark:text-gray-500 hover:text-gray-600">{{ $slot }}</{{$type}}>
@endif