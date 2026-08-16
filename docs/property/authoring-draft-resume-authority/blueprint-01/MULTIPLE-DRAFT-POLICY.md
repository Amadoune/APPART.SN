# Multiple Draft Policy

- **Zéro draft** : proposer le nouveau parcours existant.
- **Un draft** : proposer explicitement « Reprendre » et « Nouveau ». Aucun démarrage automatique n’est requis.
- **Plusieurs drafts** : afficher une sélection explicite. Aucun choix par date, ordre SQL, checkpoint ou première ligne.

Chaque candidat est identifié par son `listingId` et des libellés issus du snapshot autoritatif disponible. La décision de l’utilisateur est transportée par l’URL de reprise.

Un candidat devenu non reprenable entre l’affichage et le clic est refusé fail-closed lors de la relecture. Le système ne bascule pas silencieusement vers un autre draft.
