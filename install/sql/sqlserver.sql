-- 创建用户表
CREATE TABLE users
(
    user_id    INT IDENTITY (1,1) PRIMARY KEY,
    username   NVARCHAR(255) NOT NULL,
    email      NVARCHAR(255) NOT NULL,
    password   NVARCHAR(255) NOT NULL,
    role       NVARCHAR(50) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    avatar_url NVARCHAR(255),
    metadata   varchar(max),
    last_login DATETIME,
    status     NVARCHAR(50) DEFAULT 'inactive' CHECK (status IN ('active', 'inactive', 'deleted', 'banned', 'suspended')),
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
);

-- 创建站点表
CREATE TABLE sites
(
    site_id        INT IDENTITY (1,1) PRIMARY KEY,
    user_id        INT           NOT NULL,
    site_name      NVARCHAR(255) NOT NULL,
    site_url       NVARCHAR(255) NOT NULL,
    site_desc      varchar(max),
    site_image_url NVARCHAR(255),
    site_config    varchar(max),
    site_status    NVARCHAR(50) DEFAULT 'private' CHECK (site_status IN ('public', 'private')),
    site_ext       varchar(max),
    status         NVARCHAR(50) DEFAULT 'inactive' CHECK (status IN ('active', 'inactive', 'deleted')),
    created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- 创建Tag表
CREATE TABLE tags
(
    tag_id        INT IDENTITY (1,1) PRIMARY KEY,
    user_id       INT           NOT NULL,
    tag_name      NVARCHAR(255) NOT NULL,
    tag_code      varchar(max)  NOT NULL,
    tag_type      NVARCHAR(50)  NOT NULL CHECK (tag_type IN ('html', 'css', 'js')),
    tag_status    NVARCHAR(50) DEFAULT 'private' CHECK (tag_status IN ('public', 'private')),
    tag_desc      varchar(max),
    tag_image_url NVARCHAR(255),
    tag_config    varchar(max),
    tag_ext       varchar(max),
    status        NVARCHAR(50) DEFAULT 'inactive' CHECK (status IN ('active', 'inactive', 'deleted')),
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- 创建项目表
CREATE TABLE projects
(
    project_id        INT IDENTITY (1,1) PRIMARY KEY,
    user_id           INT           NOT NULL,
    project_name      NVARCHAR(255) NOT NULL,
    project_desc      TEXT,
    project_image_url NVARCHAR(255),
    project_config    varchar(max),
    project_ext       varchar(max),
    project_status    NVARCHAR(50) DEFAULT 'private' CHECK (project_status IN ('public', 'private')),
    created_at        DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- 创建访问记录表
CREATE TABLE site_access
(
    access_id   INT IDENTITY (1,1) PRIMARY KEY,
    access_data varchar(max),
    site_id     INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites (site_id)
);

-- 创建访问记录表
CREATE TABLE tag_access
(
    access_id   INT IDENTITY (1,1) PRIMARY KEY,
    access_data varchar(max),
    tag_id      INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tag_id) REFERENCES tags (tag_id)
);

-- 创建配置表
CREATE TABLE config
(
    k NVARCHAR(255) PRIMARY KEY,
    v NVARCHAR(MAX)
);