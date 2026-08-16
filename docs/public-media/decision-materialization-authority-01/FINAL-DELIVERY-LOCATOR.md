# Final delivery locator

Forme exacte : `/media/{mediaId}/revisions/{assetVersion}`.

Même révision donne le même locator; nouvelle assetVersion donne un nouveau locator; order/primary seuls ne le changent pas. Le payload persiste le path relatif et l'origine HTTPS est injectée à la frontière read/HTTP. L'ancien locator devient 404 après changement ou révocation.
