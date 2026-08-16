# Final trigger and handoff

## Initial

`ListingPublished` est le trigger initial : ListingId → Property → Address → terminal Place → V2 → writer.

## Refresh

Toute mutation publique d'un membre du vecteur doit réévaluer ses décisions terminales descendantes. Le transport existant ne couvre que enabled/disabled/merged; `PlaceRenamed` Domain n'est pas livré à ce consumer. Aucun contrat n'énumère les terminaux/décisions dont la chaîne contient un ancêtre.

Le refresh trigger n'est donc pas fermé. Aucun polling ou replay global implicite n'est accepté.
