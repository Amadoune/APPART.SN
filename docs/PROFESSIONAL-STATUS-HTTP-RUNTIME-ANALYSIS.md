# Professional Status HTTP Runtime Analysis

Le Sprint 4.5J ajoute exclusivement la frontière HTTP de la capacité Professional Status. L'adaptateur valide le transport, construit la requête atomique certifiée 4.5I, délègue une seule fois à `ProfessionalStatusAtomicEventOrchestrator`, puis traduit son résultat fermé.

Le contrôleur ne connaît ni le workflow, ni les stores, ni PostgreSQL, ni l'Inbox, ni l'Outbox. Il n'ouvre aucune transaction et ne produit aucun événement. L'atomicité, l'idempotence et la concurrence restent intégralement la responsabilité de la chaîne 4.5I.

Endpoint unique :

```text
POST /api/professional-statuses/{professionalId}/transitions
```

Le paramètre de route est borné par UUID. Les seules actions transportables sont `suspend` et `reactivate` ; `unknown` n'est jamais exécutable.
