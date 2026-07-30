# Public Listing Projection Runtime Source

## API

- `findByListingId()` satisfait directement `PublicListingProjectionSource` et alimente l'Updater certifié ;
- `inspect()` retourne l'explication typée, les sources assemblées, le watermark et la readiness.

Un résultat `Found` peut être soit `Ready`, soit explicitement incomplet lorsque Geography ou Media manque. Tous les autres statuts ne contiennent aucune source partielle exploitable.

## Blocages explicites

Le vocabulaire couvre notamment les identités invalides, Listing ou Property absents, ownership Media absent ou ambigu, MediaCollection absente, Search ou Content/SEO absents/corrompus, génération Active absente/corrompue, `decisionAt` absent/corrompu/divergent et décisions publiques corrompues.

## Compatibilité Updater

L'objet retourné est exactement `PublicListingProjectionSources`. L'Updater conserve donc seul la construction Search/SEO/ReadModel et l'écriture dans le Projection Store. Aucun contrat certifié n'est modifié.
