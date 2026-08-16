# Architecture boundary

Public Geography possède facts V2; ContentSeo possède adaptation SEO/locality; Public Projection possède DTO/read model; HTTP/Blade possède rendu accessible.

Les dépendances vont source → adapters → projection → UI. Public Geography ne connaît ni Blade ni routes; aucun consumer ne fabrique Geography.
