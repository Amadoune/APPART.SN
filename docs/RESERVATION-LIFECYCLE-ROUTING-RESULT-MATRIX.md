# Reservation Lifecycle Routing Result Matrix

| Situation | Résultat | Écriture nouvelle |
|---|---|---:|
| enveloppe cohérente et `messageId` absent | `Stored` | oui, exactement une |
| enveloppe identique déjà persistée | `AlreadyStored` | non |
| enveloppe incohérente avant store | `CorruptedEnvelope` | non |
| même `messageId`, contenu persistant divergent | `CorruptedEnvelope` | non |
| violation PostgreSQL de données ou d'intégrité | `CorruptedEnvelope` | non validée |
| exception technique du store | `PersistenceCorrupted` | aucune garantie exposée |
| panne PostgreSQL ou erreur technique non classifiable | `PersistenceCorrupted` | aucune garantie exposée |

Les statuts proviennent exclusivement de la validation de l'enveloppe et de la persistance. Ils ne décrivent aucune décision métier, livraison Outbox ou consommation.
