<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: users, orders, order_items.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        if ('mysql' === $platform) {
            $this->addSql(<<<'SQL'
                CREATE TABLE users (
                    id INT AUTO_INCREMENT NOT NULL,
                    username VARCHAR(100) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    roles JSON NOT NULL,
                    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    UNIQUE INDEX UNIQ_users_username (username),
                    UNIQUE INDEX UNIQ_users_email (email),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

            $this->addSql(<<<'SQL'
                CREATE TABLE orders (
                    id INT AUTO_INCREMENT NOT NULL,
                    user_id INT DEFAULT NULL,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    phone VARCHAR(50) NOT NULL,
                    city VARCHAR(100) NOT NULL,
                    address VARCHAR(255) NOT NULL,
                    delivery VARCHAR(100) NOT NULL,
                    payment VARCHAR(100) NOT NULL,
                    total_sum BIGINT DEFAULT 0 NOT NULL,
                    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    INDEX IDX_orders_user (user_id),
                    PRIMARY KEY(id),
                    CONSTRAINT FK_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

            $this->addSql(<<<'SQL'
                CREATE TABLE order_items (
                    id INT AUTO_INCREMENT NOT NULL,
                    order_id INT NOT NULL,
                    category VARCHAR(255) NOT NULL,
                    size VARCHAR(50) NOT NULL,
                    quantity INT NOT NULL,
                    INDEX IDX_order_items_order (order_id),
                    PRIMARY KEY(id),
                    CONSTRAINT FK_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

            return;
        }

        // SQLite (used by the test suite).
        $this->addSql(<<<'SQL'
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                username VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                roles CLOB NOT NULL,
                created_at DATETIME NOT NULL
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_users_username ON users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_users_email ON users (email)');

        $this->addSql(<<<'SQL'
            CREATE TABLE orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                user_id INTEGER DEFAULT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                city VARCHAR(100) NOT NULL,
                address VARCHAR(255) NOT NULL,
                delivery VARCHAR(100) NOT NULL,
                payment VARCHAR(100) NOT NULL,
                total_sum BIGINT DEFAULT 0 NOT NULL,
                created_at DATETIME NOT NULL,
                CONSTRAINT FK_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_orders_user ON orders (user_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                order_id INTEGER NOT NULL,
                category VARCHAR(255) NOT NULL,
                size VARCHAR(50) NOT NULL,
                quantity INTEGER NOT NULL,
                CONSTRAINT FK_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_order_items_order ON order_items (order_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE users');
    }
}
