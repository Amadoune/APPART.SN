# Property Lifecycle HTTP API Error Contract

Les erreurs de transport utilisent le format de validation JSON Laravel existant et n'appellent jamais l'orchestrateur.

Les refus applicatifs exposent uniquement :

```json
{"status":"denied","diagnostic":"transition_forbidden"}
```

Les conflits et indisponibilités suivent la même enveloppe minimale. Aucun message PostgreSQL, exception, stack trace, contenu Outbox, détail Worker ou Inbox n'est inclus. `PersistenceFailure` expose seulement son diagnostic applicatif certifié et retourne 503.
