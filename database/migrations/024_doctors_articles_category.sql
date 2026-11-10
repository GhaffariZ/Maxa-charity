-- Apply once to an existing database before publishing articles on /doctorspage.
INSERT INTO news_categories (name, slug, is_active, sort_order)
SELECT 'مقالات کادر درمان', 'doctors-articles', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM news_categories WHERE slug = 'doctors-articles');
