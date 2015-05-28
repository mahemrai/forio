CREATE TABLE users (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `email` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `firstname` varchar(100) NULL,
    `lastname` varchar(100) NULL,
    `title` varchar(255) NULL,
    `location` varchar(255) NULL,
    `profile_pic` varchar(255) NULL,
    `bio` text NULL,
    `clients` text NULL,
    `last_login` datetime,
    `created_at` datetime,
    `updated_at` datetime
);

CREATE TABLE experiences(
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `title` varchar(100) NOT NULL,
    `organisation` varchar(255) NULL,
    `location` varchar(255) NULL,
    `start_date` varchar(25),
    `end_date` varchar(25),
    `created_at` datetime,
    `updated_at` datetime
);

CREATE TABLE educations(
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `course` varchar(255) NOT NULL,
    `school` varchar(255) NOT NULL,
    `location` varchar(255) NOT NULL,
    `start_year` varchar(10),
    `end_year` varchar(10),
    `created_at` datetime,
    `updated_at` datetime
);

CREATE TABLE images (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `project_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `filename` varchar(255) NOT NULL,
    `thumbnail` varchar(255) NOT NULL,
    `created_at` datetime,
    `updated_at` datetime,
    CONSTRAINT fk_projectImage1 FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE TABLE projects (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `type` varchar(255) NULL,
    `for` varchar(255) NULL,
    `project_date` varchar(50) NULL,
    `published` int(1) DEFAULT 0,
    `created_at` datetime,
    `updated_at` datetime
);

CREATE TABLE keywords (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `name` varchar(255) NOT NULL,
    `created_at` datetime,
    `updated_at` datetime
);

CREATE TABLE project_keyword(
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `project_id` int(11) NOT NULL,
    `keyword_id` int(11) NOT NULL,
    `created_at` datetime,
    `updated_at` datetime,
    CONSTRAINT fk_projectKeyword1 FOREIGN KEY (project_id) REFERENCES projects(id),
    CONSTRAINT fk_projectKeyword2 FOREIGN KEY (keyword_id) REFERENCES keywords(id)
);