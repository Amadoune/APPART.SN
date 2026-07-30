# Phase 5.1D — Authority Cutover Plan

## États

```text
Historical
  ├─ divergence ──> Historical + Quarantined
  └─ seed complet ─> Profile

Profile
  ├─ rejeu identique ─> Profile
  └─ rollback sûr ────> Historical
```

Le registre `profile_claim_authority` est l'unique preuve du transfert. Avant
commit, Historical reste canonique. Après commit, Profile/Claims deviennent
canoniques. Aucun état « les deux » n'existe.

## Transaction de commit

La même transaction :

1. ouvre le run `Prepared` ;
2. écrit tous les Profiles et Claims ;
3. écrit chaque ligne de manifest ;
4. effectue le compare-and-set `Historical → Profile` ;
5. clôt le run `Committed`.

Tout échec annule les cinq effets. Account Availability et le Runtime restent
hors de 5.1D ; ils consommeront le registre seulement après ouverture de 5.1E.

## Rapport

Le run conserve version de Snapshot, version de normalisation, compte source,
comptes Profile/Claim, nombre de divergences, checksum, timestamps et état.
Le rapport ne contient aucune donnée personnelle.
