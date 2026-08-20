-- Elomiah — schema MySQL 8+ (InnoDB, utf8mb4)
-- Charset e engine obrigatórios em todas as tabelas.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `elomiah`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `elomiah`;

DROP TABLE IF EXISTS `rate_limits`;
DROP TABLE IF EXISTS `recuperacao_senhas`;
DROP TABLE IF EXISTS `visitantes`;
DROP TABLE IF EXISTS `solicitacoes_lgpd`;
DROP TABLE IF EXISTS `consentimentos_lgpd`;
DROP TABLE IF EXISTS `logs_admin`;
DROP TABLE IF EXISTS `mensagens_contato`;
DROP TABLE IF EXISTS `faqs_curso`;
DROP TABLE IF EXISTS `modulos_curso`;
DROP TABLE IF EXISTS `cursos`;
DROP TABLE IF EXISTS `depoimentos`;
DROP TABLE IF EXISTS `itens_pedido`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `imagens_produto`;
DROP TABLE IF EXISTS `produtos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `enderecos`;
DROP TABLE IF EXISTS `configuracoes`;
DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `senha_hash` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  `status` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `email_verified_at` DATETIME DEFAULT NULL,
  `failed_login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_email` (`email`),
  KEY `idx_usuarios_role` (`role`),
  KEY `idx_usuarios_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `enderecos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `cep` VARCHAR(8) NOT NULL,
  `logradouro` VARCHAR(180) NOT NULL,
  `numero` VARCHAR(20) NOT NULL,
  `complemento` VARCHAR(80) DEFAULT NULL,
  `bairro` VARCHAR(120) NOT NULL,
  `cidade` VARCHAR(120) NOT NULL,
  `estado` CHAR(2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enderecos_usuario` (`usuario_id`),
  KEY `idx_enderecos_cep` (`cep`),
  CONSTRAINT `fk_enderecos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categorias` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `descricao` VARCHAR(255) DEFAULT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categorias_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `produtos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` INT UNSIGNED DEFAULT NULL,
  `nome` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `descricao` TEXT,
  `descricao_curta` VARCHAR(280) DEFAULT NULL,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `preco_promocional` DECIMAL(10,2) DEFAULT NULL,
  `estoque` INT NOT NULL DEFAULT 0,
  `sku` VARCHAR(40) DEFAULT NULL,
  `volume` VARCHAR(40) DEFAULT NULL,
  `notas_topo` VARCHAR(255) DEFAULT NULL,
  `notas_coracao` VARCHAR(255) DEFAULT NULL,
  `notas_fundo` VARCHAR(255) DEFAULT NULL,
  `ficha_tecnica` TEXT,
  `cor_destaque` VARCHAR(7) NOT NULL DEFAULT '#1B4332',
  `aroma` VARCHAR(80) DEFAULT NULL,
  `citacao` VARCHAR(320) DEFAULT NULL,
  `colecao` VARCHAR(80) DEFAULT 'Coleção Refúgio',
  `modo_usar` TEXT,
  `precaucoes` TEXT,
  `destaque` TINYINT(1) NOT NULL DEFAULT 0,
  `achadinho_geo` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `compra_tipo` ENUM('carrinho','shopee','amazon','mercadolivre') NOT NULL DEFAULT 'carrinho',
  `url_shopee` VARCHAR(500) DEFAULT NULL,
  `url_amazon` VARCHAR(500) DEFAULT NULL,
  `url_mercadolivre` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_produtos_slug` (`slug`),
  KEY `idx_produtos_categoria` (`categoria_id`),
  KEY `idx_produtos_status` (`status`),
  KEY `idx_produtos_destaque` (`destaque`),
  CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `imagens_produto` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `produto_id` INT UNSIGNED NOT NULL,
  `caminho` VARCHAR(255) NOT NULL,
  `alt` VARCHAR(180) DEFAULT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_imagens_produto` (`produto_id`),
  CONSTRAINT `fk_imagens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pedidos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `endereco_id` INT UNSIGNED DEFAULT NULL,
  `codigo` VARCHAR(20) NOT NULL,
  `status` ENUM('pendente','pago','enviado','entregue','cancelado') NOT NULL DEFAULT 'pendente',
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `frete` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `desconto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pagamento` VARCHAR(40) DEFAULT 'pix',
  `cartao_bandeira` VARCHAR(20) DEFAULT NULL,
  `cartao_final` CHAR(4) DEFAULT NULL,
  `parcelas` TINYINT UNSIGNED DEFAULT NULL,
  `mp_preference_id` VARCHAR(80) DEFAULT NULL,
  `mp_init_point` VARCHAR(500) DEFAULT NULL,
  `mp_payment_id` VARCHAR(40) DEFAULT NULL,
  `mp_status` VARCHAR(40) DEFAULT NULL,
  `pix_qr_code` TEXT DEFAULT NULL,
  `pix_qr_base64` MEDIUMTEXT DEFAULT NULL,
  `codigo_rastreio` VARCHAR(80) DEFAULT NULL,
  `transportadora` VARCHAR(80) DEFAULT NULL,
  `pago_em` DATETIME DEFAULT NULL,
  `enviado_em` DATETIME DEFAULT NULL,
  `entregue_em` DATETIME DEFAULT NULL,
  `cancelado_em` DATETIME DEFAULT NULL,
  `observacoes` TEXT,
  `nome_cliente` VARCHAR(120) DEFAULT NULL,
  `email_cliente` VARCHAR(180) DEFAULT NULL,
  `telefone_cliente` VARCHAR(20) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pedidos_codigo` (`codigo`),
  KEY `idx_pedidos_status` (`status`),
  KEY `idx_pedidos_usuario` (`usuario_id`),
  KEY `idx_pedidos_created` (`created_at`),
  CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedidos_endereco` FOREIGN KEY (`endereco_id`) REFERENCES `enderecos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `itens_pedido` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_id` INT UNSIGNED NOT NULL,
  `produto_id` INT UNSIGNED DEFAULT NULL,
  `nome_produto` VARCHAR(160) NOT NULL,
  `quantidade` INT UNSIGNED NOT NULL DEFAULT 1,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_itens_pedido` (`pedido_id`),
  KEY `idx_itens_produto` (`produto_id`),
  CONSTRAINT `fk_itens_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `depoimentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `produto_id` INT UNSIGNED DEFAULT NULL,
  `nome` VARCHAR(120) NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `nota` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `texto` TEXT NOT NULL,
  `status` ENUM('pendente','aprovado','reprovado') NOT NULL DEFAULT 'pendente',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_depoimentos_status` (`status`),
  CONSTRAINT `fk_depoimentos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_depoimentos_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cursos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(180) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `descricao` TEXT,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `imagem` VARCHAR(255) DEFAULT NULL,
  `acesso_url` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cursos_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `modulos_curso` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `curso_id` INT UNSIGNED NOT NULL,
  `titulo` VARCHAR(180) NOT NULL,
  `descricao` TEXT,
  `ordem` INT NOT NULL DEFAULT 0,
  `duracao` VARCHAR(40) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_modulos_curso` (`curso_id`),
  CONSTRAINT `fk_modulos_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `faqs_curso` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `curso_id` INT UNSIGNED NOT NULL,
  `pergunta` VARCHAR(255) NOT NULL,
  `resposta` TEXT NOT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_faqs_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs_admin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `acao` VARCHAR(180) NOT NULL,
  `entidade` VARCHAR(80) DEFAULT NULL,
  `entidade_id` INT UNSIGNED DEFAULT NULL,
  `dados_anteriores` TEXT,
  `dados_novos` TEXT,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_usuario` (`usuario_id`),
  KEY `idx_logs_created` (`created_at`),
  CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `consentimentos_lgpd` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `email` VARCHAR(180) DEFAULT NULL,
  `tipo` VARCHAR(60) NOT NULL,
  `aceito` TINYINT(1) NOT NULL DEFAULT 1,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_consent_email` (`email`),
  CONSTRAINT `fk_consent_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `solicitacoes_lgpd` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `tipo` ENUM('exportar','excluir') NOT NULL,
  `mensagem` TEXT,
  `status` ENUM('pendente','atendida') NOT NULL DEFAULT 'pendente',
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lgpd_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `visitantes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sessao` VARCHAR(128) NOT NULL,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `pagina` VARCHAR(180) NOT NULL,
  `ip_hash` CHAR(64) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visitantes_pagina` (`pagina`),
  KEY `idx_visitantes_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `configuracoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(80) NOT NULL,
  `valor` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mensagens_contato` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `assunto` VARCHAR(160) DEFAULT NULL,
  `mensagem` TEXT NOT NULL,
  `lido` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rate_limits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `acao` VARCHAR(40) NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_acao_ip` (`acao`, `ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notificacoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo` VARCHAR(40) NOT NULL,
  `titulo` VARCHAR(180) NOT NULL,
  `mensagem` VARCHAR(255) DEFAULT NULL,
  `link` VARCHAR(180) DEFAULT NULL,
  `pedido_id` INT UNSIGNED DEFAULT NULL,
  `lida` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notificacoes_lida` (`lida`, `created_at`),
  KEY `idx_notificacoes_pedido` (`pedido_id`),
  CONSTRAINT `fk_notificacoes_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recuperacao_senhas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(180) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_recup_token` (`token_hash`),
  KEY `idx_recup_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
