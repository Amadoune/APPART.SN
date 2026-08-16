# Exact Set Comparison Evidence

Le helper de certification forme les clés réelles depuis `consumerId:eventType:payloadVersion`, trie indépendamment expected et actual, puis vérifie séparément :

- clés autorisées manquantes = `[]`;
- clés réelles inattendues = `[]`;
- expected trié === actual unique trié;
- total brut = 55.

L'attendu est une constante de test explicite; il n'est généré ni depuis le provider, ni depuis les enums productifs.
