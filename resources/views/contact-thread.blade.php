@extends('layouts.app')

@section('title', 'Conversatie contact | ReclamDesign Modern')

@section('content')
<section class="section-shell contact-thread-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Contactare</span>
            <h1>Conversatia ta</h1>
            <p>{{ $contactThread->fullName() }} · {{ $contactThread->email }}</p>
        </div>
        <a class="secondary-btn" href="{{ route('contacte') }}">Contacte</a>
    </div>

    <div class="thread-layout">
        <aside class="thread-sidebar">
            <span class="eyebrow">Solicitare</span>
            <h2>{{ $contactThread->thread_uuid }}</h2>
            <div class="thread-meta">
                <span class="status-pill {{ $contactThread->status === 'closed' ? 'invalid' : 'valid' }}">
                    {{ $contactThread->status === 'closed' ? 'Încheiat' : 'Activ' }}
                </span>
                <span>Creat: {{ $contactThread->created_at->format('d.m.Y H:i') }}</span>
                <span>Ultimul mesaj: {{ optional($contactThread->last_message_at)->format('d.m.Y H:i') ?: '-' }}</span>
            </div>
        </aside>

        <div class="thread-panel">
            <div class="message-list">
                @foreach($contactThread->messages as $message)
                    <article class="chat-row {{ $message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'from-company' : 'from-customer' }}">
                        <div class="chat-bubble">
                            <strong>{{ $message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'ReclamDesign Modern' : $contactThread->fullName() }}</strong>
                            <p>{!! nl2br(e($message->body)) !!}</p>
                            <span>{{ $message->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($contactThread->status === 'closed')
                <div class="thread-closed-note">Pretenția este încheiată. Conversația rămâne vizibilă, dar nu mai pot fi trimise mesaje.</div>
            @else
                <form class="thread-reply-form" method="POST" action="{{ route('contacte.thread.message', $contactThread->public_token) }}">
                    @csrf
                    <label>Continua conversatia
                        <textarea name="description" rows="4" required placeholder="Scrie un mesaj nou...">{{ old('description') }}</textarea>
                    </label>
                    <button class="primary-btn" type="submit">Trimite mesaj</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
