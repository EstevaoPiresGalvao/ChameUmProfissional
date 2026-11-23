<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Lê payload JSON quando enviado via POST
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'session':
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id, tipo_conta, nome, email, idade, cpf, cnpj, telefone, endereco, area_atuacao, modalidade FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user) {
                sendJson(['success' => true, 'authenticated' => true, 'user' => $user]);
            }
        }
        sendJson(['success' => true, 'authenticated' => false]);
        break;

    case 'register':
        if ($method !== 'POST') sendJson(['success' => false, 'message' => 'Método inválido'], 405);

        $tipo = trim($input['tipo_conta'] ?? '');
        $nome = trim($input['nome'] ?? '');
        $email = trim($input['email'] ?? '');
        $senha = $input['senha'] ?? '';
        $telefone = trim($input['telefone'] ?? '');
        $endereco = trim($input['endereco'] ?? '');

        $tiposPermitidos = ['cliente', 'profissional', 'empresa', 'prestador'];
        if (!in_array($tipo, $tiposPermitidos, true)) {
            sendJson(['success' => false, 'message' => 'Tipo de conta inválido.'], 400);
        }

        if (empty($nome) || empty($email) || empty($senha) || empty($telefone) || empty($endereco)) {
            sendJson(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJson(['success' => false, 'message' => 'E-mail inválido.'], 400);
        }

        // Verifica se e-mail já existe
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            sendJson(['success' => false, 'message' => 'Este e-mail já está cadastrado.'], 409);
        }

        $idade = null;
        $cpf = null;
        $cnpj = null;
        $areaAtuacao = null;
        $modalidade = null;

        if ($tipo === 'cliente') {
            $idade = filter_var($input['idade'] ?? null, FILTER_VALIDATE_INT);
            if (!$idade || $idade < 1 || $idade > 130) {
                sendJson(['success' => false, 'message' => 'Informe uma idade válida.'], 400);
            }
        } else {
            // Profissional, Empresa ou Prestador exigem área de atuação e modalidade
            $areaAtuacao = trim($input['area_atuacao'] ?? '');
            $modalidade = trim($input['modalidade'] ?? '');

            if (empty($areaAtuacao)) {
                sendJson(['success' => false, 'message' => 'Informe a área de atuação.'], 400);
            }

            // REGRA: Não é permitido uso de acentos e letras maiúsculas na área de atuação
            if (preg_match('/[A-ZÀ-Ÿ]/u', $areaAtuacao)) {
                sendJson([
                    'success' => false,
                    'message' => 'A área de atuação não pode conter letras maiúsculas ou acentos.'
                ], 400);
            }

            if (!in_array($modalidade, ['remoto', 'presencial', 'hibrido'], true)) {
                sendJson(['success' => false, 'message' => 'Especifique o serviço: remoto, presencial ou híbrido.'], 400);
            }

            if ($tipo === 'empresa') {
                $cnpj = trim($input['cnpj'] ?? '');
                if (empty($cnpj)) {
                    sendJson(['success' => false, 'message' => 'Informe o CNPJ da empresa.'], 400);
                }
            } else {
                // Profissional ou Prestador exige CPF
                $cpf = trim($input['cpf'] ?? '');
                if (empty($cpf)) {
                    sendJson(['success' => false, 'message' => 'Informe o CPF.'], 400);
                }
            }
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO usuarios (tipo_conta, nome, email, senha, idade, cpf, cnpj, telefone, endereco, area_atuacao, modalidade)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tipo, $nome, $email, $senhaHash, $idade, $cpf, $cnpj, $telefone, $endereco, $areaAtuacao, $modalidade
        ]);

        $newId = (int)$pdo->lastInsertId();
        $_SESSION['user_id'] = $newId;

        sendJson([
            'success' => true,
            'message' => 'Conta criada com sucesso!',
            'user' => [
                'id' => $newId,
                'tipo_conta' => $tipo,
                'nome' => $nome,
                'email' => $email,
                'idade' => $idade,
                'cpf' => $cpf,
                'cnpj' => $cnpj,
                'telefone' => $telefone,
                'endereco' => $endereco,
                'area_atuacao' => $areaAtuacao,
                'modalidade' => $modalidade
            ]
        ]);
        break;

    case 'login':
        if ($method !== 'POST') sendJson(['success' => false, 'message' => 'Método inválido'], 405);

        $email = trim($input['email'] ?? '');
        $senha = $input['senha'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($senha, $user['senha'])) {
            sendJson(['success' => false, 'message' => 'E-mail ou senha incorretos.'], 401);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        unset($user['senha']);

        sendJson(['success' => true, 'user' => $user]);
        break;

    case 'logout':
        session_destroy();
        sendJson(['success' => true]);
        break;

    case 'profiles':
        // Lista Profissionais, Empresas e Prestadores (e clientes caso alguém tenha conversado)
        $tipoFiltro = $_GET['tipo'] ?? '';
        $modalidadeFiltro = $_GET['modalidade'] ?? '';
        $busca = trim($_GET['q'] ?? '');

        $sql = "SELECT id, tipo_conta, nome, telefone, endereco, area_atuacao, modalidade 
                FROM usuarios 
                WHERE tipo_conta IN ('profissional', 'empresa', 'prestador')";
        $params = [];

        if (in_array($tipoFiltro, ['profissional', 'empresa', 'prestador'], true)) {
            $sql .= " AND tipo_conta = ?";
            $params[] = $tipoFiltro;
        }

        if (in_array($modalidadeFiltro, ['remoto', 'presencial', 'hibrido'], true)) {
            $sql .= " AND modalidade = ?";
            $params[] = $modalidadeFiltro;
        }

        if ($busca !== '') {
            $sql .= " AND (nome LIKE ? OR area_atuacao LIKE ? OR endereco LIKE ?)";
            $like = "%{$busca}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        sendJson(['success' => true, 'profiles' => $stmt->fetchAll()]);
        break;

    case 'contacts':
        // Retorna usuários com quem o usuário logado já trocou mensagens (útil para clientes verem respostas e profissionais verem clientes)
        if (!isset($_SESSION['user_id'])) {
            sendJson(['success' => false, 'message' => 'Não autenticado.'], 401);
        }
        $myId = (int)$_SESSION['user_id'];

        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.tipo_conta, u.nome, u.area_atuacao, u.modalidade
            FROM usuarios u
            INNER JOIN mensagens m 
                ON (m.remetente_id = u.id AND m.destinatario_id = ?) 
                OR (m.destinatario_id = u.id AND m.remetente_id = ?)
            WHERE u.id != ?
        ");
        $stmt->execute([$myId, $myId, $myId]);
        sendJson(['success' => true, 'contacts' => $stmt->fetchAll()]);
        break;

    case 'messages':
        if (!isset($_SESSION['user_id'])) {
            sendJson(['success' => false, 'message' => 'Faça login para acessar as mensagens.'], 401);
        }
        $myId = (int)$_SESSION['user_id'];

        if ($method === 'GET') {
            $otherId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
            if (!$otherId) {
                sendJson(['success' => false, 'message' => 'Destinatário inválido.'], 400);
            }

            $stmt = $pdo->prepare("
                SELECT id, remetente_id, destinatario_id, conteudo, fuso_horario_emissor, enviado_em
                FROM mensagens
                WHERE (remetente_id = ? AND destinatario_id = ?)
                   OR (remetente_id = ? AND destinatario_id = ?)
                ORDER BY enviado_em ASC, id ASC
            ");
            $stmt->execute([$myId, $otherId, $otherId, $myId]);

            sendJson(['success' => true, 'messages' => $stmt->fetchAll()]);
        } elseif ($method === 'POST') {
            $destinatarioId = filter_var($input['destinatario_id'] ?? null, FILTER_VALIDATE_INT);
            $conteudo = trim($input['conteudo'] ?? '');
            $fusoHorario = trim($input['fuso_horario_emissor'] ?? 'UTC');

            if (!$destinatarioId || empty($conteudo)) {
                sendJson(['success' => false, 'message' => 'Mensagem ou destinatário inválido.'], 400);
            }

            // Valida o fuso horário IANA enviado pelo navegador do emissor
            if (!in_array($fusoHorario, timezone_identifiers_list(), true)) {
                $fusoHorario = 'UTC';
            }

            // Salva timestamp em UTC para conversão exata no fuso horário do emissor
            $enviadoEmUtc = gmdate('Y-m-d H:i:s');

            $stmt = $pdo->prepare("
                INSERT INTO mensagens (remetente_id, destinatario_id, conteudo, fuso_horario_emissor, enviado_em)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$myId, $destinatarioId, $conteudo, $fusoHorario, $enviadoEmUtc]);

            sendJson([
                'success' => true,
                'message' => [
                    'id' => (int)$pdo->lastInsertId(),
                    'remetente_id' => $myId,
                    'destinatario_id' => $destinatarioId,
                    'conteudo' => $conteudo,
                    'fuso_horario_emissor' => $fusoHorario,
                    'enviado_em' => $enviadoEmUtc
                ]
            ]);
        }
        break;

    default:
        sendJson(['success' => false, 'message' => 'Ação não encontrada.'], 404);
}
