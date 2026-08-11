-- Migration 009 — اطمینان از وجود ستون‌های جدید در جدول اخبار (news)
-- لطفاً این فایل را در دیتابیس خود Import کنید یا کدهای زیر را در بخش SQL در phpMyAdmin اجرا نمایید.

SET NAMES utf8mb4;

-- اضافه کردن ستون subtitle در صورت عدم وجود (پشتیبانی در MariaDB / MySQL 8+)
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `subtitle` VARCHAR(255) DEFAULT NULL AFTER `title`;

-- اضافه کردن سایر ستون‌های احتمالی که ممکن است در دیتابیس شما وجود نداشته باشند:
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `keywords` VARCHAR(255) DEFAULT NULL AFTER `category_id`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `tags` VARCHAR(255) DEFAULT NULL AFTER `keywords`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `read_time` INT(11) DEFAULT 0 AFTER `viewed`;
