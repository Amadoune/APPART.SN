# Timezone Policy

Timezone normative V1 : **UTC**.

L'autorité convertit explicitement le DateTimeImmutable reçu vers `DateTimeZone('UTC')` avant extraction de l'année. Elle ne consulte ni timezone PHP/OS, ni navigateur, ni Geography, ni configuration de machine.

Un même instant absolu portant des offsets différents produit le même BusinessYear. La représentation canonique recommandée de commande reste UTC avec microsecondes et suffixe `Z`.
