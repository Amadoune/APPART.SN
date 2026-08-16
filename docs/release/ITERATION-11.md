# RC2 Stabilization — Iteration 11

## Final End-to-End Certification 01 — 2026-08-16

**GO PROPOSÉ — RC2 ITERATION 11 — GO CERTIFIÉ / FERMÉ.**

The historical NO GO records below remain unchanged. Their source blockers were closed by the certified Search, ContentSeo, Public Geography, Public Media and Active Generation gates. Final read-only evidence confirms Published/Published/Completed, canonical Property, source Found/Ready, one current Active projection, equivalent source/record, public read model Found and public page HTTP 200. Search UX/API remains unopened. The `NotReady -> confirmed` UI reduction remains a non-blocking residual defect.

Evidence: `docs/release/rc2-iteration-11-final-certification-01/`.

## Périmètre

Cette itération devait reprendre un parcours réel neuf jusqu’à `Published → Public Listing Projection`, sans réutiliser le Listing de l’itération 10 et sans ouvrir Search.

Le verdict historique de l’itération 10 reste inchangé : `NO GO — ProjectionSourceAssemblyStatus::PropertyMissing`. Les Foundations Property F0–F7 ont depuis fermé cette cause au niveau des capacités certifiées.

## Préflight réel

Le navigateur contrôlé ouvre `https://appart.test/` puis rejoint, par la navigation publique « Déposer une annonce », le formulaire réel `https://appart.test/connexion?next=%2Fauthoring%2Fworkspace`.

Le transport HTTPS et l’écran IAM sont disponibles. Aucune session owner n’est active dans ce navigateur.

## Première divergence

Le parcours ne peut pas franchir `Owner Login` : aucun credential clair autorisé n’est disponible dans la campagne. Le repository conserve l’identifiant des principals locaux mais, conformément à la politique IAM, ne conserve pas leur credential clair. Aucun mécanisme certifié opérable depuis cette campagne ne fournit ce secret.

État attendu : login owner réel, cookie Secure, redirection vers le workspace.

État réel : formulaire Login affiché, champs non soumis faute de credential autorisé.

Cause racine unique : **prérequis opérateur de démonstration absent — credential owner local autorisé non fourni à la campagne**.

## Décision fail-fast

La campagne s’arrête avant toute création de Property, Listing ou Media. Aucun ancien Listing n’est réutilisé. Aucune session, aucun cookie et aucune donnée ne sont injectés. Aucun compte, hash ou rôle n’est créé ou modifié.

La Projection, son replay et le contrôle de disparition de `PropertyMissing` ne sont donc pas atteints. Search demeure non ouvert.

## Correction

Aucune correction produit n’est démontrée ni appliquée. Le blocage est un prérequis d’exécution, pas une divergence des capacités F0–F7 ou de Projection.

## Verdict

**NO GO PROPOSÉ — ITERATION 11**

## Reopening 01 — reprise du 14 août 2026

Le prérequis historique de credential est levé : le principal Authoring local `a2110000-0000-4000-8000-000000000001` est certifié et aucune opération de provisioning n'a été rejouée.

La reprise fail-fast s'arrête toutefois au contrôle initial du workspace. Le navigateur contrôlable, depuis son onglet APPART.SN existant, refuse la navigation vers `https://appart.test/authoring/workspace` avec `net::ERR_BLOCKED_BY_CLIENT`, avant toute réponse HTTP.

- Étape : Workspace.
- Attendu : ouverture HTTPS du workspace avec la session IAM existante.
- Réel : navigation bloquée côté navigateur avant HTTP.
- Cause racine : politique ou composant du navigateur contrôlable bloquant la navigation HTTPS locale ; aucun composant APPART.SN n'est atteint.

Conformément à la règle absolue, aucune nouvelle Property, aucun Listing, aucun média et aucune commande n'ont été créés. Promotion, Queue, Claim, Review, Publication et Projection ne sont pas atteints. Search reste non ouvert. Aucune correction produit n'est appliquée.

