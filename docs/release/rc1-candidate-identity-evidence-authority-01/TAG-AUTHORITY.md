# Tag Authority

## Autorité du tag annoté

Le tag annoté est l'acte Git qui désigne un commit existant comme candidat RC1. Il doit être créé uniquement après le commit et ne doit jamais être déplacé, recréé ou remplacé après publication de la preuve.

## Contenu obligatoire de l'attestation

Le message du tag doit au minimum identifier :

- `APPART.SN Release Candidate RC1` ;
- le jalon de matérialisation RC1-A2 ;
- la baseline ancestrale R5 ;
- l'absence d'auto-référence documentaire.

Git enregistre nativement dans l'objet tag la cible, son type, le tagger, la date et le message. Le commit SHA cible n'a donc pas à être recopié dans un fichier du commit.

## Vérifications obligatoires

Après création :

1. `git cat-file -t refs/tags/appart-sn-release-candidate-rc1` retourne `tag` ;
2. la résolution `^{commit}` retourne le commit candidat ;
3. le parent unique du commit candidat est R5 ;
4. le tree du commit correspond au tree attesté dans la preuve externe ;
5. le tag demeure inchangé pendant toutes les campagnes ultérieures.

Le tag annoté qualifie l'identité RC ; il ne certifie pas à lui seul les gates produit, qualité, CI ou exploitation.
