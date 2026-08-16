# RC2 Stabilization — Validation Report

## Iteration 11 — Final End-to-End Certification 01

| Validation | Result |
|---|---|
| Aggregate / Workflow / Queue | PASS — published / published / completed |
| Canonical Property / Promotion | PASS — present / applied |
| Search / ContentSeo | PASS — Found v1 / Found v1 |
| Public Geography / Media | PASS — Available v3 / Found v1 |
| Binary delivery | PASS — 200, image/jpeg, no-store |
| Active Generation | PASS — Found, one Active |
| Projection | PASS — current, unique, equivalent |
| Public read model / page | PASS — Found / HTTP 200 |
| First runtime divergence | NONE |
| UI NotReady defect | NON-BLOCKING RESIDUAL DEFECT |
| Git checks | PASS; staged paths 0 |

Verdict: **GO PROPOSÉ — RC2 ITERATION 11 — FINAL END-TO-END CERTIFICATION 01.** Historical validations remain below.

## Iteration 10

| Validation | Résultat |
|---|---|
| Unit / Feature / Architecture / PostgreSQL ciblés | PASS — 33 tests, 358 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS |
| Listing Published | PASS |
| Queue Completed | PASS — version 4 |
| Projection source assembly | FAIL-FAST — `PropertyMissing` |
| Projection Store | FAIL — aucune projection réelle |
| Projection ledger | FAIL — aucune activation enregistrée |
| Replay / AlreadyApplied | NON ATTEINT |

## Preuve réelle

Listing : `add18bba-6635-4bda-aba9-e66d7bf4084e`.

Le manifest navigateur de l'itération 09 établit Published et le replay Approve : `RC2-STABILIZATION/iteration-09/ITERATION-09-MANIFEST.json`.

L'inspection PostgreSQL et la source applicative établissent ensuite : Queue `completed` version 4, zéro ledger Projection, zéro read model projeté, assemblage bloqué par `PropertyMissing`.

Search et Public Listing ne sont pas interrogés.

## Iteration 11

| Validation | Résultat |
|---|---|
| Home HTTPS | PASS — page navigable |
| Navigation publique vers Login owner | PASS |
| Formulaire IAM réel | PASS |
| Session owner préexistante | ABSENTE |
| Credential owner autorisé disponible | BLOQUANT — non fourni à la campagne |
| Nouveau parcours RC2 | NON ATTEINT — fail-fast au Login |
| Property Promotion | NON ATTEINT |
| Submitted / Queue / Claim / Review / Published | NON ATTEINT |
| Projection / replay | NON ATTEINT |
| Search | NON OUVERT |
| Correction produit | AUCUNE |
| `git diff --check` | PASS |

La preuve navigateur confirme l’accessibilité du transport et de l’entrée IAM, mais ne permet pas de transmettre un credential absent. Aucun test technique supplémentaire ne peut remplacer le parcours réel exigé par la mission.

Première divergence unique : `Owner Login → credential autorisé indisponible dans l’environnement d’exécution`.

## Iteration 11 — Reopening 01

| Validation | Résultat |
|---|---|
| Principal Authoring certifié | PASS — `a2110000-0000-4000-8000-000000000001` |
| Reprovisioning / modification IAM | NON EXÉCUTÉ |
| Ouverture `https://appart.test/authoring/workspace` | FAIL-FAST — `net::ERR_BLOCKED_BY_CLIENT` |
| Réponse HTTP applicative | NON ATTEINTE |
| Session IAM dans le workspace | NON QUALIFIABLE |
| Nouveau Property / Listing | NON CRÉÉ |
| Upload / Preview / Submit | NON ATTEINT |
| Promotion / Queue / Review / Published | NON ATTEINT |
| Projection / replay | NON ATTEINT |
| Search | NON OUVERT |
| Correction produit | AUCUNE |

La première divergence est externe à la surface HTTP APPART.SN : le navigateur contrôlable bloque la navigation HTTPS locale avant émission ou réception d'une réponse. La campagne s'arrête à ce point conformément au fail-fast.

