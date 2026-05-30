<?php

namespace App\Http\Controllers;

use App\Mail\ContactThreadNotification;
use App\Models\ContactMessage;
use App\Models\ContactThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('contacte');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->contactRules(), $this->contactMessages());

        [$thread, $message] = DB::transaction(function () use ($data): array {
            $thread = ContactThread::query()->create([
                'thread_uuid' => $this->uniqueThreadUuid(),
                'public_token' => $this->uniquePublicToken(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'status' => 'open',
            ]);

            $message = $thread->messages()->create([
                'sender' => ContactMessage::SENDER_CUSTOMER,
                'body' => $data['description'],
            ]);

            $thread->update(['last_message_at' => $message->created_at]);

            return [$thread->refresh(), $message];
        });

        $emailWarning = $this->sendCompanyNotification($thread, $message, true);

        $redirect = redirect()
            ->route('contacte.thread.show', $thread->public_token)
            ->with(
                'success',
                $emailWarning
                    ? 'Mesajul a fost salvat. Conversatia ta este deschisa mai jos.'
                    : 'Mesajul a fost trimis si salvat. Conversatia ta este deschisa mai jos.'
            );

        if ($emailWarning) {
            $redirect->with('warning', $emailWarning);
        }

        return $redirect;
    }

    public function showThread(ContactThread $contactThread): View
    {
        $contactThread->load(['messages' => fn ($query) => $query->oldest()]);

        return view('contact-thread', compact('contactThread'));
    }

    public function storeThreadMessage(Request $request, ContactThread $contactThread): RedirectResponse
    {
        if ($contactThread->status === 'closed') {
            return back()->with('warning', 'Pretenția este încheiată. Nu mai poți trimite mesaje în această conversație.');
        }

        $data = $request->validate([
            'description' => ['required', 'string'],
        ], [
            'description.required' => 'Scrie mesajul inainte de trimitere.',
        ]);

        $message = DB::transaction(function () use ($contactThread, $data): ContactMessage {
            $message = $contactThread->messages()->create([
                'sender' => ContactMessage::SENDER_CUSTOMER,
                'body' => $data['description'],
            ]);

            $contactThread->update([
                'status' => 'open',
                'last_message_at' => $message->created_at,
            ]);

            return $message;
        });

        $emailWarning = $this->sendCompanyNotification($contactThread->refresh(), $message);

        $redirect = back()->with('success', 'Mesajul tau a fost adaugat la conversatie.');

        if ($emailWarning) {
            $redirect->with('warning', $emailWarning);
        }

        return $redirect;
    }

    private function contactRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'description' => ['required', 'string'],
            'contact_form' => ['nullable', 'string'],
        ];
    }

    private function contactMessages(): array
    {
        return [
            'first_name.required' => 'Introduce numele.',
            'last_name.required' => 'Introduce prenumele.',
            'email.required' => 'Introduce emailul.',
            'email.email' => 'Introduce un email valid.',
            'description.required' => 'Descrie problema sau necesitatea.',
        ];
    }

    private function sendCompanyNotification(ContactThread $thread, ContactMessage $message, bool $isInitial = false): ?string
    {
        try {
            Mail::to(config('mail.company_address'))->send(new ContactThreadNotification($thread, $message, $isInitial));

            return null;
        } catch (\Throwable $exception) {
            Log::error('Email contact nereusit', [
                'thread_uuid' => $thread->thread_uuid,
                'message_id' => $message->id,
                'error' => $exception->getMessage(),
            ]);

            return 'Mesajul a fost salvat, dar emailul catre companie nu a putut fi trimis. Verifica setarile SMTP.';
        }
    }

    private function uniqueThreadUuid(): string
    {
        do {
            $uuid = (string) random_int(100, 9999);
        } while (ContactThread::query()->where('thread_uuid', $uuid)->exists());

        return $uuid;
    }

    private function uniquePublicToken(): string
    {
        do {
            $token = Str::random(48);
        } while (ContactThread::query()->where('public_token', $token)->exists());

        return $token;
    }
}
