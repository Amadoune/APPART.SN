# APPART.TEST Product Experience Shell 01 — Product Shell Specification

## Surfaces

- `/` : enveloppe publique visuelle APPART.SN ;
- `/_local/bootstrap` : diagnostic technique, inaccessible hors environnement `local` ;
- `/up` : health Laravel existant.

## Composition

1. header sticky avec marque, navigation, CTA et menu mobile ;
2. hero éditorial immobilier ;
3. recherche purement visuelle ;
4. catégories génériques ;
5. cartes d'annonces explicitement fictives ;
6. bloc propriétaires/professionnels non connecté ;
7. arguments de confiance ;
8. footer extensible ;
9. notification explicite pour toute action non connectée ;
10. états et contrôles génériques réutilisables par classes CSS.

## Responsive

- desktop : hero en deux colonnes, navigation complète, grilles quatre/trois colonnes ;
- tablette, sous 900 px : empilement du hero, grilles deux colonnes, navigation mobile ;
- mobile, sous 620 px : grilles simples, recherche verticale, typographie et espacements réduits.

## Limites

Aucun formulaire n'est soumis, aucune authentification n'est déclenchée, aucune recherche n'est exécutée, aucune annonce n'est chargée et aucune publication n'est créée.
