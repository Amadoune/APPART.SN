# Evidence 05 Source Integrity Audit

Source obligatoire : clone neuf du tag annoté `phase-5.9-baseline-candidate-r3`, commit `a2c53c00094a219c9858261f952f5599a260a828`.

L'identité Git, le type du tag et la propreté du clone sont PASS. Le contrôle du contenu échoue avant les campagnes : le blob R3 de `tools/release/build-release.sh` est `dc1beb0c168226fd14d1d291e7ec5d3a94527d5a` et contient encore `shell_exec("composer --version")`.

Le correctif certifié Packaging Correction 01 correspond au blob non matérialisé `53cb5974a56e428aaae811be5c7cead09476b719`, qui capture la version Composer dans Bash, impose une valeur non vide et la transmet au manifeste. Ce blob n'appartient pas à R3.

Employer le script corrigé extérieurement violerait la source unique ; déplacer R3 ou créer R4 est interdit. Evidence 05 est donc structurellement bloquée.
