# Identity Access HTTP Runtime — Binding Audit

## Binding courant

`IdentityAccessHttpServiceProvider::register()` déclare un singleton direct :

`IdentityAccessHttpRuntime → FailClosedIdentityAccessHttpRuntime`.

Aucun alias conditionnel, environnemental ou nommé ne remplace ce binding. Le même binding s'applique donc en local et dans toute composition de production utilisant ce Provider.

## Nature du fallback

| Qualification | Décision |
|---|---|
| provisoire | OUI au sens architectural : il représente une dépendance absente |
| volontaire | OUI : retour systématique `Unavailable` / session invalide |
| historique | OUI : introduit ainsi dès le commit `84be4995abaf171d76eadf97df329a647a105186` |
| destiné à la production | OUI : la documentation 5.1I le nomme explicitement « fallback de production fail-closed » |
| destiné au développement | NON spécifiquement : aucune bifurcation locale n'existe |

## Historique

- le port, le fallback et le Provider apparaissent ensemble dans la baseline matérialisée par le commit `84be499` ;
- l'historique ne contient aucune autre classe concrète de production implémentant `IdentityAccessHttpRuntime` ;
- aucune suppression d'un ancien Runtime HTTP réel n'est observée ;
- le Runtime réel n'a donc pas été supprimé ni simplement débranché : il n'a jamais été matérialisé dans l'historique disponible.

## Dépendances manquantes derrière le port

Les stores et l'orchestration atomique ne suffisent pas. Une implémentation doit encore porter les réductions applicatives pour : recherche d'identité sans fuite, vérification du credential, politique d'état du compte, création/rotation/révocation de session, hashing du secret, inspection temporelle, récupération, profil, changement de contact et clôture.

Changer uniquement le binding vers un store ou l'orchestrateur existant violerait la frontière : aucun de ces composants n'implémente le port ni la sémantique complète du résultat HTTP.
