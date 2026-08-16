# Trigger and handoff

`ListingPublished` existe, est durablement transporté et fournit ListingId/publicationVersion. Il est un trigger technique plausible : le consumer peut ensuite relire les owners. Le même consumer alimente déjà Search et ContentSeo.

Il n'est pas certifié ici comme trigger final, car le handoff vers une représentation Geography révisionnée n'existe pas. Property promotion et les événements Place ne fournissent pas seuls la relation Published attendue.
