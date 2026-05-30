@extends('layouts.app')

@section('title', 'Pretentie '.$contactThread->thread_uuid.' | Admin')

@section('content')
<section class="section-shell admin-order-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Pretentie</span>
            <h1>{{ $contactThread->fullName() }}</h1>
            <p>{{ $contactThread->email }} · {{ $contactThread->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <a class="secondary-btn" href="{{ route('admin.claims.index') }}">Inapoi</a>
    </div>

    <div class="thread-layout admin-thread-layout">
        <aside class="thread-sidebar">
            <span class="eyebrow">Fir</span>
            <h2>{{ $contactThread->thread_uuid }}</h2>
            
            <div class="thread-meta">
                <span class="status-pill {{ $contactThread->status === 'closed' ? 'invalid' : 'valid' }}">
                    {{ $contactThread->status === 'closed' ? 'Încheiat' : 'Activ' }}
                </span>
                <span>Ultimul mesaj: {{ optional($contactThread->last_message_at)->format('d.m.Y H:i') ?: '-' }}</span>
            </div>
        </aside>

        <div class="thread-panel">
            <div class="message-list">
                @foreach($contactThread->messages as $message)
                    <article class="chat-row {{ $message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'from-company' : 'from-customer' }}">
                        <div class="chat-bubble">
                            <strong>{{ $message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'Compania' : $contactThread->fullName() }}</strong>
                            <p>{!! nl2br(e($message->body)) !!}</p>
                            <span>{{ $message->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($contactThread->status === 'closed')
                <div class="thread-closed-note">Pretenția este încheiată. Nu se mai pot trimite mesaje.</div>
            @else
                <form id="admin-thread-reply-form" class="thread-reply-form" method="POST" action="{{ route('admin.claims.reply', $contactThread->thread_uuid) }}">
                    @csrf
                    <label>Raspuns catre client
                        <textarea name="message" rows="5" required placeholder="Scrie raspunsul companiei...">{{ old('message') }}</textarea>
                    </label>
                </form>
                <div class="thread-action-row">
                    <form method="POST" action="{{ route('admin.claims.close', $contactThread->thread_uuid) }}" data-confirm="Închei pretenția? După încheiere nu se mai pot trimite mesaje.">
                        @csrf
                        @method('PATCH')
                        <button class="secondary-btn" type="submit">Încheie Pretenția</button>
                    </form>
                    <button class="primary-btn" type="submit" form="admin-thread-reply-form">Trimite raspuns</button>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