**NO GO PROPOSÉ — ITERATION 11 — REOPENING 01**

## Reopening 02 — campagne `5a00c6ca-82f6-4574-aebe-821d0aa546c6`

La campagne a matérialisé son manifest avant navigation et a créé, par les chaînes IAM locales déjà certifiées, deux principals distincts :

- Authoring sans rôle : `4e992d1a-8a7a-48f9-8677-f4562f72a228` ;
- reviewer portant uniquement `publication_reviewer` : `6abcbe6a-b472-41c7-86a3-a4ed13405bb3`.

Les credentials éphémères n'ont pas été inscrits dans le repository ou les preuves. Les scripts temporaires de provisioning ont été supprimés.

Première divergence : Chrome système piloté par Playwright, avec le profil neuf `RC2-OWNER-5a00c6ca`, sans extension et sans bypass TLS, refuse l'ouverture du formulaire réel avec `net::ERR_CERT_AUTHORITY_INVALID`.

- attendu : GET HTTPS du formulaire Login puis réponse HTTP ;
- réel : échec TLS avant HTTP ;
- cause racine observable : le processus Chrome système de la campagne ne reconnaît pas la CA locale dans son contexte de confiance effectif.

Arrêt immédiat avant Login. Aucun POST, cookie, session, Property, Listing, média ou commandId produit n'existe. Search reste non ouvert et aucune correction produit n'est appliquée.

**NO GO PROPOSÉ — ITERATION 11 — REOPENING 02**

## Reopening 03 — campagne `9fcb7575-8c54-4b75-8fae-9ad26d3761ba`

Le préflight Chrome système manuel est déclaré PASS par l'opérateur : `https://appart.test/` s'affiche sans interstitial TLS dans le profil `RC2-OWNER-REOPENING03`.

La première étape suivante exige une saisie et une observation manuelles dans Chrome système. L'agent d'exécution ne dispose d'aucune surface d'interaction manuelle avec cette fenêtre ; ses seules surfaces programmatiques constitueraient Playwright, CDP, navigateur contrôlable ou automatisation UI, tous explicitement interdits par la mission.

- étape : Owner Login ;
- attendu : saisie manuelle du credential, POST réel, session et workspace ;
- réel : aucune interaction autorisée ne peut être émise ou observée par l'agent ;
- cause : incompatibilité entre le mode exclusivement manuel imposé et les surfaces d'exécution disponibles à l'agent.

Aucun POST, cookie, session, Property, Listing ou commandId n'est produit. Aucun défaut APPART.SN n'est démontré. Search reste non ouvert.

**NO GO PROPOSÉ — ITERATION 11 — REOPENING 03**

## Reopening 04 — campagne `a905da4c-8d13-4a4a-9e35-e224b0b03c52`

Le parcours neuf a franchi Resume, Submit, Publication Review et Publication avec des données réelles : Property canonique présente, ledger Promotion appliqué, Aggregate et Workflow `published`, Queue `completed`. Les replays stricts Claim et BeginReview retournent `already_applied`.

La première divergence intervient à l'activation Projection. L'interface affiche « Publication confirmée » mais le résultat applicatif fermé de Projection est `not_ready`. Le replay strict, avec les mêmes identités et le même payload canonique, retourne également `not_ready`.

L'inspection read-only de `CertifiedPublicListingProjectionSource` ferme la cause racine unique : `ProjectionSourceAssemblyStatus::SearchMissing`. La Property canonique est bien présente ; `PropertyMissing` a disparu. Aucun ledger Projection n'est créé et `public_projection.listing_projections` contient zéro ligne pour le Listing.

La vue de confirmation ne réduit pas `NotReady` en erreur et présente donc un succès visuel sans Projection matérialisée. Aucune correction n'est appliquée. Search n'est pas ouvert.

**NO GO PROPOSÉ — ITERATION 11 — REOPENING 04**
