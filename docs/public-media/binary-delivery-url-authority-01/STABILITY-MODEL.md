# Stability model

- même MediaId + même assetVersion → même locator;
- storageKey ou backend déplacé sans changement de contenu/révision → locator inchangé;
- nouvelle révision binaire → nouveau locator;
- changement de host/environnement → locator inchangé, URL absolue dérivée différente;
- redeploy → locator inchangé.

L'identité publique ne dépend ni d'un chemin filesystem ni d'un hostname.
