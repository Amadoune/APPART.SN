# Revision model

Modèle retenu : **B, revision vector Place + ancestors**, avec projection scalaire déterministe pour le writer existant.

- vecteur : root→leaf des versions positives;
- version watermark : somme arithmétique vérifiée des versions;
- checksum : payload canonique complet;
- causalité : clé déterministe de représentation V1 et du checksum du vecteur.

Les parents étant immuables et les versions monotones, toute mutation autorisée augmente strictement la somme. Overflow ou vecteur invalide échoue fermé.
