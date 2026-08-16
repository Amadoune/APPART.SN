# Audit de l’autorité Session

`RequireIdentityAccessSession` lit exclusivement le cookie configuré, appelle `IdentityAccessHttpRuntime::inspectSession()` et refuse si l’inspection n’est pas valide ou ne fournit pas `AccountId` et `AuthenticatedSessionContext`.

En succès, le middleware transporte :

- une identité authentifiée ;
- un contexte de session déterminé ;
- l’`AccountId` autoritatif.

Il ne transporte et ne décide aucun rôle ou capability. Il n’accorde pas un statut métier « owner » global.

Le Runtime accepte uniquement les sessions réduites `Valid` ou `RotationRequired`. Les secrets invalides, sessions expirées/révoquées ou états indisponibles sont refusés.
