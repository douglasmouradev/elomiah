INSERT INTO `usuarios` (`nome`, `email`, `senha_hash`, `telefone`, `role`, `status`, `email_verified_at`) VALUES
('Geo Elomiah', 'admin@elomiah.com', '$2y$12$rGc715ufzNVkBWEmOgiOFewfCeGvvv4a2rmnFp9M9Wtbd9y2o3p1y', '11999999999', 'admin', 'ativo', NOW()),
('Ana Clara Mendes', 'ana@example.com', '$2y$12$rGc715ufzNVkBWEmOgiOFewfCeGvvv4a2rmnFp9M9Wtbd9y2o3p1y', '11988887777', 'cliente', 'ativo', NOW());

INSERT INTO `categorias` (`nome`, `slug`, `descricao`, `ordem`) VALUES
('Coleção Refúgio', 'colecao-refugio', 'Sprays de ambiente 120 ml', 1),
('Coleção Elo', 'colecao-elo', 'Home spray para os primeiros capítulos', 2),
('Difusor', 'difusor', 'Presença contínua no ambiente', 3),
('Kits', 'kits', 'Rituais completos', 4),
('Formação', 'formacao', 'Cursos com a Geo', 9);

INSERT INTO `produtos`
(`categoria_id`,`nome`,`slug`,`descricao`,`descricao_curta`,`preco`,`preco_promocional`,`estoque`,`sku`,`volume`,`notas_topo`,`notas_coracao`,`notas_fundo`,`ficha_tecnica`,`cor_destaque`,`aroma`,`citacao`,`colecao`,`modo_usar`,`precaucoes`,`destaque`,`achadinho_geo`,`status`,`compra_tipo`,`url_shopee`,`url_amazon`,`url_mercadolivre`)
VALUES
(1,'Despertar','despertar',
'Um aroma frutado, verde e luminoso que desperta os sentidos e renova os ambientes. A delicadeza da manga verde envolve o espaço com uma sensação de frescor, leveza e bem-estar.',
'Aroma manga verde. Frescor que abre o dia.',
89.90, NULL, 40, 'ELO-RF-DES', '120 ml',
'Manga verde',
'Folhas verdes, lírio-do-vale',
'Cedro, âmbar suave',
'Spray de ambiente 120 ml | 4,05 fl oz. Vidro, atomizador dourado. Álcool, água, fragrância e conservante.',
'#1B4332','Manga Verde',
'As maiores mudanças da nossa vida começam quando enxergamos o que sempre esteve diante de nós.',
'Coleção Refúgio',
'Borrife no ambiente, a cerca de 20 cm de distância, para criar uma atmosfera acolhedora. Evite aplicar diretamente sobre pessoas, animais e superfícies delicadas.',
'Manter fora do alcance de crianças. Inflamável. Evite contato com os olhos. Armazenar em local fresco, longe de fontes de calor.',
1, 0, 'ativo', 'carrinho', 'https://collshp.com/geovanaferreira030789?view=storefront', 'https://amazon.com.br', 'https://mercadolivre.com.br'),

(1,'Recomeço','recomeco',
'Um aroma fresco e envolvente que desperta novos começos. As notas frutadas do figo se encontram com a leveza das flores e o aconchego das madeiras, criando uma atmosfera de renovação e esperança.',
'Aroma figo. Renovação em névoa.',
89.90, NULL, 40, 'ELO-RF-REC', '120 ml',
'Figo, folhas verdes, bergamota',
'Lírio, jasmim, flor de laranjeira',
'Cedro, âmbar suave, musk',
'Spray de ambiente 120 ml | 4,05 fl oz. Vidro, atomizador dourado.',
'#B8A8C3','Figo',
'Existe uma versão sua esperando pela decisão de recomeçar.',
'Coleção Refúgio',
'Borrife no ambiente, a cerca de 20 cm de distância, para criar uma atmosfera acolhedora. Evite aplicar diretamente sobre pessoas, animais e superfícies delicadas.',
'Manter fora do alcance de crianças. Inflamável. Evite contato com os olhos. Armazenar em local fresco, longe de fontes de calor.',
1, 0, 'ativo', 'carrinho', 'https://collshp.com/geovanaferreira030789?view=storefront', 'https://amazon.com.br', 'https://mercadolivre.com.br'),

