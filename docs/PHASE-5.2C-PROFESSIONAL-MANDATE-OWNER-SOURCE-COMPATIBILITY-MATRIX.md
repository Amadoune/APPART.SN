# Phase 5.2C — Owner Source Compatibility Matrix

| Exigence | Réalisation | État |
|---|---|---|
| owner Professional Core | schéma et contrats Professionals | conforme |
| read-only pour futur resolver | `ProfessionalMandateOwnerSource::resolve` | conforme |
| déterminisme | liste canonique triée et unique | conforme |
| fail-closed | résultats fermés hors Resolved | conforme |
| contrat V1 inchangé | aucune modification du resolver public | conforme |
| Aggregate non exposé | ProfessionalId uniquement | conforme |
| Representative non exposé | aucun type ou champ | conforme |
| établissement non exposé | aucun type ou champ | conforme |
| SQL non exposé | confiné à Infrastructure | conforme |
| Runtime/HTTP absents | aucun binding, provider ou route | conforme |
| IAM inchangé | Account transporté comme UUID opaque | conforme |

## Mapping futur

| Source owner | Resolver V1 |
|---|---|
| Resolved | Resolved |
| NotMandated | NotMandated |
| Ambiguous | Ambiguous |
| Corrupted | Corrupted |
| DependencyUnavailable | DependencyUnavailable |

Ce mapping est bijectif et n’ajoute aucune décision métier.
