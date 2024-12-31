@props([
    'fileid' => null,
    'page' => 1,
    'label' => '1stPAGE IMG',
])

@php
    $file = App\Models\File::find($fileid);
@endphp
<!-- components.file.link_pdfthumb -->
@isset($file->key)
    <a class="p-1 px-2 rounded-md bg-yellow-300 hover:bg-orange-300"
        href="{{ route('file.pdfimages', ['file' => $fileid, 'page' => $page, 'hash' => substr($file->key, 0, 12)]) }}"
        target="_blank">{{ $label }}</a>
@endisset
