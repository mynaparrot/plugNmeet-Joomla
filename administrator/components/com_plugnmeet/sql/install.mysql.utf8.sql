CREATE TABLE `#__plugnmeet_rooms`
(
    `id`                   int(11)          NOT NULL AUTO_INCREMENT,
    `asset_id`             int(10) unsigned NOT NULL DEFAULT 0,
    `cat`                  int(11)          NOT NULL,
    `room_id`              varchar(255)     NOT NULL,
    `room_title`           varchar(255)     NOT NULL,
    `alias`                varchar(255)     NOT NULL,
    `description`          text                      DEFAULT NULL,
    `moderator_pass`       varchar(255)     NOT NULL,
    `attendee_pass`        varchar(255)     NOT NULL,
    `welcome_message`      text             NOT NULL,
    `max_participants`     int(10)          NOT NULL,
    `room_metadata`        json             NOT NULL,
    `design_customisation` json             NOT NULL,
    `params`               text                      DEFAULT NULL,
    `state`                int(1)                    DEFAULT 1,
    `created_by`           int(10)          NOT NULL,
    `created`              datetime         NOT NULL DEFAULT current_timestamp(),
    `modified_by`          int(10)                   DEFAULT NULL,
    `modified`             datetime                  DEFAULT NULL ON UPDATE current_timestamp(),
    `checked_out`          int(11) unsigned          DEFAULT NULL,
    `checked_out_time`     datetime                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `room_id` (`room_id`),
    KEY `state` (`state`),
    KEY `created_by` (`created_by`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

