# Deterministic Packaging Diagnostic

Evidence 04 invoquait Git Bash puis remplaçait son `PATH` par des chemins POSIX afin d'exposer PHP et Composer. Le packaging Bash trouvait Composer, mais le processus Windows PHP créant le manifeste héritait d'un PATH inadapté à `cmd.exe`. Son appel `shell_exec("composer --version")` ne produisait aucune version et retardait la terminaison jusqu'à la limite de vingt minutes.

Correction strictement procédurale : exposer PHP, Composer et les outils Unix dans le PATH Windows **avant** le lancement de Git Bash. MSYS effectue alors la conversion appropriée pour Bash et pour les sous-processus Windows. Aucun fichier R3 ni outil Build/CI n'est modifié.
