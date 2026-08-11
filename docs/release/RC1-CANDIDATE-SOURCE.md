# RC1-A Candidate Source

## Définition normative

La future source RC1 est l'état courant qualifié appliqué sur le commit R5 :

`phase-5.9-baseline-candidate-r5`

`9801d9ed30ea3a5fa412708cd022d16bc84e472c`

`+ delta RC1-A de 632 chemins`

`- .pnpm-store/v11/index.db`

Aucun autre workspace, artefact historique ou fichier généré n'est autorisé.

## Procédure préparée — non exécutée

Le futur jalon de matérialisation devra :

1. vérifier HEAD et l'ascendance R5 ;
2. supprimer ou exclure littéralement le cache pnpm local ;
3. vérifier que le delta correspond au présent manifeste ;
4. exécuter les validations autorisées par le futur jalon ;
5. ajouter exactement la source qualifiée à l'index ;
6. créer un commit unique avec le sujet préparé ;
7. créer le tag annoté `appart-sn-release-candidate-rc1` sur ce commit exact ;
8. vérifier type du tag, résolution tag→commit, worktree propre et empreintes ;
9. publier uniquement vers un repository officiel préalablement résolu par autorité.

Les étapes 5 à 9 ne sont pas autorisées dans RC1-A et restent **NOT_EXECUTED**.

## Reproductibilité attendue

Après matérialisation, toute preuve RC devra repartir d'un clone neuf du tag annoté, restaurer exclusivement les lockfiles et rejouer la chaîne complète. Aucun résultat R5 ou workspace courant ne pourra devenir une preuve terminale RC1 par simple réutilisation.

## Exécution RC1-A2

La tentative de matérialisation a indexé exactement les 632 chemins qualifiés, sans le cache pnpm. Le premier contrôle applicable aux nouveaux fichiers, `git diff --cached --check`, a ensuite détecté **67 divergences** de type `new blank line at EOF`.

Les divergences sont localisées dans :

- 12 documents Listing Expiration / Transition Evidence ;
- 4 documents Architecture Decision ;
- 6 documents Local HTTPS / IAM ;
- 14 documents Product Amendments ;
- 13 documents Product Implementations ;
- 15 documents Product Sprints ;
- `public/robots.txt` ;
- `resources/views/owner-dashboard.blade.php` ;
- `resources/views/sitemap.blade.php`.

Conformément à l'interdiction de modifier l'état qualifié, aucune normalisation n'a été appliquée. Le staging a été intégralement retiré après le fail-fast.

État terminal RC1-A2 :

- commit candidat : **NOT_CREATED** ;
- tag annoté : **NOT_CREATED** ;
- HEAD : `9801d9ed30ea3a5fa412708cd022d16bc84e472c` ;
- parent prévu R5 : inchangé ;
- index : vide ;
- worktree : non propre, état source préservé ;
- `git diff --check` sur fichiers déjà suivis : PASS ;
- `git diff --cached --check` sur la source candidate complète : FAIL.

## Gate de matérialisation

La source est matériellement préparée si :

- le cache exclu est le seul chemin non-source ;
- les 632 chemins du delta sont présents ;
- les migrations 092–097 et rollbacks correspondent aux empreintes ;
- les 84 Providers restent référencés ;
- les lockfiles conservent leurs empreintes ;
- `git diff --check` reste PASS.

La dernière condition n'est pas satisfaite pour la totalité des nouveaux fichiers. Une correction documentaire/whitespace explicitement autorisée est nécessaire avant toute nouvelle exécution.

## Normalisation RC1-A2-W1

L'amendement RC1-A2-W1 a retiré exactement une fin de ligne excédentaire sur les 67 fichiers identifiés par Git. La transformation a été effectuée sur les octets sources : aucun réencodage et aucune modification avant le suffixe terminal n'ont été admis.

Résultat terminal :

- fichiers normalisés : **67** ;
- autres fichiers modifiés par l'amendement : **0** ;
- chemins candidats indexés pour la preuve : **632** ;
- `git diff --cached --check` : **PASS** ;
- commit : **NOT_CREATED** ;
- tag : **NOT_CREATED** ;
- staging résiduel après preuve : **NONE**.

La source est de nouveau éligible à une matérialisation distincte.
