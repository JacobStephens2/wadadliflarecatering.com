-- Apply once to the existing production database before deploying issue #7.
ALTER TABLE contact_submissions
    ADD COLUMN flag_reason VARCHAR(255) NULL,
    ADD INDEX idx_email_submitted_at (email, submitted_at);
ALTER TABLE quote_requests
    ADD COLUMN flag_reason VARCHAR(255) NULL,
    ADD INDEX idx_email_submitted_at (email, submitted_at);
