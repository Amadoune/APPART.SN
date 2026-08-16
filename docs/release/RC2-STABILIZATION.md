# RC2 Stabilization Program

## Règle d'exécution

Le programme applique une discipline fail-fast permanente : une campagne complète, une première divergence, une cause, une correction, puis un rejeu. Aucune divergence postérieure n'est anticipée.

## Tableau de bord après l'itération 01

| Étape | État |
|---|---|
| HTTPS | PASS |
| IAM | PASS |
| Property | PASS |
| Listing | PASS |
| Media Upload | PASS — HTTP 201 |
| Preview | PASS |
| Submit Listing | PASS — état Submitted |
| Reviewer Login | PASS |
| Reviewer Authorization | PASS |
| Publication Review Queue | FIRST DIVERGENCE — candidature absente de l'écran |
| Claim et étapes suivantes | NOT_EXECUTED |

L'itération 01 est fermée dès l'absence de la candidature dans la Queue. Elle ne qualifie pas la cause de cette nouvelle divergence.
