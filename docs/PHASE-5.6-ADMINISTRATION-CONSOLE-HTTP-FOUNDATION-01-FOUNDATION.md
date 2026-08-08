# Administration Console HTTP Foundation

## Périmètre

La Foundation matérialise exclusivement la façade HTTP publique de `AdministrationConsole` :

- `AdministrationOperatorController` consommant `AdministrationOperatorReaderV1` ;
- `AdministrationQueueController` consommant `AdministrationQueueReaderV1` ;
- `AdministrationAuditController` consommant `AdministrationAuditReaderV1` ;
- trois Form Requests dédiées ;
- `AdministrationConsoleResponseFactory` ;
- `AdministrationConsoleHttpRuntimeV1` ;
- `AdministrationConsoleHttpServiceProvider`.

## Endpoints

| Endpoint GET | Controller |
|---|---|
| `/api/administration/operator` | `AdministrationOperatorController` |
| `/api/administration/queue` | `AdministrationQueueController` |
| `/api/administration/audit` | `AdministrationAuditController` |

Les requêtes acceptent exclusivement `subjectKey` et `observedAt`. Les réponses exposent uniquement `status`, avec `Cache-Control: no-store` et `X-Content-Type-Options: nosniff`.

## Composition

Le Provider enregistre quatre singletons lazy : ResponseFactory et trois Controllers. Il est enregistré une seule fois dans `bootstrap/providers.php`.

Les Controllers dépendent uniquement des Readers publics V1 et de la ResponseFactory. Ils ne connaissent ni `AdministrationConsoleOwnerSource`, ni Runtime interne, ni Persistence, ni Infrastructure.
