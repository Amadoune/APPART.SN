# Phase 5.2A — Completeness Policy V1

## Principe

La complétude est une décision pure, versionnée et non un statut lifecycle.
Elle n'autorise pas elle-même la publication.

## Entrées

- vue minimale Property : type, surface selon type, rooms, bathrooms, Place
  usable, Property availability ;
- draft : title, description, transactionKind, price/currency selon type,
  disponibilité et contactPreference ;
- Media : `Eligible`, `Ineligible` ou `Unknown`.

## Résultats fermés

- `Complete(policyVersion, evidenceChecksum)` ;
- `Incomplete(policyVersion, missingCodes)` ;
- `Unavailable(dependencyCode)`.

Codes manquants V1 : `PROPERTY_FACTS`, `PROPERTY_ADDRESS`, `TITLE`,
`DESCRIPTION`, `TRANSACTION_KIND`, `PRICE`, `CURRENCY`, `MEDIA`.

`Unknown` est fail-closed pour une soumission. La policy V1 est
`property-listing-authoring-completeness-v1`.

## Déterminisme

À entrées canoniques identiques, le résultat et l'evidenceChecksum sont
identiques. Aucun texte libre n'est inclus dans le checksum exposé.
