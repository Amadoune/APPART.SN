# APPART.TEST Public Search Experience 01 — Search Experience Specification

## Parcours attendu

`/` → soumission de filtres autorisés → résultats publics réels → chemin canonique → fiche publique réelle.

## Contrat minimal requis mais absent

Pour réaliser ce parcours sans contourner l'architecture, une surface publique certifiée devrait fournir au minimum :

- une requête structurée dont chaque filtre est explicitement autorisé ;
- une collection ordonnée de résumés d'annonces publiques ;
- le chemin canonique de chaque résultat ;
- les seuls champs de carte approuvés ;
- un statut vide et un statut d'indisponibilité ;
- éventuellement un compteur et une pagination si qualifiés.

Le contrat actuel est volontairement status-only. Le modifier ou créer un nouveau port/read model constituerait un amendement contractuel distinct non autorisé dans ce chantier.

## Données requises mais absentes

Une génération publique active contenant au moins une projection cohérente est indispensable. Sa création exige le pipeline métier/projection certifié ou un jeu de démonstration explicitement autorisé. Aucun Seeder, SQL direct ou écriture métier n'est effectué ici.

## Design conservé

Le Product Experience Shell reste inchangé. Sa recherche et ses cartes restent explicitement visuelles et ne sont pas présentées comme un parcours fonctionnel.
