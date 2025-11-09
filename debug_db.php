<?php
require_once 'vendor/autoload.php'; 

// Load environment variables - FIXED PATH
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new App\Kernel('dev', true); 
$kernel->boot(); 
$connection = $kernel->getContainer()->get('doctrine')->getConnection(); 

echo 'Connected to database: ' . $connection->getDatabase() . PHP_EOL;
echo 'Connection params: ' . PHP_EOL;
print_r($connection->getParams());
echo PHP_EOL;

try {
    $result = $connection->executeQuery('SELECT DATABASE() as db')->fetchAssociative();
    echo 'Current database: ' . ($result['db'] ?? 'NULL') . PHP_EOL;
    
    $tables = $connection->executeQuery('SHOW TABLES')->fetchAllAssociative(); 
    echo 'Tables found: ' . count($tables) . PHP_EOL; 
    
    if (count($tables) > 0) {
        foreach ($tables as $table) { 
            print_r($table); 
        }
    } else {
        echo 'No tables found. Let me check if we are in the right database...' . PHP_EOL;
        $allTables = $connection->executeQuery('SHOW TABLES FROM wshop_api')->fetchAllAssociative();
        echo 'Tables in wshop_api: ' . count($allTables) . PHP_EOL;
        foreach ($allTables as $table) {
            print_r($table);
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}