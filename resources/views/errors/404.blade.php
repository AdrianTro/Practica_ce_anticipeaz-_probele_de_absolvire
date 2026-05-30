<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Pagina nu a fost găsită</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #000;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .not-found {
            width: 100%;
            padding: 32px;
            text-align: center;
        }

        .not-found img {
            width: min(280px, 45vw);
            height: auto;
            margin-bottom: 28px;
        }

        .not-found h1 {
            margin: 0;
            font-size: clamp(64px, 5vw, 120px);
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.06em;
        }

        .not-found p {
            margin: 14px 0 0;
            font-size: clamp(20px, 4vw, 32px);
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="not-found">
        <img src="{{ asset('images/logo/logo_404.png') }}" alt="Logo">
        <h1>404</h1>
        <p>Pagina nu a fost găsită</p>
    </main>
</body>
</html>
