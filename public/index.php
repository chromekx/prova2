<?php
require __DIR__ . "/../vendor/autoload.php";

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$usuarios = [
    ["id" => 1, "nome" => "Victor", "email" => "victor@gmail.com", "login" => "chromekx", "senha" => "1234"],
    ["id" => 2, "nome" => "Davi", "email" => "davi@gmail.com", "login" => "PeopleEater", "senha" => "5678"],
    ["id" => 3, "nome" => "Luyza", "email" => "luyza@gmail.com", "login" => "betedabliu", "senha" => "8901"],
    ["id" => 4, "nome" => "Felipe", "email" => "felipe@gmail.com", "login" => "Parasintetico", "senha" => "2345"],
    ["id" => 5, "nome" => "Carlos", "email" => "carlos@gmail.com", "login" => "cazinn67", "senha" => "6789"],
];

$app->get('/status', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/usuarios', function ($request, $response, $args) use (&$usuarios) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
    $email = $queryParams['email'] ?? null;
    $login = $queryParams['login'] ?? null;
    $senha = $queryParams['senha'] ?? null;
    if ($nome) {
        $filtrados = array_filter($usuarios, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
        $response->getBody()->write(json_encode($filtrados));
    } else if ($email) {
        $filtrados = array_filter($usuarios, fn($item) => str_contains(mb_strtolower($item['email']), mb_strtolower($email)));
        $response->getBody()->write(json_encode($filtrados));
    } else if ($login) {
        $filtrados = array_filter($usuarios, fn($item) => str_contains(mb_strtolower($item['login']), mb_strtolower($login)));
        $response->getBody()->write(json_encode($filtrados));
    } else if ($senha) {
        $filtrados = array_filter($usuarios, fn($item) => str_contains(mb_strtolower($item['senha']), mb_strtolower($senha)));
        $response->getBody()->write(json_encode($filtrados));
    } else {
        $response->getBody()->write(json_encode($usuarios));
    }
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/usuario/{id}', function ($request, $response, $args) use (&$usuarios) {
    $id = (int) $args['id'];
    $usuario = current(array_filter($usuarios, fn($p) => $p['id'] === $id));

    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário nao encontrado']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $response->getBody()->write(json_encode($usuario));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/usuarios', function ($request, $response) use (&$usuarios) {
    $dados = $request->getParsedBody();

    if (isset($dados['id']) || !empty($dados['id'])) {
        $response->getBody()->write(json_encode(['erro' => 'O id não pode estar preenchido.']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
    if (
        !isset($dados['nome'])  || empty($dados['nome']) ||
        !isset($dados['email']) || empty($dados['email']) ||
        !isset($dados['login']) || empty($dados['login']) ||
        !isset($dados['senha']) || empty($dados['senha'])
    ) {
        $response->getBody()->write(json_encode(['erro' => 'Todos os campos são obrigatórios.']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
    $novoUsuario = [
        'id' => count($usuarios) + 1,
        'nome' => $dados['nome'],
        'email' => $dados['email'],
        'login' => $dados['login'],
        'senha' => $dados['senha']
    ];

    $usuarios[] = $novoUsuario;
    $response->getBody()->write(json_encode($novoUsuario));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $dados = $request->getParsedBody();

    foreach ($usuarios as &$usuario) {
        if ($usuario['id'] === (int) $args['id']) {
            if (empty($dados['nome']) || empty($dados['email']) || empty($dados['login']) || empty($dados['senha'])) {
                $response->getBody()->write(json_encode(['erro' => 'Complete todos os campos.']));

                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            } else {
                $usuario['nome'] = $dados['nome'];
                $usuario['email'] = $dados['email'];
                $usuario['login'] = $dados['login'];
                $usuario['senha'] = $dados['senha'];
                $response->getBody()->write(json_encode($usuario));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
        }
    }
    return $response->withStatus(404);
});

$app->delete('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $usuarios = array_values(array_filter($usuarios, fn($p) => $p['id'] !== (int) $args['id']));
    return $response->withStatus(204);
});

$app->run();
