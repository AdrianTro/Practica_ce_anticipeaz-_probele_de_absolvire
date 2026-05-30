@extends('layouts.app')

@section('title', 'Pretentii | Admin')

@section('content')
<section class="section-shell admin-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1>Pretentii</h1>
            
        </div>
        <a class="secondary-btn" href="{{ route('admin.dashboard') }}">Inapoi</a>
    </div>

    <div class="claim-grid">
        @forelse($threads as $thread)
            <a class="claim-card {{ $thread->new_messages_count > 0 ? 'has-new-claim' : '' }}" href="{{ route('admin.claims.show', $thread->thread_uuid) }}">
                @if($thread->new_messages_count > 0)
                    <span class="claim-new-badge" aria-label="Pretentie noua">NEW</span>
                @endif
                <div>
                    <span class="status-pill {{ $thread->status === 'closed' ? 'invalid' : 'valid' }}">
                        {{ $thread->status === 'closed' ? 'Încheiat' : 'Activ' }}
                    </span>
                    <h2>{{ $thread->fullName() }}</h2>
                    <p>{{ $thread->email }}</p>
                </div>
                <div class="claim-meta">
                    <span>Trimis: {{ $thread->created_at->format('d.m.Y H:i') }}</span>
                    <span>Mesaje: {{ $thread->messages_count }}</span>
                    <span>Ultimul mesaj: {{ optional($thread->last_message_at)->format('d.m.Y H:i') ?: '-' }}</span>
                </div>
            </a>
        @empty
            <div class="empty-state">Nu exista mesaje primite.</div>
        @endforelse
    </div>

    <div class="pagination-wrap">{{ $threads->links() }}</div>
</section>
@endsection
