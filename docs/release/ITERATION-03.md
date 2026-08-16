# RC2 Stabilization — Iteration 03

## Périmètre

Cette itération traite exclusivement `Publication Review Queue → Claim → Claim persistence → Command ledger → replay`.

BeginReview, Approve, Projection, Search et Public Listing n'ont été ni analysés ni exécutés.

## Première divergence

Le premier Claim était appliqué et persisté. Le replay du même formulaire retournait toutefois HTTP 409 au lieu de `AlreadyApplied`.

`PostgreSqlPublicationReviewQueue` inclut légitimement `occurredAt` dans le checksum canonique de la commande. Le contrôleur reconstruisait cet instant avec `new DateTimeImmutable` à chaque requête : le même `commandId` recevait donc un checksum différent au replay et était réduit en `DivergentCommand`.

## Correction unique

Le formulaire Claim transporte désormais un `occurredAt` figé avec microsecondes et offset. La Request le valide strictement et le contrôleur transmet cet instant au contrat `ClaimPublicationReviewV1`.

La persistence Claim, le ledger, les règles de replay et l'optimistic locking ne sont pas modifiés.

## Preuve terminale

Listing réel : `72b990e2-6b32-44ab-93c3-0ce688c0b18d`.

`HTTPS → Owner Login → Upload HTTP 201 → Preview → Submit HTTP 200 → Queue → Claim HTTP 200 → replay identique HTTP 200 / AlreadyApplied`

Le premier Claim affiche l'état assigné. Le replay réutilise exactement le même `commandId`, la même version et le même `occurredAt`. La campagne s'arrête immédiatement avant BeginReview.

Preuves externes : `RC2-STABILIZATION/iteration-03/ITERATION-03-MANIFEST.json`, `05-claim.png` et `TRACE.zip`.

## Verdict

**GO PROPOSÉ — ITERATION 03**
