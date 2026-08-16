# Current RC2 Evidence

Listing : `979cd5aa-ced1-48a1-8adf-8b29c843a0c2`.

## Faits présents

- Aggregate Listing `published` v3 ;
- révision Published séquence 4, id `e40a5c74-d8cd-549b-8793-a8ea715de533`, instant `2026-08-15T09:22:24.151935+02:00` ;
- Workflow publication v4 ;
- Property `5797a9b5-088d-43ea-8854-cac441946f6b`, active, apartment, v0 ;
- handoff public authoring v1, transaction `sale`, published revision concordante ;
- MediaCollection `71fae610-6d12-5ad2-9c13-572c8eb1658c` v1 ;
- média principal actif `1586b2bc-48ab-57b7-948a-9d9a19813ca8` ;
- zéro `search_discovery.public_search_decisions` ;
- zéro Projection pour le Listing.

## Suffisance

Les faits suffisent à démontrer Published, Property Available et Media Ready. Ils ne suffisent pas à produire une décision : aucun rank n'est présent, aucune sélection productive de facettes n'est certifiée et la révision Property positive n'est pas résolue.

Aucune écriture n'a été effectuée.

## Completion 01

Les lacunes historiques sont fermées : policy v1 produit rang `0` et facettes `[]`; le ledger de promotion fournit la révision Property positive. Avec les révisions Listing et Media déjà présentes, le Listing est matériellement rattrapable par la future Implementation, sans écriture dans cette Authority.

Lecture PostgreSQL ciblée de Completion :

- Listing `published`, Aggregate v3 ;
- révision Listing séquence 4, factId `e40a5c74-d8cd-549b-8793-a8ea715de533`, effectiveAt `2026-08-15T09:22:24.151935+02:00` ;
- promotion Property `applied`, authoringVersion 1, factId `ef9e24b4-c42e-4221-8fe0-3f2e52cec2e6`, effectiveAt `2026-08-15T09:16:27.907000+02:00` ;
- MediaCollection version 1, factId `71fae610-6d12-5ad2-9c13-572c8eb1658c`, effectiveAt `2026-08-15T09:01:01.000000+02:00` ;
- aucune ligne `public_search_decisions`.

Selon la formule UUIDv5 certifiée, l'identité candidate v1 du Listing est `20d5ab5a-45ac-5a5e-9f15-d1357e9105a9` et sa première version sera `1`. Ce calcul est pur et n'a créé aucune ligne.
