INSERT INTO page_categories (id, code, name, is_index_visible, is_url_visible, is_seo_visible, created_at, updated_at) VALUES (36, 'info_protection', 'a:2:{s:2:"ru";s:33:"Защита информации";s:2:"en";s:25:"Protection of information";}', 0, 1, 0, 1485519309, 1485519309);

INSERT INTO page_category_props (id, type, code, name, page_category_id, is_multivalue) VALUES (161, 1, 'info_protection_text', 'a:2:{s:2:"ru";s:46:"Текст "Защита информации"";s:2:"en";s:32:"Text "Protection of information"";}', 1, 0);
INSERT INTO page_category_props (id, type, code, name, page_category_id, is_multivalue) VALUES (162, 1, 'page_title', 'a:2:{s:2:"ru";s:35:"Заголовок страницы";s:2:"en";s:10:"Page title";}', 36, 0);
INSERT INTO page_category_props (id, type, code, name, page_category_id, is_multivalue) VALUES (163, 5, 'page_body', 'a:2:{s:2:"ru";s:25:"Тело страницы";s:2:"en";s:9:"Page body";}', 36, 0);

INSERT INTO pages (id, name, text, seo_title, seo_keywords, seo_description, url, code, noindex, is_disabled, created_at, updated_at, images, page_category_id, sort) VALUES (192, 'a:2:{s:2:"ru";s:33:"Защита информации";s:2:"en";s:25:"Protection of information";}', 'a:2:{s:2:"ru";s:0:"";s:2:"en";s:0:"";}', 'a:0:{}', 'a:0:{}', 'a:0:{}', 'info_protection', 'info_protection', 0, 0, 1485519445, 1485519521, 'a:0:{}', 36, 100);

INSERT INTO page_props (id, page_id, page_category_prop_id, entity_id, multilang_value, value) VALUES (559, 1, 161, null, 'a:2:{s:2:"ru";s:33:"Защита информации";s:2:"en";s:25:"Protection of information";}', null);
INSERT INTO page_props (id, page_id, page_category_prop_id, entity_id, multilang_value, value) VALUES (560, 192, 162, null, 'a:2:{s:2:"ru";s:33:"Защита информации";s:2:"en";s:25:"Protection of information";}', null);
INSERT INTO page_props (id, page_id, page_category_prop_id, entity_id, multilang_value, value) VALUES (561, 192, 163, null, 'a:2:{s:2:"ru";s:0:"";s:2:"en";s:0:"";}', null);