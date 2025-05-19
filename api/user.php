<?php
require __DIR__ . '/../config/db.php';

$db = new PDO('sqlite:' . $config['root_dir'] . '/data/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Return all users
        $stmt = $db->query('SELECT id, username FROM users');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users );
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            parse_str(file_get_contents('php://input'), $input);
        }

        if (isset($input['username']) && isset($input['password'])) {
            // Authenticate user and return their cows + vaccines
            $stmt = $db->prepare('SELECT id FROM users WHERE username = ? AND password = ?');
            $stmt->execute([$input['username'], $input['password']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $stmt = $db->prepare('
                    SELECT cows.cow_name, vaccines.vaccine_name, vaccines.day, vaccines.month
                    FROM cows
                    LEFT JOIN vaccines ON cows.id = vaccines.cow_id
                    WHERE cows.user_id = ?
                ');
                $stmt->execute([$user['id']]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($results);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid username or password']);
            }
        } elseif (isset($input['username']) && isset($input['cow'])) {
            // Get vaccines for a specific cow for a specific user
            $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$input['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $stmt = $db->prepare('
                    SELECT vaccines.vaccine_name, vaccines.day, vaccines.month
                    FROM cows
                    LEFT JOIN vaccines ON cows.id = vaccines.cow_id
                    WHERE cows.user_id = ? AND cows.cow_name = ?
                ');
                $stmt->execute([$user['id'], $input['cow']]);
                $vaccines = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($vaccines);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
