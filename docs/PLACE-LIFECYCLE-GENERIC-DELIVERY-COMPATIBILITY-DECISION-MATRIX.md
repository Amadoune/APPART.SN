# Place Lifecycle — Matrice de décision Generic Delivery

| Critère | Option 1 : interfaces directes | Option 2 : adaptation générique | Option 3 : abandon |
|---|---|---|---|
| nouveaux Payload/Consumer | aucun | couche supplémentaire | duplication probable |
| modification ports historiques | aucune | probable | aucune |
| classes Place spécialisées supplémentaires | aucune | risque élevé | oui |
| neuf owners inchangés | oui | démonstration complexe | oui |
| Writer/Reader/Mapper génériques | réutilisés | adaptés | abandonnés |
| Worker générique | réutilisé | adapté | abandonné |
| sémantique 4.8G | inchangée | indirection | chaîne distincte |
| matrice 4.8I | inchangée | indirection | chaîne distincte |
| conformité R1 | totale | incertaine | non |
| verdict | **GO** | **NO GO** | **NO GO** |

L'option 1 possède la plus petite surface : deux déclarations d'interface et
une adaptation de frontière dans des classes existantes, sans nouveau type
métier ou infrastructure.
