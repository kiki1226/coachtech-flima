@foreach ($comments as $comment)
    <div class="comment-block">
        <div class="comment-header">
            <div class="avatar"></div>
            <span class="comment-username">{{ $comment->user->name }}</span>
        </div>
        <div class="comment-body">{{ $comment->body }}</div>
    </div>
@endforeach
