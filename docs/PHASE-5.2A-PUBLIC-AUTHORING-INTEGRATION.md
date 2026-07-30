# Phase 5.2A — Public Authoring Integration

## Statut certifié

**GO CERTIFIÉ — FERMÉ.**

## Parcours public

La façade versionnée `/api/public-authoring/v1/{operation}` expose les sept
étapes certifiées :

- initiation et mise à jour Property ;
- création complète Listing ;
- édition du draft ;
- ajout et retrait de délégation ;
- soumission vers F-01.

`PublicAuthoringJourney` traduit chaque requête vers le contrat certifié
`PropertyListingAuthoringOperations`. Il n’accède ni au Runtime, ni aux stores,
ni directement à F-01.

## UI

L’espace privé `/authoring/workspace` fournit un parcours progressif pour
initier un bien, créer ou modifier un brouillon et demander sa soumission.
L’interface ne collecte jamais l’AccountId. Elle utilise :

- le cookie de session IAM existant ;
- le jeton CSRF Laravel ;
- `credentials: same-origin` ;
- un `Idempotency-Key` UUID généré pour chaque étape ;
- des payloads filtrés par opération.

Le build Vite de production est vérifié. Les dépendances frontend sont
verrouillées par `package-lock.json`.

## Sécurité API

- session IAM obligatoire et fail-closed ;
- auto-scope exclusif depuis la session ;
- validation fermée et champs hors opération interdits ;
- version attendue et horodatage explicite ;
- rate limiting par empreinte HMAC sans PII ;
- réponses `no-store`, `nosniff` et sans diagnostic interne ;
- résultat non trouvé/non autorisé homogène ;
- opérations inconnues rejetées par la route fermée.

## Frontières

L’intégration est additive : les endpoints HTTP certifiés précédents ne sont pas
modifiés. Les migrations 001–057, Runtime Health à 58 capacités, Event V1,
Delivery, Outbox et les capacités F-01, F-02, F-11, F-14, F-15, F-17 et F-18
restent inchangés.
