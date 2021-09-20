<!DOCTYPE html>
<html>
<head>
    <title>SQLite3の例外</title>
</head>
<body>
    <h1>SQLite3 例外(PDO)</h1>

    <h2>例外を有効にしない場合</h2>
    <?php
        try {
            $pdo = new PDO('sqlite::memory:');
            // エラーのあるSQLを実行する
            $pdo->exec('CREATE TABLE disable');
        } catch(Exception $e) {
            // 例外内容の表示
            echo $e->getMessage();
        } finally {
            // データベースを閉じる
            $pdo = null;
        }
    ?>

    <h2>例外を有効にした場合</h2>
    <?php
        try {
            $pdoe = new PDO('sqlite::memory:', null, null, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            // エラーのあるSQLを実行する
            $pdoe->exec('CREATE TABLE enable');
        } catch(Exception $e) {
            // 例外内容の表示
            echo $e->getMessage();
        } finally {
            // データベースを閉じる
            $pdoe = null;
        }
    ?>
</body>
</html>
