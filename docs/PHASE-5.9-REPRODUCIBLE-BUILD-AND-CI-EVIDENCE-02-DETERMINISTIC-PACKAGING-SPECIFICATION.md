# Deterministic Packaging Specification

`tools/release/create-deterministic-tar.php` écrit directement un flux USTAR : ordre lexical, chemins normalisés `/`, uid/gid 0, mode 0644, mtime 0, checksum d'en-tête recalculé et deux blocs terminaux nuls.

Les timestamps, owner/group, permissions locales, métadonnées OS, chemins absolus, caches et temporaires ne participent pas à l'archive. Le format non compressé évite les métadonnées gzip.

La comparaison probatoire porte sur le SHA-256 byte-for-byte de l'archive et sur un inventaire trié `path + SHA-256`.
