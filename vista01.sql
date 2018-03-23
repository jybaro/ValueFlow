SELECT *
,(
) AS capacidad_facturada
,(
) AS precio_mb
FROM sai_paso_atencion

LEFT OUTER JOIN sai_atencion
ON ate_borrado IS NULL
AND paa_atencion = ate_id

LEFT OUTER JOIN sai_estado_atencion
ON esa_borrado IS NULL
AND ate_estado_atencion = esa_id
 
WHERE paa_borrado IS NULL
AND NOT paa_confirmado IS NULL

AND(
	esa_nombre ILIKE '%servicio activo%'
	OR esa_nombre ILIKE '%incremento%'
	OR esa_nombre ILIKE '%decremento%'
)