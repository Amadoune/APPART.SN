# Lifecycle Model

Chemin existant: `absent → candidate → active → retired`. `createCandidate` crée Candidate; `activate` exige Candidate et manifeste valide, retire l'Active courante puis active la cible; `rollback` réactive une Retired. Il n'existe pas de suppression productive ni de transition directe absent → active.
