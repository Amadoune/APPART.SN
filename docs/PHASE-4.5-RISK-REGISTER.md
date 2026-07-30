# Phase 4.5 — Risk Register

| Risque | Niveau | Prévention |
|---|---:|---|
| confusion statut / établissement / mandat | élevé | frontière limitée au statut et tests Architecture |
| événement implicite à l'initialisation | élevé | `ProfessionalRegistered` explicitement exclu du workflow |
| temps issu de l'Aggregate historique | moyen | contexte explicite fourni par l'appelant |
| rejeu impossible après progression de version | élevé | inspection exacte contractuelle avant 4.5D |
| port de routage sans résultat | élevé | résultat fermé obligatoire en 4.5F |
| Consumer sans destination réelle | critique | Inbox certifiée avant compatibilité Outbox |
| owner Outbox absent | élevé | sprint 4.5H-R1 préalable |
| fuite de numéro d'enregistrement ou représentant | élevé | payload minimal et politique de confidentialité 4.5E |
| dérivation implicite d'éligibilité Lead | critique | connexion interdite hors sprint contractuel dédié |
| transaction journal/Outbox distincte | critique | réutilisation exclusive de la transaction générique |
