CREATE INDEX IF NOT EXISTS moderation_reports_queue_category_read_idx
    ON moderation_reports.queue_items (
        state,
        category,
        priority DESC,
        updated_at ASC,
        queue_item_id ASC
    );
