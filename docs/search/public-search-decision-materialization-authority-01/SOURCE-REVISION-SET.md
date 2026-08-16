# Source Revision Set

`SourceRevisionSet` exige exactement Listing, Property et Media. Chaque `SourceRevision` contient : source, version positive, UUID factId et effectiveAt.

Pour le Listing RC2 :

- Listing : la révision Published `e40a5c74-d8cd-549b-8793-a8ea715de533`, séquence 4, à `2026-08-15T09:22:24.151935+02:00` est une candidate autoritative ;
- Property : l'Aggregate canonique est version 0, donc incompatible tel quel avec l'exigence positive; la promotion porte authoringVersion 1 mais aucune décision ne l'a qualifiée comme révision Search ;
- Media : la collection `71fae610-6d12-5ad2-9c13-572c8eb1658c` est version 1, avec média principal actif.

Le set n'est donc pas entièrement qualifiable sans une autorité de mapping Property et sans adapters SearchDiscovery owner-scoped. Aucun instant de lecture canonique commun n'est décidé ici.

## Completion 01

Constat historique fermé. La révision Property est le fait owner du ledger de promotion : `authoringVersion` positive, `commandId` UUID et `occurredAt`. Listing utilise sa révision Domain ; Media utilise la version, l'identité et `lastChangedAt` de la collection. Aucune révision synthétique.
