-- The redesigned admin Departments page adds an optional description field
-- per department; back it with a real column instead of a UI-only input.
ALTER TABLE departments ADD COLUMN description VARCHAR(255) NULL AFTER name;
