-- 创建用户表
CREATE TABLE users
(
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       VARCHAR(255) NOT NULL                  DEFAULT 'user',
    avatar_url VARCHAR(255) NOT NULL                  DEFAULT 'https://cravatar.cn/avatar/245467ef31b6f0addc72b039b94122a4?s=100&f=y&r=g',
    last_login DATETIME                               DEFAULT NOW(),
    status     ENUM ('active', 'inactive' , 'banned') DEFAULT 'inactive',
    -- 可以添加其他用户相关的字段
    created_at DATETIME                               DEFAULT NOW()
) ENGINE = InnoDB;

INSERT INTO users (user_id, username, email, password, role,status)
VALUES (0, 'guest', 'guest', 'guest','guest','inactive'),
       (null, 'admin', 'admin@example.com', 'admin','super_admin','active');

CREATE TABLE user_meta
(
    meta_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    meta_key   VARCHAR(255) NOT NULL,
    meta_value TEXT,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
) ENGINE = InnoDB;

-- 创建站点表
CREATE TABLE sites
(
    site_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT          NOT NULL,
    site_name       VARCHAR(255) NOT NULL,
    site_domain        VARCHAR(255) NOT NULL,
    site_icp_number VARCHAR(8) NOT NULL UNIQUE,
    site_desc       TEXT,
    site_avatar_url VARCHAR(255),
    site_config     TEXT,
    site_status     ENUM ('public', 'private')                 DEFAULT 'public',
    site_ext        TEXT,
    status          ENUM ('awaiting', 'approved' , 'rejected') DEFAULT 'awaiting',
    -- 可以添加其他站点相关的字段
    created_at      DATETIME                                   DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users (user_id)
) ENGINE = InnoDB;

CREATE TABLE site_meta
(
    meta_id    INT AUTO_INCREMENT PRIMARY KEY,
    site_id    INT          NOT NULL,
    meta_key   VARCHAR(255) NOT NULL,
    meta_value TEXT,
    FOREIGN KEY (site_id) REFERENCES sites (site_id)
) ENGINE = InnoDB;

-- 创建访问记录表
CREATE TABLE site_access
(
    access_id   INT AUTO_INCREMENT PRIMARY KEY,
    access_data TEXT,
    site_id     INT NOT NULL,
    created_at  DATETIME DEFAULT NOW(),
    FOREIGN KEY (site_id) REFERENCES sites (site_id)
) ENGINE = InnoDB;

-- 创建配置表
CREATE TABLE config
(
    k VARCHAR(255) PRIMARY KEY,
    v TEXT
) ENGINE = InnoDB;

