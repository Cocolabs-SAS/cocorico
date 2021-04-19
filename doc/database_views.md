## Database views

```sql
CREATE VIEW vue_qualification_siae AS
    SELECT
        d.id as id,
        sd.cnt as secteurs,
        sd.source,
        d.department,
        d.region,
        d.kind,
        d.nature,
        d.brand,
        d.name
    FROM directory d
    LEFT JOIN (
        SELECT directory_id, source, COUNT(*) AS cnt
        FROM directory_listing_category
        GROUP BY directory_id, source
    ) sd ON sd.directory_id = d.id;
```