(1,'Encontro','encontro',
'Um aroma acolhedor e envolvente que inspira conexão, presença e entrega. A suavidade das notas florais se encontra com a profundidade resinosa do ládano, criando uma atmosfera de afeto, aceitação e verdade.',
'Aroma ládano. Presença que abraça.',
89.90, NULL, 40, 'ELO-RF-ENC', '120 ml',
'Bergamota, flor de laranjeira',
'Rosa, jasmim, íris',
'Ládano, âmbar, musk',
'Spray de ambiente 120 ml | 4,05 fl oz. Vidro, atomizador dourado.',
'#D6A2A2','Ládano',
'Os encontros mais importantes da nossa vida começam dentro de nós.',
'Coleção Refúgio',
'Borrife no ambiente, a cerca de 20 cm de distância, para criar uma atmosfera acolhedora. Evite aplicar diretamente sobre pessoas, animais e superfícies delicadas.',
'Manter fora do alcance de crianças. Inflamável. Evite contato com os olhos. Armazenar em local fresco, longe de fontes de calor.',
1, 0, 'ativo', 'carrinho', 'https://collshp.com/geovanaferreira030789?view=storefront', 'https://amazon.com.br', 'https://mercadolivre.com.br'),

(1,'Equilíbrio','equilibrio',
'Um aroma suave, acolhedor e sofisticado. A doçura da vanilla se encontra com flores brancas e madeiras claras, criando harmonia no cômodo — sem peso, sem pressa.',
'Aroma vanilla. Harmonia quieta.',
89.90, NULL, 40, 'ELO-RF-EQU', '120 ml',
'Bergamota, flor de laranjeira',
'Vanilla, jasmim, flor de algodão',
'Musk, âmbar, sândalo',
'Spray de ambiente 120 ml | 4,05 fl oz. Vidro, atomizador dourado.',
'#9BB1C9','Vanilla',
'O verdadeiro equilíbrio não está na ausência do caos, mas na paz que escolhemos cultivar.',
'Coleção Refúgio',
'Borrife no ambiente, a cerca de 20 cm de distância, para criar uma atmosfera acolhedora. Evite aplicar diretamente sobre pessoas, animais e superfícies delicadas.',
'Manter fora do alcance de crianças. Inflamável. Evite contato com os olhos. Armazenar em local fresco, longe de fontes de calor.',
1, 0, 'ativo', 'carrinho', 'https://collshp.com/geovanaferreira030789?view=storefront', 'https://amazon.com.br', 'https://mercadolivre.com.br'),

(1,'Silêncio','silencio',
'Um aroma que traz quietude e profundidade, conectando você ao que realmente importa. As notas amadeiradas e resinosas do âmbar criam uma atmosfera acolhedora, perfeita para pausa, reflexão e presença.',
'Aroma âmbar. Quietude que permanece.',
89.90, NULL, 40, 'ELO-RF-SIL', '120 ml',
'Bergamota, tangerina, folhas verdes',
'Flor de algodão, jasmim, ylang ylang',
'Âmbar, sândalo, musk',
'Spray de ambiente 120 ml | 4,05 fl oz. Vidro, atomizador dourado.',
'#C68E71','Âmbar',
'No silêncio, a alma descansa e o coração se lembra do que realmente importa.',
'Coleção Refúgio',
'Borrife no ambiente, a cerca de 20 cm de distância, para criar uma atmosfera acolhedora. Evite aplicar diretamente sobre pessoas, animais e superfícies delicadas.',
'Manter fora do alcance de crianças. Inflamável. Evite contato com os olhos. Armazenar em local fresco, longe de fontes de calor.',
1, 0, 'ativo', 'carrinho', 'https://collshp.com/geovanaferreira030789?view=storefront', 'https://amazon.com.br', 'https://mercadolivre.com.br'),

