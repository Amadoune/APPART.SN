# Phase 5.1J — Global Compatibility Matrix

| Frontière | Lecture autorisée | Écriture autorisée | Garantie finale |
|---|---|---|---|
| Historical Account / Snapshot V1 | Seed 5.1D uniquement | interdite | inchangé |
| Account / AccountRegistry | contrats historiques publiés | aucune extension 5.1 | inchangé |
| Account Status | lecture Availability | aucune | V1 gelé |
| Profile | owner Profile | store Profile uniquement | canonique après cutover |
| Identity Claims | owner Claims | store Claims uniquement | unicité atomique |
| Sessions | owner Sessions | sessions/checkpoint uniquement | secrets hashés |
| Closure | owner Closure | store Closure uniquement | distinct de Suspended |
| Runtime Health historique | aucune extension | interdite | 58 capacités |
| Events IAM | six types V1 | producteurs Profile/Closure | sans PII |
| Outbox IAM | reader propriétaire | writer propriétaire | distincte de 043 |
| HTTP IAM | ports Runtime | aucune SQL directe | fail-closed |

Il n'existe aucune double autorité, FK cross-domain, cascade, dépendance
Application vers Infrastructure ou dépendance circulaire nouvelle.
