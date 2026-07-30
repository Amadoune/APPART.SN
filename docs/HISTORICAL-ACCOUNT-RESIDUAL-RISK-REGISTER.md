# Historical Account — Residual Risk Register

| ID | Risque résiduel | État | Traitement / gate |
|---|---|---|---|
| HAR-01 | Aucun import des Accounts legacy n'est certifié | OUVERT | La création native est l'unique stratégie actuellement certifiée; tout import exige un jalon dédié |
| HAR-02 | Dépendance aux types, contraintes et transactions PostgreSQL | ACCEPTÉ | Maintenir la suite PostgreSQL comme gate obligatoire |
| HAR-03 | Confidentialité des hash et tokens au repos dépend de la sécurité PostgreSQL et de l'exploitation | OUVERT | Chiffrement, contrôle d'accès, rotation et sauvegardes relèvent d'un dispositif futur; aucune protection non implémentée n'est revendiquée |
| HAR-04 | Le bootstrap futur de 4.9C devra partager la transaction et le PDO avec Historical Account | OUVERT | Recertification ciblée 4.9C obligatoire |
| HAR-05 | La coexistence entre version historique Account et version lifecycle doit rester explicite | SURVEILLÉ | Interdire toute assimilation ou copie implicite entre les deux versions |
| HAR-06 | Une reprise prématurée de 4.9D déplacerait la frontière certifiée | BLOQUANT | 4.9D reste suspendu jusqu'au GO de la recertification ciblée 4.9C |
| HAR-07 | Toute évolution du Snapshot V1 ou de la migration 042 peut rompre la reconstruction | BLOQUANT | Amendement versionné, analyse d'impact et recertification obligatoires |

## Décisions non revendiquées

- Aucun import legacy n'est disponible.
- Aucun chiffrement applicatif supplémentaire des secrets au repos n'est
  déclaré.
- Aucune transaction partagée avec le bootstrap Account Status n'est encore
  composée.
- La recertification ciblée 4.9C n'est pas ouverte par le présent dossier.
- Le Sprint 4.9D n'est ni repris ni autorisé.