(2,'Elo','elo',
'Os primeiros capítulos da vida têm um aroma que o coração nunca esquece. Lavanda, camomila e algodão para um cômodo que precisa ser ninho — suave, limpo, seguro.',
'Coleção Elo. Home spray 120 ml.',
89.90, NULL, 24, 'ELO-ELO-001', '120 ml',
'Lavanda, camomila, bergamota',
'Algodão, jasmim, rosa branca',
'Musk, vanilla, sândalo',
'Home spray 120 ml. Sem testes em animais. Fragrância premium. Álcool, água, fragrância e conservante.',
'#C9A24B','Algodão e camomila',
'Os primeiros capítulos da vida têm um aroma que o coração nunca esquece.',
'Coleção Elo',
'Borrife no ar ou em tecidos a cerca de 20 cm de distância.',
'Manter fora do alcance de crianças. Inflamável. Evite contato com os olhos.',
1, 0, 'ativo', 'carrinho', 'https://collshp.com/geovanaferreira030789?view=storefront', 'https://amazon.com.br', 'https://mercadolivre.com.br'),

(5,'O Ritual das Essências','o-ritual-das-essencias',
'Um curso íntimo com a Geo: olfato, composição de ambiente e o hábito de consagrar o espaço. Seis módulos gravados, caderno de práticas e um encontro ao vivo por turma.',
'Formação com a Geo. Acesso por 12 meses.',
497.00, NULL, 999, 'ELO-CURSO', 'Acesso 12 meses',
NULL, NULL, NULL, NULL,
'#1B4332', NULL, NULL,
'Formação',
NULL, NULL,
0, 0, 'ativo', 'carrinho', NULL, NULL, NULL);

INSERT INTO `imagens_produto` (`produto_id`, `caminho`, `alt`, `ordem`) VALUES
(1, 'images/frasco-despertar.webp', 'Despertar — spray manga verde', 1),
(1, 'images/ficha-despertar.webp', 'Despertar — ficha', 2),
(2, 'images/frasco-recomeco.webp', 'Recomeço — spray figo', 1),
(2, 'images/ficha-recomeco.webp', 'Recomeço — ficha', 2),
(3, 'images/frasco-encontro.webp', 'Encontro — spray ládano', 1),
(3, 'images/ficha-encontro.webp', 'Encontro — ficha', 2),
(4, 'images/frasco-equilibrio.webp', 'Equilíbrio — spray vanilla', 1),
(4, 'images/ficha-equilibrio.webp', 'Equilíbrio — ficha', 2),
(5, 'images/frasco-silencio.webp', 'Silêncio — spray âmbar', 1),
(5, 'images/ficha-silencio.webp', 'Silêncio — ficha', 2),
(6, 'images/frasco-elo.webp', 'Elo — home spray', 1),
(6, 'images/ficha-elo.webp', 'Elo — ficha', 2),
(7, 'images/still-sagrado.webp', 'O Ritual das Essências', 1);

INSERT INTO `depoimentos` (`usuario_id`, `produto_id`, `nome`, `nota`, `texto`, `status`) VALUES
(NULL, 1, 'Marina S.', 5, 'Despertar mudou o tom da manhã. Manga verde, sem doçura de vitrine. A casa acorda comigo.', 'aprovado'),
(NULL, 5, 'Helena R.', 5, 'Silêncio é o spray que eu acendo quando o dia pede pausa. O âmbar fica no linho até a noite.', 'aprovado'),
(NULL, 2, 'Paula V.', 5, 'Recomeço no hall. Visitas perguntam o que mudou. Eu digo: o figo, e um pouco de coragem.', 'aprovado'),
(NULL, 6, 'Camila T.', 5, 'O Elo ficou no quarto do meu filho. Cheiro de ninho, não de produto. A Coleção Elo acertou o silêncio certo.', 'aprovado'),
(NULL, 4, 'Renata M.', 5, 'Equilíbrio é o que eu queria sem saber o nome. Vanilla discreta, quarto em paz.', 'aprovado');

INSERT INTO `cursos` (`titulo`, `slug`, `descricao`, `preco`, `imagem`, `status`) VALUES
('O Ritual das Essências', 'o-ritual-das-essencias',
'Um curso íntimo com a Geo: olfato, composição de ambiente e o hábito de consagrar o espaço. Seis módulos gravados, caderno de práticas e um encontro ao vivo por turma.',
497.00, 'images/colecao-refugio.webp', 'ativo');

