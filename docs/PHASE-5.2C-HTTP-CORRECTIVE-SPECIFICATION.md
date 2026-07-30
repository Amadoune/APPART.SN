# Phase 5.2C — HTTP Foundation Corrective Specification

## Statut

**REPRÉSENTATION CORRECTIVE — NO GO PROPOSÉ.**

## Baseline contractuelle

Les seules frontières cross-domain autorisées sont désormais :

- `ProfessionalMandateResolverV1` — owner Professional Core ;
- `ProfessionalPublicStatusReaderV1` — owner F-05.

L’enchaînement normatif est :

```text
AccountId issu de la session IAM
→ ProfessionalMandateResolverV1
→ ProfessionalId lorsque Resolved
→ ProfessionalPublicStatusReaderV1
→ Available
→ ProfessionalProfileRuntimeV1
→ mapping HTTP
```

Tout résultat autre que `Resolved` ou `Available` ferme la requête sans exposer
la cause interne.

## État réel du dépôt

Les deux contrats et leurs résultats fermés existent. En revanche :

- aucune implémentation de `ProfessionalMandateResolverV1` n’existe ;
- aucune implémentation de `ProfessionalPublicStatusReaderV1` n’existe ;
- aucun binding Laravel ne les rend résolvables ;
- aucun controller, request, route ou Runtime HTTP Professional Profile
  n’existe.

Les amendements ont explicitement certifié les frontières contractuelles
seulement. Ils n’ont autorisé ni adapter, ni binding, ni composition Runtime.

## Conséquence

Une couche HTTP créée dans cet état dépendrait de services non résolvables en
production. Des tests utilisant des fakes prouveraient uniquement le mapping,
pas l’exécutabilité de la Foundation. Un Null Object fail-closed ou une
implémentation dans HTTP absorberait illégalement les owners.

La contrainte « ne pas ouvrir Runtime, Binding ou implémentation
supplémentaire » interdit précisément les travaux nécessaires pour lever ce
dernier gate.

## Surface HTTP cible après autorisation

Après certification des implémentations owner et de leurs bindings, HTTP devra :

- consommer exclusivement les deux contrats publics puis
  `ProfessionalProfileRuntimeV1` ;
- auto-scoper toutes les mutations depuis la session IAM ;
- refuser tout `ProfessionalId` privé fourni par le client ;
- appliquer validation stricte, `Idempotency-Key`, erreurs fermées, no-store,
  nosniff et rate limiting HMAC ;
- ne contenir aucune logique métier ou lecture cross-domain.

Cette section spécifie la cible et n’autorise aucune implémentation.

## Décision proposée

**NO GO.**

Les contrats sont nécessaires et correctement certifiés, mais ils ne disposent
pas encore des implémentations et bindings owner permettant une HTTP Foundation
exécutable.
