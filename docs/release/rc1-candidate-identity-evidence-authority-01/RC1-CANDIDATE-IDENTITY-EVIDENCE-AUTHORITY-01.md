# RC1 Candidate Identity Evidence Authority 01

## Question d'autorité

L'identité d'une Release Candidate Git ne peut pas être une donnée que le commit inscrit sur lui-même. Le SHA du commit est calculé à partir du tree, du parent, de l'auteur, du committer et du message ; inscrire ce SHA dans un fichier du tree modifie précisément l'objet à identifier.

## Comparaison

| Option | Immutabilité | Auditabilité | Reproductibilité | Simplicité | Compatibilité Git | Décision |
|---|---|---|---|---|---|---|
| A — commit auto-référent | impossible à stabiliser | contradictoire | impossible | trompeuse | non | REJETÉE |
| B — second commit documentaire | oui pour le premier commit | deux HEAD et deux objets à expliquer | possible | complexité inutile | oui | NON RETENUE |
| C — tag annoté + rapport post-matérialisation | oui | relation native et vérifiable | clone/tag exact | directe | native | RETENUE |

## Décision

L'option C est normative.

La baseline contient exclusivement les sources, manifestes pré-matérialisation et règles de vérification. Elle ne contient pas son propre SHA.

Après création du commit, le tag annoté lie le nom RC au commit exact. Un rapport d'exécution externe consigne les identités calculées et les contrôles. Ce rapport ne modifie ni le commit ni le tag et ne fait pas partie de la baseline.

## Conséquence RC1-A2

RC1-A2 peut créer un commit unique avec le message déjà autorisé, puis créer le tag annoté sur ce commit. Il ne doit pas modifier les documents versionnés pour y inscrire les SHA calculés. La preuve postérieure est externe à la baseline.
