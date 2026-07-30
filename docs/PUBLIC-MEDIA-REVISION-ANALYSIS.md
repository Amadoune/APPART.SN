# Sprint 3.7B — analyse d’architecture

Public Media doit fournir au watermark une version de publication stable, indépendante d’une horloge et distincte des détails de persistance du module Media.

La fondation 3.7B est strictement applicative et spécialisée. Elle reprend le protocole certifié de Public Geography sans créer d’abstraction partagée ni modifier 3.7A : version publique positive, checksum SHA-256 du payload public canonique et clé de causalité.

La source future demeure responsable de la séquence et de la canonicalisation du payload Media public. La stratégie ne choisit pas la couverture, ne trie pas la galerie, ne décide pas la visibilité et ne transforme aucun média.

La politique distingue explicitement la promotion initiale, l’avancement, la rediffusion déjà stable, l’obsolescence et la divergence. Une version identique accompagnée d’un checksum ou d’une causalité différente est divergente et non promotable.

Le contrat de lecture expose seulement la révision stable d’une collection. Son implémentation de production reste réservée aux futurs sprints de sources Runtime.
