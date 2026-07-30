# Historical Account Persistence — Risk Matrix

| ID | Risque | Gravité | Prévention / gate |
|---|---|---|---|
| P1 | extraction impossible des secrets et collections | bloquante | snapshot sécurisé versionné avant mapper |
| P2 | réflexion ou sérialisation contourne le domaine | critique | interdiction Architecture; API de persistance explicite |
| P3 | hydratation rejoue des mutations/événements | critique | factories `reconstitute`, test `releaseEvents()=[]` |
| P4 | fuite du password hash ou des tokens | critique | type secret borné, aucune journalisation/debug, chiffrement au repos selon politique |
| P5 | écrasement concurrent | critique | `UPDATE ... WHERE version=:expected`, cardinalité exacte 1 |
| P6 | racine et enfants partiellement écrits | critique | transaction PDO unique, rollback intégral |
| P7 | doublon email/téléphone sous concurrence | critique | index uniques PostgreSQL et mapping SQLSTATE déterministe |
| P8 | historique rôles/consents aplati | élevé | lignes ordinales immuables; conservation des cycles |
| P9 | confusion version Account / lifecycle | critique | colonnes et types séparés; aucune comparaison |
| P10 | journal 041 utilisé comme source Account | critique | interdiction structurelle et tests Architecture |
| P11 | statut historique mis à jour par 4.9 | critique | dépendance lecture seule de 4.9C; aucune méthode historique appelée |
| P12 | corruption mappée en Missing | élevé | exception Infrastructure fermée; `null` réservé à l'absence attestée |
| P13 | ordre de verrouillage divergent | élevé | verrou Account racine puis enfants dans ordre fixe |
| P14 | transaction externe commitée par le Repository | critique | owner transactionnel explicite; join sans commit/rollback |
| P15 | import legacy inventé | élevé | aucun import sans source, dictionnaire et décision séparée |
| P16 | données legacy sans version fiable | élevé | quarantaine; jamais de version synthétique implicite |
| P17 | événements libérés/perdus par save | élevé | Repository n'appelle jamais `releaseEvents()` |
| P18 | Runtime Health effectue une mutation | élevé | sonde structurelle read-only et résolution paresseuse |
| P19 | `save` accepte un saut de version invalide | élevé | contrat P-B : candidat attendu à `expected+1` |
| P20 | données enfants incohérentes avec la racine | élevé | contraintes, checksum/snapshot validation et tests de corruption |

## Gates préventifs proposés

```text
4.9P-B-R1 — Secure Persistence Snapshot Boundary
```

À déclencher avant tout mapper si 4.9P-B ne peut certifier une extraction et
une hydratation complètes sans modifier le comportement métier.

```text
4.9P-C-R1 — Legacy Import Gate
```

À ouvrir uniquement si une source legacy réelle est fournie. Il devra décider
identités, conversions, doublons, versions et quarantaine.

```text
4.9P-C-R2 — Shared Transaction Compatibility Gate
```

À déclencher si la connexion ou l'ordre de verrouillage du Repository Account
diverge du store lifecycle 4.9C.
