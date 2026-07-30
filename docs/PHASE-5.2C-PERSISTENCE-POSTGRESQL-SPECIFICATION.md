# Phase 5.2C — Persistence PostgreSQL Specification

## Migration 061

`061_professional_profile.sql` est additive et crée exclusivement le schéma
owner-scoped `professional_profile`. Son rollback supprime les tables dans
l’ordre inverse puis le schéma.

## Concurrence

Chaque écriture :

1. rejoint ou ouvre une transaction locale ;
2. acquiert `pg_advisory_xact_lock(hashtextextended(professionalId, 0))` ;
3. inspecte l’intent permanent ;
4. vérifie expected version et séquence/checkpoint ;
5. écrit snapshot et historique ;
6. enregistre l’intent ;
7. commit uniquement si elle possède la transaction.

Un même intent/checksum converge en `AlreadyApplied`. Un checksum différent
converge en `DivergentIntent`. Aucune écriture partielle ne survit au rollback.

## Types et confidentialité

- identités : UUID ;
- checksums : SHA-256 hexadécimal `char(64)` ;
- collections : JSONB avec type contrôlé ;
- contacts publics : objet JSONB, y compris vide ;
- preuves : références opaques en tableau JSONB ;
- timestamps : `timestamptz(6)` ;
- aucune donnée de session, credential, document brut ou diagnostic.

## Portfolio

`listing_ids` contient uniquement des références opaques. Il n’existe ni FK
vers Listing, ni trigger, ni écriture dans les tables Listing. Le rebuild
remplace localement la projection à checkpoint monotone.

## Rollback

Le down 061 supprime uniquement les objets 5.2C. Il ne touche aucune migration
001–060 et n’emploie aucune cascade.
