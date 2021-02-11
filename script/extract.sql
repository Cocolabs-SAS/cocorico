COPY(
SELECT u.id as user_id,
       u.last_name,
       u.first_name,
       u.phone,
       u.email,
       siae.id as id,
       siae.siret,
       siae.naf,
       siae.kind,
       siae.name,
       siae.brand,
       siae.phone,
       siae.email,
       siae.website,
       siae.description,
       siae.address_line_1,
       siae.address_line_2,
       siae.post_code,
       siae.city,
       siae.department,
       siae.source,
       ST_X(siae.coords::geometry) AS longitude,
       ST_Y(siae.coords::geometry) AS latitude,
       convention.is_active
FROM siaes_siae AS siae
LEFT OUTER JOIN siaes_siaeconvention AS convention ON convention.id = siae.convention_id
LEFT OUTER JOIN siaes_siaemembership AS membership ON membership.siae_id = siae.id
LEFT OUTER JOIN users_user AS u ON membership.user_id = u.id
) TO STDOUT WITH CSV DELIMITER '|' HEADER
