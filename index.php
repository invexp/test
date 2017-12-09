<?php

$config = array(
    'host' => getenv(strtoupper(getenv("DATABASE_SERVICE_NAME"))."_SERVICE_HOST"),
    'port' => getenv(strtoupper(getenv("DATABASE_SERVICE_NAME"))."_SERVICE_PORT"),
    'user' => getenv("DATABASE_USER"),
    'pass' => getenv("DATABASE_PASSWORD"),
    'db_name' => getenv("DATABASE_NAME"),
    'table' => 'visited',
);

class App
{
    private $c;
    private $db;

    public function __construct($config)
    {
        $this->c = $config;
        $this->connect();
    }

    public function run()
    {
        if (isset($_GET['id'])) {

            $isVisited = false;
            $q = sprintf("select id from " . $this->c['table'] . " where id = '%s'", $_GET['id']);
            $rows = $this->db->query($q);
            if ($rows) {
                $row = $rows->fetch_assoc();
                $isVisited = !empty($row['id']);
            }
            echo json_encode(array('isVisited' => $isVisited));

        } else if (isset($_GET['all'])) {

            $result = array();
            $q = "select id from " . $this->c['table'] . " order by date_entered";
            $rows = $this->db->query($q);
            while ($rows && $row = $rows->fetch_assoc()) {
                $result[] = $row['id'];
            }
            echo join(',', $result);

        } else if (isset($_POST['id'])) {

            $q = sprintf(
                "insert into " . $this->c['table'] . "(id, date_entered) values ('%s', now())",
                trim($_POST['id'])
            );
            $this->db->query($q);

        } else if (isset($_POST['import_data'])) {

            $q = base64_decode($_POST['import_data']);
            $this->db->query($q);

        }
    }

    protected function connect()
    {
        $db = mysqli_connect(
            $this->c['host'],
            $this->c['user'], $this->c['pass'],
            $this->c['db_name'],
            $this->c['port']
        );
        if (!$db) {
            die('Connection failed for some reason');
        } else if ($db->connect_error) {
            die('Connection failed because of: ' . $db->connect_error);
        }

        $this->db = $db;
    }

    public function __destruct()
    {
        if ($this->db) {
            mysqli_close($this->db);
        }
    }
}

$app = new App($config);
$app->run();
