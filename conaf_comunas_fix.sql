-- Corrección de 7 destinos que usaban grafías antiguas de comuna (Coihaique/Aisen)
UPDATE destinations d JOIN communes c ON c.slug='coyhaique'
SET d.commune_id = c.id WHERE d.slug IN ('parque-nacional-cerro-castillo','reserva-nacional-coyhaique','reserva-nacional-trapananda','monumento-natural-dos-lagunas') AND d.commune_id IS NULL;

UPDATE destinations d JOIN communes c ON c.slug='aysen'
SET d.commune_id = c.id WHERE d.slug IN ('parque-nacional-laguna-san-rafael','reserva-nacional-las-guaitecas','monumento-natural-cinco-hermanas') AND d.commune_id IS NULL;
