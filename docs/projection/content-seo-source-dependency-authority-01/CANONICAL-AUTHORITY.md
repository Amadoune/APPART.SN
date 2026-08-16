# Canonical Authority

`CanonicalPolicy::fromPath()` fixe le site `https://appart.sn/` et normalise un chemin déjà décidé. Elle ne crée pas ce chemin.

Le `canonicalPath` est obligatoire dans `ListingSeoSource`, le snapshot et le read model. Aucun fait owner productif ne fournit actuellement le chemin RC2. Les commandes locales utilisent des constantes manuelles.

Inventer un slug depuis le titre ou le ListingId violerait la mission. La décision du canonical path est la première donnée normative non fermée pour le catch-up RC2.
