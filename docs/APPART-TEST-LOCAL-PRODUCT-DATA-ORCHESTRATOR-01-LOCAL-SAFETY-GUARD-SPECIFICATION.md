# APPART.TEST LOCAL PRODUCT DATA ORCHESTRATOR 01 — Local Safety Guard Specification

Toute future commande devra refuser avant résolution de ses services métier lorsque l'une des conditions suivantes est vraie :

- `app()->environment('local')` est faux ;
- `DB_DATABASE` diffère exactement de `appart_test` ;
- ni confirmation interactive ni `--force-dev` n'est fourni ;
- le marqueur de développement réservé est absent ;
- une étape précédente n'a pas retourné un résultat explicitement accepté.

Elle devra afficher la cible, le caractère local et réversible de la donnée, sans exposer de secret de connexion.

Ces garde-fous sont qualifiés mais non implémentés, car le pipeline sous-jacent est bloqué avant que la commande puisse satisfaire son contrat principal.
