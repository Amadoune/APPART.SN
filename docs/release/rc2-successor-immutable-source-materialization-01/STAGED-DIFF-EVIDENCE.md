# Staged Diff Evidence

Le manifeste exige 77 chemins et interdit tout chemin inattendu.

La réconciliation terminale de l'index doit établir :

- missing : 0 ;
- unexpected : 0 ;
- `git diff --cached --check` : PASS ;
- fichiers temporaires/artifacts : 0.

Le résultat cryptographique et les identités connues seulement après commit restent dans les preuves externes.
