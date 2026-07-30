# Stratégie de checkpoint Rebuild Runtime

Le checkpoint est un token Base64 URL-safe contenant :

- une version de format ;
- l'empreinte SHA-256 du scope exact ;
- la dernière identité rendue.

Le token est opaque pour l'appelant. Il est refusé si son encodage est invalide, si sa version est inconnue, si le curseur est vide ou s'il appartient à un autre scope.

La reprise utilise une condition keyset `id > last_id`. Une page déjà traitée peut être rejouée avec son checkpoint d'entrée : les mêmes identités sont produites, et le Writer converge vers `AlreadyApplied`.
