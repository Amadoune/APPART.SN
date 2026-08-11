<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>APPART.TEST</title>
    <style>
        body { margin: 0; background: #f4f6f8; color: #17202a; font-family: system-ui, sans-serif; }
        main { max-width: 42rem; margin: 8vh auto; padding: 2rem; background: white; border-radius: 1rem; box-shadow: 0 1rem 3rem #17202a14; }
        h1 { margin-top: 0; }
        dl { display: grid; grid-template-columns: max-content 1fr; gap: .75rem 1.5rem; }
        dt { font-weight: 700; }
        dd { margin: 0; }
    </style>
</head>
<body>
<main>
    <h1>APPART.TEST</h1>
    <p>Bootstrap local OK</p>
    <dl>
        <dt>PHP</dt><dd>{{ $phpVersion }}</dd>
        <dt>Laravel</dt><dd>{{ $laravelVersion }}</dd>
        <dt>Environnement</dt><dd>{{ $environment }}</dd>
        <dt>PostgreSQL</dt><dd>{{ $postgresql }}</dd>
    </dl>
</main>
</body>
</html>
