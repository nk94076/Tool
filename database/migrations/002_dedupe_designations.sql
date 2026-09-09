-- designations had no unique constraint on `name`, so every deploy's
-- `INSERT IGNORE` re-seed silently duplicated the same rows. Reassign any
-- employee profiles pointing at a duplicate row to the lowest-id row for
-- that name, drop the duplicates, then add the unique key so it can't
-- happen again (matches the existing constraint on departments.name).

UPDATE employee_profiles ep
JOIN designations dup ON dup.id = ep.designation_id
JOIN (
    SELECT name, MIN(id) AS keep_id
    FROM designations
    GROUP BY name
) keep ON keep.name = dup.name
SET ep.designation_id = keep.keep_id
WHERE dup.id <> keep.keep_id;

DELETE dup FROM designations dup
JOIN (
    SELECT name, MIN(id) AS keep_id
    FROM designations
    GROUP BY name
) keep ON keep.name = dup.name
WHERE dup.id <> keep.keep_id;

ALTER TABLE designations ADD UNIQUE KEY uq_designation_name (name);
