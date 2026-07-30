# ADR-1012 — Historical Canonical Qualification Boundary

## Statut

Accepté pour le Sprint 3.10CA.

## Décision

La qualification Current/Historical appartient à `ContentSeo` et précède la construction de `HistoricalCanonical`. Le port accepte `CanonicalUrl`, déjà normalisée et publique, puis retourne un résultat fermé. Seul le résultat `Historical` contient l'identité exigée par `HistoricalRedirectResolver`.

## Conséquences

Le Web pourra ultérieurement orchestrer des ports sans interpréter une canonical. Une absence dans `PublicListingQuery` ne sera jamais assimilée implicitement à une identité historique. La persistance et la composition seront traitées dans 3.10CB et 3.10CC.

## Alternatives écartées

- Construire `HistoricalCanonical` dans HTTP transférerait une décision Content/SEO au Web.
- Étendre `PublicListingQuery` casserait son caractère Current-only.
- Retourner un booléen masquerait Unknown, Ambiguous et Corrupted.
