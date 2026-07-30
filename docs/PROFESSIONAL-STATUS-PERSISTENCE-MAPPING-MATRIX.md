# Professional Status Persistence Mapping Matrix

| Donnée contractuelle | Colonne PostgreSQL |
|---|---|
| `ProfessionalStatusId` | `professional_id uuid` |
| version métier | `version bigint` |
| état source | `previous_state text` |
| état résultant | `current_state text` |
| action | `action text` |
| intégrité canonique | `transition_checksum char(64)` |

Le checksum SHA-256 couvre, dans un ordre fixe, identité, version, état précédent, état courant et action. Aucun timestamp, acteur ou identifiant aléatoire n'y participe.
