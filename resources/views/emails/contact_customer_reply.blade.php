<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #172033; margin: 0; padding: 24px; }
        .card { max-width: 720px; margin: auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 45px rgba(28, 44, 74, .12); }
        .header { background: #0969ff; color: #ffffff; padding: 28px; }
        .content { padding: 28px; }
        .message { white-space: pre-line; line-height: 1.6; padding: 18px; border-left: 5px solid #ffd21e; background: #f8fafc; border-radius: 12px; }
        .button { display: inline-block; margin-top: 20px; padding: 13px 18px; border-radius: 12px; background: #0969ff; color: #ffffff !important; text-decoration: none; font-weight: 800; }
        .muted { color: #6b7280; font-size: 12px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Răspuns de la ReclamDesign Modern</h1>
            <p>Fir: <strong>{{ $thread->thread_uuid }}</strong></p>
        </div>
        <div class="content">
            <p>Bună, {{ $thread->fullName() }}!</p>
            <p>Am revenit cu un răspuns la solicitarea ta:</p>

            <div class="message">{{ $contactMessage->body }}</div>

            <a class="button" href="{{ $customerUrl }}">Continuă conversația</a>
            <p class="muted">Mesajele trimise prin butonul de mai sus rămân salvate în același fir de conversație.</p>
        </div>
    </div>
</body>
</html>
