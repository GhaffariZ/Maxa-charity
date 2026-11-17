-- Apply once to an existing database before publishing articles on /patientoption.
INSERT INTO news_categories (name, slug, is_active, sort_order)
SELECT 'مقالات بیماران', 'patients-articles', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM news_categories WHERE slug = 'patients-articles');
