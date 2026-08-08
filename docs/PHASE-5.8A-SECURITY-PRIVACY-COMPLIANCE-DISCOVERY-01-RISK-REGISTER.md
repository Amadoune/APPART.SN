# Risk Register — Security, Privacy & Compliance Discovery

| Risque | Impact | Traitement candidat | Résiduel Discovery |
|---|---|---|---|
| secret embarqué ou journalisé | critique | inventaire, stockage dédié, redaction et rotation | élevé jusqu'à inventaire |
| compromission de clé | critique | séparation, rotation, révocation et réponse aux incidents | élevé jusqu'au modèle de clés |
| algorithme inadapté à la finalité | élevé | catalogue approuvé distinct chiffrement/signature/hash/password | moyen |
| PII surcollectée | élevé | registre de finalités et minimisation champ par champ | moyen |
| conservation indéfinie | élevé | calendrier par catégorie et purge prouvable | élevé jusqu'aux décisions owners |
| suppression incomplète | critique | cartographie copies/index/caches/sauvegardes | élevé jusqu'à inventaire |
| export à un mauvais demandeur | critique | authentification forte, double contrôle et preuve de remise | moyen |
| journal d'audit altérable | critique | append-only, intégrité cryptographique et accès restreint | moyen |
| incident non détecté ou mal notifié | critique | playbooks, seuils, rôles et chronologie | élevé jusqu'aux exercices |
| indisponibilité sans reprise prouvée | critique | objectifs, sauvegarde et tests de restauration | élevé jusqu'aux mesures |
| transfert d'autorité métier | critique | matrice RACI et décisions laissées aux owners | faible |
| interprétation juridique erronée | critique | gate de validation juridique | moyen |
| modification d'une capacité gelée | critique | dépendances publiques uniquement et amendement obligatoire | faible |

Ce registre qualifie les risques ; il ne vaut ni acceptation ni traitement implémenté.
