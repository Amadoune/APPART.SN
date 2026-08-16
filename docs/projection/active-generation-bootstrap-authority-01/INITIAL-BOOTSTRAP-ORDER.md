# Initial Bootstrap Order

Ordre obligatoire:

1. vérifier zéro Active et aucune Candidate concurrente;
2. valider generationId UUIDv4 et scope explicite non vide;
3. `createCandidate`;
4. assembler/rebuild chaque Listing du scope dans cette Candidate;
5. construire le manifeste avec tous et seulement les records courants produits;
6. valider;
7. activer;
8. vérifier `ActiveGenerationReader::Found` avec la même identité.

Tout résultat incomplet arrête avant activation.
