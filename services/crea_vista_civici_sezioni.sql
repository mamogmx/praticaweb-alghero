CREATE OR REPLACE VIEW civici_sezioni AS 
 SELECT b.civicoid AS civico_id, b.civiconum::integer AS civico_numero, COALESCE(b.civicosub, ''::character varying) AS civico_esponente, b.tpstrid AS strada_id, c.sez2001::integer AS sezione_2001
   FROM dbt_topociv.dbt_accpc a
   JOIN dbt_topociv.dbt_civico b ON a.civicoid = b.civicoid::numeric
   LEFT JOIN sezioni c ON st_contains(c.the_geom, a.accpcpos)
  WHERE c.sez2001 IS NOT NULL;