INSERT INTO `modulos_curso` (`curso_id`, `titulo`, `descricao`, `ordem`, `duracao`) VALUES
(1, 'O olfato como refúgio', 'Como o cheiro organiza memória, humor e território. Exercício de escuta olfativa.', 1, '42 min'),
(1, 'Notas que habitam', 'Topo, coração e fundo — não como teoria de perfumista, mas como arquitetura de um cômodo.', 2, '38 min'),
(1, 'A casa como altar', 'Rotina de abertura e fechamento do dia com spray, janela e silêncio.', 3, '35 min'),
(1, 'Composição caseira consciente', 'Limites do faça-você-mesma, diluição segura e quando procurar um laboratório.', 4, '47 min'),
(1, 'Pele e tecido', 'Diferença entre essência de corpo e névoa de ambiente. Erros comuns.', 5, '29 min'),
(1, 'O ritual que permanece', 'Como não abandonar o hábito. Caderno de 21 dias e encontro ao vivo.', 6, '40 min');

INSERT INTO `faqs_curso` (`curso_id`, `pergunta`, `resposta`, `ordem`) VALUES
(1, 'Preciso saber de perfumaria?', 'Não. O curso é para quem quer viver melhor com o cheiro — não para formular em escala.', 1),
(1, 'As aulas ficam disponíveis?', 'Sim. Acesso por 12 meses à gravacão e ao caderno em PDF.', 2),
(1, 'Há garantia?', 'Sete dias para desistir, sem pergunta. O refúgio não se impõe.', 3),
(1, 'O material inclui frascos?', 'O curso é de prática e percepção. Kits de matéria-prima são opcionais e vendidos à parte.', 4);

INSERT INTO `configuracoes` (`chave`, `valor`) VALUES
('instagram', 'https://instagram.com/elomiah'),
('marketplace_shopee', 'https://collshp.com/geovanaferreira030789?view=storefront'),
('marketplace_amazon', 'https://amazon.com.br'),
('marketplace_ml', 'https://mercadolivre.com.br'),
('frete_padrao', '18.90'),
('frete_nome', 'Despacho do ateliê'),
('frete_prazo', 'Sai em até 3 dias úteis · Correios, 5 a 12 dias no destino');

INSERT INTO `enderecos` (`usuario_id`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`) VALUES
(2, '01310100', 'Avenida Paulista', '1000', 'Apto 12', 'Bela Vista', 'São Paulo', 'SP');

INSERT INTO `pedidos` (`usuario_id`, `endereco_id`, `codigo`, `status`, `subtotal`, `frete`, `desconto`, `total`, `metodo_pagamento`, `nome_cliente`, `email_cliente`, `telefone_cliente`, `created_at`) VALUES
(2, 1, 'ELO-24001', 'entregue', 89.90, 18.90, 0, 108.80, 'pix', 'Ana Clara Mendes', 'ana@example.com', '11988887777', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(2, 1, 'ELO-24002', 'pago', 89.90, 18.90, 0, 108.80, 'pix', 'Ana Clara Mendes', 'ana@example.com', '11988887777', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(NULL, 1, 'ELO-24003', 'pendente', 89.90, 18.90, 0, 108.80, 'pix', 'Helena Rocha', 'helena@example.com', '11977776666', NOW()),
(2, 1, 'ELO-24004', 'enviado', 89.90, 0, 0, 89.90, 'pix', 'Ana Clara Mendes', 'ana@example.com', '11988887777', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `itens_pedido` (`pedido_id`, `produto_id`, `nome_produto`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
(1, 1, 'Despertar', 1, 89.90, 89.90),
(2, 2, 'Recomeço', 1, 89.90, 89.90),
(3, 5, 'Silêncio', 1, 89.90, 89.90),
(4, 6, 'Elo', 1, 89.90, 89.90);

INSERT INTO `consentimentos_lgpd` (`usuario_id`, `email`, `tipo`, `aceito`, `ip`) VALUES
(2, 'ana@example.com', 'privacidade_cadastro', 1, '127.0.0.1'),
(2, 'ana@example.com', 'cookies_essenciais', 1, '127.0.0.1');
