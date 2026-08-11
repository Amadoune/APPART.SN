# Identity Model

## Objets canoniques

L'identité RC est une chaîne Git vérifiable :

`nom du tag → objet tag annoté → objet commit → objet tree`

Chaque objet possède une fonction distincte :

| Objet | Autorité |
|---|---|
| commit SHA | identité canonique de la révision, de son parent et de ses métadonnées Git |
| tree SHA | identité canonique du contenu versionné et de sa structure |
| tag annoté | désignation autoritative de cette révision comme Release Candidate |
| tag object SHA | identité de l'attestation annotée elle-même |

Le nom humain `appart-sn-release-candidate-rc1` n'est autoritatif qu'en tant que référence vers un objet tag de type `tag`, lequel doit résoudre vers le commit candidat exact.

## Identité minimale RC1

La preuve complète doit établir :

- nom du tag ;
- type Git `tag` ;
- SHA de l'objet tag ;
- SHA du commit résolu ;
- SHA du tree du commit ;
- SHA du parent R5 ;
- message exact du commit ;
- date Git ;
- worktree propre au terme de l'exécution.

Le commit SHA identifie la révision. Le tree SHA prouve le contenu. Le tag annoté porte la qualification Release Candidate. Aucun de ces éléments ne remplace les deux autres.

## Absence d'auto-référence

Une baseline immuable existe valablement avant que son SHA soit connu : Git reçoit son contenu et ses métadonnées, crée l'objet, puis calcule son identité. Le SHA est une conséquence de la matérialisation, pas une donnée source nécessaire à celle-ci.
