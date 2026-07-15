-- Migration 007 — افزودن فیلد زیرعنوان (subtitle) به جدول اخبار (news)
SET NAMES utf8mb4;

ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `subtitle` VARCHAR(255) DEFAULT NULL AFTER `title`;
