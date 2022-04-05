<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200622174457 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, shipment_method_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, unique_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', discr VARCHAR(255) NOT NULL, order_id INT DEFAULT NULL, notes VARCHAR(255) DEFAULT NULL, order_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', order_price INT DEFAULT NULL, points INT DEFAULT NULL, fio VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postcode VARCHAR(255) DEFAULT NULL, pick_up_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_BA388B7E3C68343 (unique_id), INDEX IDX_BA388B7A76ED395 (user_id), UNIQUE INDEX UNIQ_BA388B78D9F6D38 (order_id), INDEX IDX_BA388B7239B3F56 (shipment_method_id), INDEX IDX_BA388B74C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart_item (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, variant_id INT DEFAULT NULL, quantity INT NOT NULL, INDEX IDX_F0FE25278D9F6D38 (order_id), INDEX IDX_F0FE25273B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, seo_id INT DEFAULT NULL, parent INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, root TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_64C19C197E3DD86 (seo_id), INDEX IDX_64C19C13D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, cart_id INT DEFAULT NULL, discount INT NOT NULL, code VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, expire_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_64BF3F021AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feedback (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, payment_method_id INT DEFAULT NULL, currency_code VARCHAR(255) NOT NULL, amount INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', inv_id VARCHAR(255) DEFAULT NULL, payment_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', cancel_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', payment_status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_6D28840D6F6FD57F (inv_id), UNIQUE INDEX UNIQ_6D28840D5AA1164F (payment_method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, seo_id INT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, prod_id INT NOT NULL, vendor_code VARCHAR(255) NOT NULL, vendor VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(10000) NOT NULL, en_stock TINYINT(1) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', rating INT DEFAULT NULL, discount INT NOT NULL, brutto VARCHAR(255) DEFAULT NULL, batteries VARCHAR(255) DEFAULT NULL, pack VARCHAR(255) DEFAULT NULL, material VARCHAR(255) DEFAULT NULL, lenght VARCHAR(255) DEFAULT NULL, diameter VARCHAR(255) DEFAULT NULL, collection_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_D34A04AD97E3DD86 (seo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_category (product_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_CDFC73564584665A (product_id), INDEX IDX_CDFC735612469DE2 (category_id), PRIMARY KEY(product_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_image (product_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_64617F034584665A (product_id), UNIQUE INDEX UNIQ_64617F033DA5256D (image_id), PRIMARY KEY(product_id, image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_variant (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, size VARCHAR(255) DEFAULT NULL, a_id INT NOT NULL, barcode VARCHAR(255) NOT NULL, shiping_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', quantity_in_stock INT NOT NULL, currency_retail VARCHAR(255) DEFAULT NULL, currency_whole VARCHAR(255) DEFAULT NULL, retail_price DOUBLE PRECISION DEFAULT NULL, whole_price DOUBLE PRECISION DEFAULT NULL, base_retail_price DOUBLE PRECISION NOT NULL, base_whole_price DOUBLE PRECISION NOT NULL, discount INT NOT NULL, INDEX IDX_209AA41D4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, reviewer_id INT DEFAULT NULL, product_id INT DEFAULT NULL, rating INT NOT NULL, comment VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_794381C670574616 (reviewer_id), INDEX IDX_794381C64584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reviewer (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, nickname VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seo (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, h1 VARCHAR(255) DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(300) DEFAULT NULL, html VARCHAR(10000) DEFAULT NULL, route VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipment (id INT AUTO_INCREMENT NOT NULL, method_id INT DEFAULT NULL, tracking VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2CB20DC19883967 (method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipment_method (id INT AUTO_INCREMENT NOT NULL, price INT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE slider (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, position INT NOT NULL, UNIQUE INDEX UNIQ_CFC710073DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, fio VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', points INT NOT NULL, status VARCHAR(255) DEFAULT \'active\' NOT NULL, UNIQUE INDEX UNIQ_8D93D649444F97DD (phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7239B3F56 FOREIGN KEY (shipment_method_id) REFERENCES shipment_method (id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B74C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25278D9F6D38 FOREIGN KEY (order_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25273B69A9AF FOREIGN KEY (variant_id) REFERENCES product_variant (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C197E3DD86 FOREIGN KEY (seo_id) REFERENCES seo (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C13D8E604F FOREIGN KEY (parent) REFERENCES category (id)');
        $this->addSql('ALTER TABLE coupon ADD CONSTRAINT FK_64BF3F021AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D5AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment_method (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD97E3DD86 FOREIGN KEY (seo_id) REFERENCES seo (id)');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F033DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41D4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C670574616 FOREIGN KEY (reviewer_id) REFERENCES reviewer (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE shipment ADD CONSTRAINT FK_2CB20DC19883967 FOREIGN KEY (method_id) REFERENCES shipment_method (id)');
        $this->addSql('ALTER TABLE slider ADD CONSTRAINT FK_CFC710073DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25278D9F6D38');
        $this->addSql('ALTER TABLE coupon DROP FOREIGN KEY FK_64BF3F021AD5CDBF');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C13D8E604F');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC735612469DE2');
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F033DA5256D');
        $this->addSql('ALTER TABLE slider DROP FOREIGN KEY FK_CFC710073DA5256D');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B74C3A3BB');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D5AA1164F');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC73564584665A');
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');
        $this->addSql('ALTER TABLE product_variant DROP FOREIGN KEY FK_209AA41D4584665A');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C64584665A');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25273B69A9AF');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C670574616');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C197E3DD86');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD97E3DD86');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7239B3F56');
        $this->addSql('ALTER TABLE shipment DROP FOREIGN KEY FK_2CB20DC19883967');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE feedback');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE product_image');
        $this->addSql('DROP TABLE product_variant');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE reviewer');
        $this->addSql('DROP TABLE seo');
        $this->addSql('DROP TABLE shipment');
        $this->addSql('DROP TABLE shipment_method');
        $this->addSql('DROP TABLE slider');
        $this->addSql('DROP TABLE user');
    }
}
