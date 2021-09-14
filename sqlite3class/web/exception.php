<!DOCTYPE html>
<html>
<head>
    <title>SQLite3の操作</title>
</head>
<body>
    <h1>SQLite3 例外</h1>

    <h2>例外を無効にした場合</h2>
    <?php
        $edb = new SQLite3(':memory:');
        try {
            // 例外のスローを無効にする
            $edb->enableExceptions(false);
            // エラーのあるSQLを実行する
            $edb->exec('CREATE TABLE disable');
        } catch(Exception $e) {
            // 例外内容の表示
            echo $e->getMessage();
        } finally {
            // データベースを閉じる
            $edb->close();
        }
    ?>

    <h2>例外を有効にした場合</h2>
    <?php
        $tdb = new SQLite3(':memory:');
        try {
            // 例外のスローを有効にする
            $tdb->enableExceptions(true);
            // エラーのあるSQLを実行する
            $tdb->exec('CREATE TABLE enable');
        } catch(Exception $e) {
            // 例外内容の表示
            echo $e->getMessage();
        } finally {
            // データベースを閉じる
            $tdb->close();
        }
    ?>
</body>
</html>
