# Compatibility Matrix — SecurityCompliance Contracts

| Surface | Autorisé | Interdit | Statut |
|---|---|---|---|
| huit Readers V1 | SubjectKey, ObservedAt et Result dédié | écriture, commande, mutation | Compatible |
| Results V1 | statut fermé et observedAt UTC | secret, clé, PII, configuration | Minimal |
| catalogues | quatre états publics par Reader | fallback, ambiguïté, détail métier | Fermés |
| module Application | types internes au module | Infrastructure, Persistence, Runtime | Isolé |

Discovery 5.8A est GO CERTIFIÉ — FERMÉ. Aucune Foundation Runtime, HTTP, Event, Delivery ou Outbox n'est ouverte. Les capacités 5.1 à 5.7 et les migrations 084/085 restent gelées et inchangées.
