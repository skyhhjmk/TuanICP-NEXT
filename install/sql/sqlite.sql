-- 创建用户表
CREATE TABLE users
(
    user_id    INTEGER PRIMARY KEY AUTOINCREMENT,
    username   TEXT NOT NULL,
    email      TEXT NOT NULL,
    password   TEXT NOT NULL,
    role       TEXT DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    avatar_url TEXT,
    metadata   TEXT,
    last_login TIMESTAMP,
    status     TEXT DEFAULT 'inactive' CHECK (status IN ('active', 'inactive', 'deleted', 'banned', 'suspended')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 创建站点表
CREATE TABLE sites
(
    site_id        INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL,
    site_name      TEXT NOT NULL,
    site_url       TEXT NOT NULL,
    site_desc      TEXT,
    site_image_url TEXT,
    site_config    TEXT,
    site_status    TEXT DEFAULT 'private' CHECK (site_status IN ('public', 'private')),
    site_ext       TEXT,
    status         TEXT DEFAULT 'inactive' CHECK (status IN ('active', 'inactive', 'deleted')),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- 创建Tag表
CREATE TABLE tags
(
    tag_id        INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id       INTEGER NOT NULL,
    tag_name      TEXT NOT NULL,
    tag_code      TEXT NOT NULL,
    tag_type      TEXT NOT NULL CHECK (tag_type IN ('html', 'css', 'js')),
    tag_status    TEXT DEFAULT 'private' CHECK (tag_status IN ('public', 'private')),
    tag_desc      TEXT,
    tag_image_url TEXT,
    tag_config    TEXT,
    tag_ext       TEXT,
    status        TEXT DEFAULT 'inactive' CHECK (status IN ('active', 'inactive', 'deleted')),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- 创建项目表
CREATE TABLE projects
(
    project_id        INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id           INTEGER NOT NULL,
    project_name      TEXT NOT NULL,
    project_desc      TEXT,
    project_image_url TEXT,
    project_config    TEXT,
    project_ext       TEXT,
    project_status    TEXT DEFAULT 'private' CHECK (project_status IN ('public', 'private')),
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- 创建访问记录表
CREATE TABLE site_access
(
    access_id INTEGER PRIMARY KEY AUTOINCREMENT,
    access_data TEXT,
    site_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites (site_id)
);

-- 创建访问记录表
CREATE TABLE tag_access
(
    access_id INTEGER PRIMARY KEY AUTOINCREMENT,
    access_data TEXT,
    tag_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tag_id) REFERENCES tags (tag_id)
);

-- 创建配置表
CREATE TABLE config
(
    k TEXT PRIMARY KEY,
    v TEXT
);