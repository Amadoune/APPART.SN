# Phase 5.2C — HTTP Foundation

## Statut

**GO PROPOSÉ.**

## Composition

La Foundation expose exclusivement :

- `GET /professional/mandate` ;
- `GET /professional/status` ;
- `ProfessionalProfileHttpController` ;
- `ProfessionalMandateHttpPresenter` ;
- `ProfessionalStatusHttpPresenter` ;
- `ProfessionalEndpointRuntimeV1` ;
- `ProfessionalProfileHttpServiceProvider`.

Le controller délègue intégralement à la Runtime et aux presenters. Les
presenters sont les seules autorités de mapping HTTP.

## Auto-scope

```text
Session IAM
→ AccountId
→ ProfessionalMandateResolverV1
→ ProfessionalId uniquement si Resolved
→ ProfessionalPublicStatusReaderV1
```

Pour `/professional/status`, les résolutions non positives convergent de manière
fail-closed :

- NotMandated → Missing ;
- Ambiguous ou Corrupted → Corrupted ;
- DependencyUnavailable → DependencyUnavailable.

Le reader F-05 n'est jamais appelé sans mandat `Resolved`.

## Sécurité

- session IAM obligatoire ;
- aucun `ProfessionalId` client ;
- rate limiting IAM authentifié ;
- réponses JSON fermées ;
- cache privé désactivé ;
- aucun diagnostic interne ;
- aucune lecture SQL, Aggregate ou reconstruction événementielle.

## Runtime Health

Le composant `ProfessionalEndpoint` porte la valeur publique
`professional_http`. Le provider HTTP étend l’inspecteur existant par
composition, sans modifier son provider propriétaire. Le catalogue historique
reste à 60 exigences et l’exigence HTTP additive demeure `Healthy`.

## Hors périmètre

Mutations de profil, migrations, persistence, Event, Transport, Delivery,
Consumer, Outbox, Search et Projection.
