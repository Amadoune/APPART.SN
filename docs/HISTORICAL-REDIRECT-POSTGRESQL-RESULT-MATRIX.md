# Historical Redirect PostgreSQL Result Matrix

| Données persistées | Résultat |
|---|---|
| aucune ligne exacte | `NotFound` |
| une ligne intacte, destination Current distincte | `Resolved` |
| une ligne intacte sans destination ni qualification | `DestinationMissing` |
| destination égale à la source | `LoopDetected` |
| destination qualifiée Historical | `ChainDetected` |
| au moins deux décisions pour la source | `Ambiguous`, aucune cible |
| état corrompu, checksum, révision, URL ou qualification invalide | `Corrupted`, aucune cible |

La validation d'une ligne précède la cardinalité : une ligne illisible ne peut pas être masquée par `Ambiguous`. Les lectures répétées de données identiques rendent des résultats identiques.
