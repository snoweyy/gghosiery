USE gg_hosiery;

ALTER TABLE products
ADD COLUMN image_path VARCHAR(255) NULL AFTER color;
