# Frontière de sécurité

La campagne préserve :

- aucun credential dans Git, documents, captures ou manifest ;
- aucune valeur de cookie documentée ;
- aucune session copiée entre profils ;
- aucun bypass TLS ;
- aucune extension Chrome ;
- aucun AccountId client utilisé comme autorité ;
- aucun rôle artificiel ;
- aucun SQL IAM direct ;
- principals Authoring et reviewer distincts ;
- DevTools utilisé en observation, jamais pour modifier cookies ou storage ;
- aucun JavaScript injecté pour contourner l'UI ;
- aucune commande rejouée avec une identité altérée.

Tout écart invalide la campagne et déclenche le fail-fast.
