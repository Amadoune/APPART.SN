# Phase 4.6 — Media Item Lifecycle Contract Blueprint

## Workflow candidat

États fermés : `Active`, `Removed`, `Archived`.

Actions fermées : `Remove`, `Archive`, `Unknown`.

| État | Remove | Archive | Unknown |
|---|---|---|---|
| Active | Removed / Allowed | Archived / Allowed | Denied / UnknownAction |
| Removed | Denied / TerminalState | Denied / TerminalState | Denied / UnknownAction |
| Archived | Denied / TerminalState | Denied / TerminalState | Denied / UnknownAction |

Priorité diagnostique : `UnknownAction` précède `TerminalState`. La fonction de décision dépend exclusivement de `(state, action)`.

## Frontières

Le workflow possède uniquement l'autorisation de changer le statut d'un média. `MediaCollection` reste propriétaire de l'unicité, du checksum, de l'ordre, du média principal et du remplacement du principal. La création, le captioning, le réordonnancement et `markPrimary` restent hors workflow.

## Contexte futur obligatoire

Avant 4.6D, un contrat versionné devra porter au minimum : collection, media, version attendue, acteur, `occurredAt`, et décision explicite de remplacement du média principal lorsque nécessaire. Ce contrat devra provenir de la collection certifiée ; l'orchestrateur ne pourra jamais recalculer si le média est principal ni choisir son remplaçant.

## Rejeu

Le dernier append devra être inspectable exactement (transition, version, acteur, instant, contexte de remplacement et checksum) avant toute classification `AlreadyApplied`, divergence ou conflit.
