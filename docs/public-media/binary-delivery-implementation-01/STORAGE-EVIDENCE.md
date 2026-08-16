# Storage Evidence

`LaravelFilesystemPublicMediaBinaryContentReader` est l'unique adapter qui reconstruit la clé privée à partir des identités owner-side. Il lit le disque Media existant, vérifie la taille et le SHA-256 autoritatifs, puis remet un flux temporaire en lecture.

Le fichier n'est ni copié durablement, ni transcodé, ni redimensionné. Un objet absent ou corrompu échoue fermé. Une panne de dépendance reste distincte en interne.
