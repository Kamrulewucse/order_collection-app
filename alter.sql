-- 04/02/2025(Notes)

1. php artisan queue:work and php artisan serve ai 2 ta run korte hobe vai


ALTER TABLE `products` ADD `sub_category_id` BIGINT NULL DEFAULT NULL AFTER `category_id`;

-- 24/02/2025
ALTER TABLE `tasks` ADD `task_total_cost` DOUBLE(8,2) NULL DEFAULT '0' AFTER `task_no`;
ALTER TABLE `task_details` ADD `task_cost` DOUBLE(8,2) NULL DEFAULT '0' AFTER `task_details`;
