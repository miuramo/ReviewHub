@props([
    'status' => null,
    'url' => null,
    'messageId' => null,
    'paper_id' => null,
])

<div class="bb-read-status text-left text-xs text-gray-500 dark:text-gray-300 pt-1" data-status-url="{{ $url }}"
    data-message-id="{{ $messageId }}">
    @can('manage_review', $paper_id)
        @if ($status !== null)
            @if ($status['count'] !== null)
                閲覧者 {{ $status['count'] }}人
            @else
                @if (isset($status['read_at']) && $status['read_at'])
                    既読（{{ date('Y-m-d H:i:s', strtotime($status['read_at'])) }}）
                @else
                    未読
                @endif
            @endif
        @endif
    @endcan
</div>
