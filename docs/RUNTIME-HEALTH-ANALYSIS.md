# Sprint 3.7F — Runtime Health Analysis

La santé Runtime est une inspection de composition, pas un probe d'infrastructure. Elle vérifie que
chaque capacité certifiée possède une implémentation enregistrée, configurée, compatible avec son
contrat et sans dépendance déclarée manquante.

L'inspecteur n'appelle aucune méthode des composants. Il ne prouve donc ni la connectivité réseau ni
l'état instantané d'une base ; ces contrôles opérationnels appartiendront au Runtime. Cette fondation
répond uniquement à la question déterministe : « la composition déclarée est-elle complète et
compatible ? ».

Dix capacités obligatoires sont déclarées : Lookup, Runtime Source, Geography, Public Media,
MediaCollection → Property, stratégie multi-cibles, Updater, Store, Rebuild Enumerator et Consumer.
