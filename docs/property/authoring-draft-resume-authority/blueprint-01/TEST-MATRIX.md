# Test Matrix

## Unit

- snapshot déterministe ;
- règles de step ;
- zéro mutation ;
- chaque résultat fermé ;
- Geography inchangée sans proof fabriqué.

## Feature

- 401 sans session ;
- 404 cross-owner et UUID absent ;
- zéro/un/plusieurs drafts avec sélection explicite ;
- URL de reprise valide ;
- bootstrap sans nouvelles identités ;
- délégation VIEW/EDIT existante.

## PostgreSQL

- vrai Portfolio + Property + Draft + Aggregate + Workflow + Media ;
- versions conservées ;
- GeographicPlaceId et AddressIntentId conservés ;
- aucune ligne créée/modifiée ;
- incohérences fail-closed.

## Architecture

- aucune dépendance Projection/Search/Promotion ;
- aucun SQL dans Application/HTTP ;
- aucune nouvelle identité ;
- owner issu uniquement de la session.
