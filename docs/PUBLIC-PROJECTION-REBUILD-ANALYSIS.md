# Sprint 3.6E — analyse d’architecture

Le rebuild reste une orchestration applicative du Projection Store. Il ne lit aucun Aggregate, n’exécute aucun transport Delivery et ne reconstruit aucune règle Search, SEO ou canonical.

La chaîne est séparée en quatre responsabilités :

1. `PublicProjectionRebuildEnumerator` énumère une page bornée d’identités selon un scope Full, Listings ou Range.
2. `PublicProjectionCandidateFactory` produit le record déjà décidé par les sources certifiées.
3. `PublicProjectionRebuilder` écrit uniquement dans une génération Candidate via le Writer 3.6D.
4. Les ports de validation et de gestion des générations contrôlent l’intégrité avant une transition atomique.

Le checkpoint est opaque pour l’orchestrateur et retourné après chaque page. Une interruption ne perd donc pas la progression ; rejouer une page converge vers `AlreadyApplied` grâce au Writer certifié.

L’adaptateur PostgreSQL ajouté ne modifie ni le schéma ni le Store 3.6D. Il exploite les états et contraintes existants. Activation et rollback verrouillent les générations dans un ordre stable, retirent l’Active courante puis promeuvent la cible dans la même transaction. Ils rejoignent une transaction externe lorsqu’elle existe.

Une activation exige un `PublicProjectionGenerationValidation` valide et lié à la génération cible. La validation relit chaque payload avec le Mapper certifié, vérifie son checksum, compare le watermark vectoriel au manifeste et rend visibles les absences, divergences et corruptions.

Les métriques sont des valeurs déterministes du rapport : progression, lag et nombre de divergences. Aucun backend Runtime, aucune supervision et aucun binding ne sont introduits.
