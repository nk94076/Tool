-- The redesigned admin Secret Santa Events page shows a short tagline per
-- event (e.g. "Let's make this holiday season special!"); back it with a
-- real column instead of a UI-only input.
ALTER TABLE secret_santa_events ADD COLUMN description VARCHAR(255) NULL AFTER name;
