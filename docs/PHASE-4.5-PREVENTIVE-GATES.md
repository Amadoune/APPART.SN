# Phase 4.5 — Preventive Gates

| Gate | Bloque | Preuve exigée |
|---|---|---|
| frontière du statut | 4.5A | établissements et mandats explicitement exclus |
| entrée du cycle | 4.5A | `initialState() = Active`, aucun événement implicite |
| contexte de transition | 4.5D | acteur, instant, version et rejeu définis avant orchestration |
| résultat de routage | 4.5G | port non-void et résultats fermés certifiés dès le transport |
| destination durable | 4.5H | Inbox réelle, aucun sink silencieux |
| politique de consommation | Consumer | acquittement, retry et quarantaine exhaustifs |
| owner Outbox | compatibilité Outbox | mapping `Professionals → professionals` additif et rollback isolé |
| atomicité | 4.5I | même PDO et transaction unique journal + Outbox |
| exposition HTTP | 4.5J | intégrateur atomique certifié et mapping fermé |

## NO GO structurels

Un NO GO est obligatoire si une étape exige :

- la lecture de l'Aggregate pour reconstruire une décision ;
- une horloge ou identité implicite ;
- la copie des règles d'établissement ou de mandat ;
- un Consumer sans destination durable ;
- un acquittement sans traitement réel ;
- la modification d'une migration ou d'un owner certifié ;
- une deuxième transaction ou une compensation métier.
