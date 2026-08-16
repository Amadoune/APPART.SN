# No StorageKey Evidence

La `storageKey` n'appartient à aucun DTO, résultat Application, JSON, header ou message public. Elle est reconstruite uniquement dans l'adapter storage et ne quitte jamais cette frontière.

Les tests d'architecture et HTTP vérifient l'absence de fuite de `storageKey`, de préfixe privé et de chemin filesystem.
