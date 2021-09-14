<!DOCTYPE html>
<html>
<head>
    <title>SQLite3の操作</title>
    <style>
        table {border-collapse: collapse; }
        table, th, td { border: 1px #000 solid; }
    </style>
</head>
<body>
    <h1>SQLite3 クラスを使った操作</h1>

    <h2>1.データベース作成</h2>
    <?php
        // SQLite3データベースファイル名
        $dbname = '../db/database.db';
        try {
            // データベースを開く
            // データベースファイルが存在しない場合は新規に作成されます
            // SQLite3::__construct SQLite3オブジェクトを作成し、オープンします
            $db = new SQLite3($dbname);
            echo 'データベース'.$dbname.'を作成・オープンしました。';
        } catch (Exception $e) {
            echo 'データベース'.$dbname.'の作成・オープンに失敗しました。';
            echo $e->getMessage();
        }
    ?>
    
    <h2>2.テーブル作成</h2>
    <?php
        // テーブルを作成するSQL
        $createTable = 'CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            memo TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
            )';
        // SQLite3::exec 結果を返さないクエリを実行します
        $createRet = $db->exec($createTable);
        if ($createRet) {
            echo 'テーブルを作成しました。';
        } else {
            // SQLite3::lastErrorMsg 直近で失敗した SQLite リクエストについての英文テキストの説明
            echo 'テーブル作成に失敗しました。: '.$db->lastErrorMsg();
        }
    ?>

    <h2>3.データの追加</h2>
    <?php
        // 直接SQLを実行する場合
        $insSQL1 = "INSERT INTO test_table(id, name, memo, created_at, updated_at) VALUES (1, 'Apple', '赤いりんご🍎とgreen apple🍏', DATETIME('now'), DATETIME('now'));";
        // insert SQLの実行
        $insRet1 = $db->exec($insSQL1);
        if ($insRet1) {
            // SQLite3::changes 関数で直近のSQL文で変更された行の数を取得できます
            echo '追加された行の数は '.$db->changes().' です。<br>';
        }

        // プリペアドステートメントを使用する場合
        $insSQL2 = "INSERT INTO test_table(id, name, memo, created_at, updated_at) VALUES (:id, :name, :memo, DATETIME('now'), DATETIME('now'));";
        // SQLite3::prepare 実行するSQL文を準備します
        if ($insStmt = $db->prepare($insSQL2)) {
            // SQLパラメータに変数をバインド
            // SQLite3Stmt::bindParam パラメータを変数にバインドします
            $insStmt->bindParam(':id', $id, SQLITE3_INTEGER);
            $insStmt->bindParam(':name', $name);
            $insStmt->bindParam(':memo', $memo);
            // 変数に登録値をセット
            $id = 2;
            $name = 'Banana';
            $memo = 'バナナ、<b>🍌</b>';
            // SQLite3Stmt::execute プリペアドステートメントを実行し、結果セットオブジェクトを返します
            if ($insRet2 = $insStmt->execute()) {
                echo 'id : '.$id.' を追加しました。<br>';
            }
            // 各変数に別の値をセットして同じSQLを実行
            $id = 3;
            $name = 'Cherry';
            $memo = 'さくらんぼ、<i>🍒</i>';
            $insRet3 = $insStmt->execute();
        }
    ?>

    <h2>4.1項目のデータ取得</h2>
    <?php
        // 取得結果が1つの場合
        // SQLite3::querySingle 一つの結果を返すクエリを実行します
        // $countRet 変数にはtest_tableのレコード数である int(3) がセットされます
        $countRet = $db->querySingle('SELECT count(id) FROM test_table');
        if ($countRet) {
            echo 'テーブルのレコード数は '.$countRet.' です。';
        }
    ?>

    <h2>5.複数件のデータ取得 (SQLをそのまま実行する場合)</h2>
    <table>
        <tr><th>id</th><th>name</th><th>memo</th><th>created_at</th><th>updated_at</th></tr>
        <?php
            $selSql = 'SELECT id, name, memo, created_at, updated_at FROM test_table';
            // SQLite3::query SQLを実行して結果をSQLite3Resultオブジェクトで受け取ります
            $selRet = $db->query($selSql);
            $sel1List = array();
            if ($selRet != false) {
                // 結果を行毎に処理
                $selRow = '<tr>';
                while ($row = $selRet->fetchArray(SQLITE3_ASSOC)) {
                    $selRow .= '<td>'.htmlspecialchars($row['id']).'</td>';
                    $selRow .= '<td>'.htmlspecialchars($row['name']).'</td>';
                    $selRow .= '<td>'.htmlspecialchars($row['memo']).'</td>';
                    $selRow .= '<td>'.htmlspecialchars($row['created_at']).'</td>';
                    $selRow .= '<td>'.htmlspecialchars($row['updated_at']).'</td></tr>';
                }
                $sel1List[] = $selRow;
            }
            foreach ($sel1List as $r1) {
                echo $r1;
            }
        ?>
    </table>

    <h2>6.複数件のデータ取得 (SQLに値をバインドする場合)</h2>
    <?php
        // プリペアドステートメントを使用する場合
        if($selStmt = $db->prepare('SELECT id, name FROM test_table WHERE id <= :id'))
        {
            // 検索条件の変数:idに値をバインド
            // SQLite3Stmt::bindValue パラメータの値を変数にバインドします
            $selStmt->bindValue(':id', 2, SQLITE3_INTEGER);
            // プリペアドステートメントを実行し、結果をSQLite3Resultオブジェクトで受け取り
            $sel2Ret = $selStmt->execute();
            $sel2Names = array();
            while ($arr = $sel2Ret->fetchArray(SQLITE3_ASSOC)) {
                $sel2Names[$arr['id']] = $arr['name'];
            }
            // 取得データを表示
            echo '<ul>';
            foreach($sel2Names as $key => $value) {
                echo '<li>';
                echo 'id : '.$key.' - name : '.$value;
                echo '</li>';
            }
            echo '</ul>';
        }
    ?>

    <h2>7.データベースから切断</h2>
    <?php
        // SQLite3::close データベースとの接続を閉じます
        if ($db->close()) {
            echo 'SQLite3データベースへの接続を切断しました。';
        }
    ?>
    
    <h2>8.トランザクション</h2>
    <?php
        try {
            $tdb = new SQLite3('../db/transaction.db');
        } catch(Exception $e) {
            echo $e->getMessage();
            goto owari;
        }
        // トランザクションの開始
        $tdb->exec('BEGIN');
        try {
            // 例外のスローを有効にする
            $prev = $tdb->enableExceptions(true);
            // テーブル作成
            $tdb->exec('CREATE TABLE temptb (name TEXT PRIMARY KEY)');
            // データ追加
            $stmt = $tdb->prepare('INSERT INTO temptb(name) VALUES (:name)');
            $stmt->bindValue(':name', 'firstname', SQLITE3_TEXT);
            $stmt->execute();
            $stmt->bindValue(':name', 'secondname', SQLITE3_TEXT);
            $stmt->execute();
            // コミット　処理の確定
            $tdb->exec('COMMIT');
            echo 'トランザクションをコミットしました。';
        } catch(Exception $e) {
            // ロールバック　処理の取り消し
            $tdb->exec('ROLLBACK');
            // 例外内容の表示
            echo '<pre>';
            print_r($e);
            echo '</pre>';
        } finally {
            // データベースを閉じる
            $tdb->close();
        }
        owari:
    ?>
</body>
</html>
