# Phase 5.1E — Account Availability Policy

## Contrat V1

Entrée : `AccountId`, purpose fermé et `observedAt`.

Purposes :

- Authenticate ;
- RenewSession ;
- RecoverPassword ;
- MutateProfile ;
- RequestClosure ;
- ReopenClosure.

Résultats fermés :

- Available ;
- UnavailableSuspended ;
- UnavailableClosed ;
- AccountMissing ;
- Inconsistent ;
- Indeterminate.

## Précédence

1. exception ou source rejetée : `Indeterminate` ;
2. absence cohérente de l'Account : `AccountMissing` ;
3. contradiction entre existence et sources : `Inconsistent` ;
4. Status Suspended : `UnavailableSuspended` ;
5. pour ReopenClosure, Closed est la seule entrée disponible ;
6. ClosureRequested ou Closed interdit les autres purposes ;
7. Status Active avec Closure Open/Reopened : `Available`.

Reopened ne neutralise jamais Suspended. Aucun consumer ne peut convertir
`Indeterminate` ou `Inconsistent` en disponibilité.

## Diagnostics

Le résultat interne conserve :

- policy version `account-availability-v1` ;
- observedAt ;
- Status et sa version ;
- Closure et sa version.

Il ne contient aucune PII. Les futures réponses Authentication/Recovery devront
mapper ces diagnostics vers un résultat public non énumérant.

## Persistence

Availability ne possède aucune table. L'absence additive d'une ligne Closure
est interprétée `LegacyOpen`, version 0, seulement si les autres sources sont
cohérentes.
