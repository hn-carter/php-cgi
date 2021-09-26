<?php
/**
 * SQLite3カウンターデータベース
 */
class CCounter {
    /**
     * データベースのインスタンス
     * 
     * @var PDO
     */
    private $db;

    /**
     * データベースファイル
     * 
     * @var string
     */
    private $db_file;

    /**
     * コンストラクタ
     * 
     * @param string $dbfile SQLite3データベースファイル
     */
    public function __construct($dbfile = 'counter.db') {
        $this->db = null;
        $this->db_file = $dbfile;
    }

    /**
     * データベースに接続する
     */
    public function connectDb() {
        // データベースファイルの存在確認
        $exist = file_exists($this->db_file);
        // PDOで接続する
        $this->db = new PDO('sqlite:'.$this->db_file, null, null, 
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        // データベースを新規作成した場合初期設定する
        if ($exist === false) {
            $this->createTable();
        }
    }

    /**
     * データベースから切断する
     */
    public function closeDb() {
        $this->db = null;
    }

    /**
     * カウントアップしカウント数を返す
     * 
     * @return int カウント数
     */
    public function countUp() {
        // カウンタ取得SQL
        $counter_get = "SELECT counter FROM counter_tbl;";
        // カウントアップSQL
        $counter_up = "UPDATE counter_tbl SET
            counter = counter + 1,
            updated_at =  DATETIME('now');";
        // カウント数
        $count = 0;
        if ($this->db != null) {
            try {
                // カウントアップ
                $this->db->exec($counter_up);
                // カウンタ取得
                $counter_ret = $this->db->query($counter_get);
                $count = $counter_ret->fetchColumn();
            } catch (Exception $e) {
                echo '['.__LINE__.'] Exception : '.$e->getMessage();
                print_r($this->db->errorInfo());
            }
        }
        return $count;
    }

    /**
     * 新規データベースの初期設定
     */
    private function createTable() {
        // カウンタテーブル作成SQL
        $counter_table = "CREATE TABLE counter_tbl (
            counter INTEGER,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
            );";
        // カウンタテーブル初期化SQL
        $counter_init = "INSERT INTO counter_tbl (
            counter, created_at, updated_at
            ) VALUES (
            0, DATETIME('now'), DATETIME('now'));";

        try {
            // テーブルを作成しカウンタの初期値を設定
            $this->db->exec($counter_table);
            $this->db->exec($counter_init);
        } catch (Exception $e) {
            echo '['.__LINE__.'] Exception : '.$e->getMessage();
            print_r($this->db->errorInfo());
        }
    }
}