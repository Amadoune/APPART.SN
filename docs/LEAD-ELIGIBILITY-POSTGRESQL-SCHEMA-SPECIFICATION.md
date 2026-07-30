# Lead Eligibility PostgreSQL Schema Specification

Table : `contacts_leads.lead_eligibility_decisions`.

| Colonne | Invariant |
|---|---|
| `listing_id` | UUID, identité du flux |
| `version` | strictement positive, clé primaire avec Listing |
| `normative_advertiser_id` | UUID nullable, zéro ou un destinataire |
| `listing_decision` | enum historique fermé |
| `evaluated_advertiser_id` | UUID explicite |
| `advertiser_decision` | enum historique fermé |
| `coherence_id` | UUID explicite, unique par Listing |
| `effective_at` | `timestamptz(6)` explicite, sans valeur par défaut |
| `materialization_checksum` | SHA-256 canonique |

Aucune colonne n'utilise `DEFAULT`, `now()`, génération UUID ou horloge PostgreSQL.