## Iteration 11 — Reopening 02

| Validation | Résultat |
|---|---|
| Manifest initialisé avant navigation | PASS — campagne `5a00c6ca-82f6-4574-aebe-821d0aa546c6` |
| Principal Authoring distinct, sans rôle | PASS — `4e992d1a-8a7a-48f9-8677-f4562f72a228` |
| Principal reviewer distinct | PASS — `6abcbe6a-b472-41c7-86a3-a4ed13405bb3`, rôle unique `publication_reviewer` |
| Profil Chrome owner neuf, sans extension | PASS |
| Bypass TLS | ABSENT |
| GET formulaire Login HTTPS | FAIL-FAST — `net::ERR_CERT_AUTHORITY_INVALID` |
| Réponse HTTP / POST Login | NON ATTEINT |
| Cookie et session | NON ÉMIS |
| Authoring → Projection | NON ATTEINT |
| Search | NON OUVERT |
| Correction produit | AUCUNE |

Le processus Chrome système lancé par Playwright ne reconnaît pas la CA locale dans son contexte de confiance effectif. Aucun défaut HTTP ou produit ne peut être qualifié derrière cette frontière TLS.

## Iteration 11 — Reopening 03

| Validation | Résultat |
|---|---|
| Manifest Reopening 03 | PASS — campagne `9fcb7575-8c54-4b75-8fae-9ad26d3761ba` |
| Chrome système manuel, profil owner | PASS — preuve opérateur |
| Home HTTPS sans interstitial | PASS — preuve opérateur |
| Playwright / navigateur contrôlable / bypass TLS | NON UTILISÉS |
| Interaction manuelle Login accessible à l'agent | FAIL-FAST — surface indisponible |
| POST Login / statut HTTP | NON ÉMIS / NON OBSERVÉ |
| Session / workspace | NON ATTEINT |
| Owner journey / reviewer / Projection | NON ATTEINT |
| Search | NON OUVERT |
| Correction produit | AUCUNE |

La divergence appartient à l'environnement d'exécution et non à APPART.SN : le mode manuel obligatoire nécessite un opérateur GUI, mais aucune surface manuelle n'est exposée à l'agent. Utiliser une surface programmatique violerait explicitement la campagne.

## Iteration 11 — Reopening 04

| Validation | Résultat |
|---|---|
| Isolation DB application / tests | PASS — `appart_rebuild` / `appart_test` |
| Resume du draft | PASS — Property Authoring v1, Listing Draft v1 |
| Submit / Promotion | PASS — commande `ef9e24b4-c42e-4221-8fe0-3f2e52cec2e6` |
| Property canonique | PASS — présente |
| Queue / Claim / replay Claim | PASS — `applied` / `already_applied` |
| BeginReview / replay | PASS — `under_review` / `already_applied` |
| ApprovePublication | PASS — Aggregate `published` v3, Workflow `published` v4 |
| Queue terminale | PASS — `completed` v4 |
| Assemblage source Projection | FAIL-FAST — `search_missing` |
| Projection initiale / replay | FAIL — `not_ready` / `not_ready` |
| Projection ledger | FAIL — 0 |
| Projection Store | FAIL — 0 |
| Disparition de `PropertyMissing` | PASS |
| Search | NON OUVERT |
| Correction produit | AUCUNE |

Le statut UI « Publication confirmée » n'est pas une preuve de Projection : le contrôleur rend le mode `confirmed` pour le résultat `NotReady`. Les stores et l'inspection applicative établissent l'absence de Projection et la cause unique `SearchMissing`.
## Post-Iteration-11 HTTP/UI correction

`PUBLICATION REVIEW HTTP/UI — NOTREADY RESULT-REDUCTION CORRECTION 01` preserves the certified runtime result and changes only its presentation reduction. Targeted Unit/Feature/Architecture tests pass with 13 tests and 58 assertions; PHPStan targeted, Pint targeted and Vite pass. The historical `NotReady -> confirmed` residual defect is closed without rematerializing Projection or changing RC2 data.
