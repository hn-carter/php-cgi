<!DOCTYPE html>
<html>
<head>
    <title>SQLite3の操作(PDO)</title>
    <style>
        table {border-collapse: collapse; }
        table, th, td { border: 1px #000 solid; }
    </style>
</head>
<body>
    <h1>PDOでSQLite3を操作</h1>

    <h2>1.データベース作成</h2>
    <?php
        // SQLite3データベースファイル名
        $dbname = '../db/database.db';
        try {
            // データベースを操作するオプションを設定
            // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            // エラー時に例外を投げる
            // これによってif文で異常を判定する必要がなくなります
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];
            
            // データベースを開く
            // データベースファイルが存在しない場合は新規に作成されます
            // PDO::__construct データベースを操作する PDO インスタンスを生成する 
            $pdo = new PDO('sqlite:'.$dbname, null, null, $options);
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
        try {
            // PDO::exec SQLを実行し、変更があった行数が返ります
            $createRet = $pdo->exec($createTable);
            echo 'テーブルを作成しました。';
        } catch (Exception $e) {
            echo 'テーブル作成に失敗しました。';
            echo $e->getMessage();
            // PDO::errorCode  直近の操作に関連する SQLSTATE を取得します
            echo "<br>PDO::errorCode(): ", $pdo->errorCode();
            // PDO::errorInfo 直近で操作に失敗した拡張エラー情報を取得します
            echo '<pre>';
            print_r($pdo->errorInfo());
            echo '</pre>';
        }
    ?>
    <h2>3.データの追加</h2>
    <?php
        // 直接SQLを実行する場合
        $insSQL1 = "INSERT INTO test_table(id, name, memo, created_at, updated_at) VALUES (1, 'Apple', '赤いりんご🍎とgreen apple🍏', DATETIME('now'), DATETIME('now'));";
        // insert SQLの実行
        $insRet1 = $pdo->exec($insSQL1);
        if ($insRet1) {
            // PDO::exec 関数は直近のSQL文で変更された行の数を返します
            echo '追加された行の数は '.$insRet1.' です。<br>';
        }

        // プリペアドステートメントを使用する場合
        $insSQL2 = "INSERT INTO test_table(id, name, memo, created_at, updated_at) VALUES (:id, :name, :memo, DATETIME('now'), DATETIME('now'));";
        // PDO::prepare 実行するSQL文を準備します
        if ($insStmt = $pdo->prepare($insSQL2)) {
            // SQLパラメータに変数をバインド
            // PDOStatement::bindParam パラメータを変数にバインドします
            $insStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $insStmt->bindParam(':name', $name, PDO::PARAM_STR);
            $insStmt->bindParam(':memo', $memo, PDO::PARAM_STR);
            // 変数に登録値をセット
            $id = 2;
            $name = 'Banana';
            $memo = 'バナナ、<b>🍌</b>';
            // PDOStatement::execute プリペアドステートメントを実行します
            // 成功した場合 true が返ります
            if ($insStmt->execute()) {
                echo 'id : '.$id.' を追加しました。<br>';
            }
            // 各変数に別の値をセットして同じSQLを実行
            $id = 3;
            $name = 'Cherry';
            $memo = 'さくらんぼ、<i>🍒</i>';
            $insRet3 = $insStmt->execute();
            // 各変数に別の値をセットして同じSQLを実行
            $id = 4;
            $name = 'Durian';
            $memo = null;
            $insRet4 = $insStmt->execute();
        }
        $insStmt = null;
    ?>

    <h2>4.1項目のデータ取得</h2>
    <?php
        // 取得結果が1つの場合
        // PDO::query クエリを実行し、結果(PDOStatement|false)を返します
        $countRet = $pdo->query('SELECT count(id) FROM test_table');
        if ($countRet) {
            // PDOStatement::fetchColumn 結果セットから一つの結果を返します
            $result = $countRet->fetchColumn();
            echo 'テーブルのレコード数は '.$result.' です。';
        }
        $countRet = null;
    ?>

    <h2>5.複数件のデータ取得 (SQLをそのまま実行する場合)</h2>
    <table>
        <tr><th>id</th><th>name</th><th>memo</th><th>created_at</th><th>updated_at</th></tr>
        <?php
            $selSql = 'SELECT id, name, memo, created_at, updated_at FROM test_table';
            // PDO::query SQLを実行して結果をPDOStatementオブジェクトで受け取ります
            $selRet = $pdo->query($selSql);
            // 結果のPDOStatementオブジェクトから全体を配列で受け取ります
            $rows = $selRet->fetchAll();
            foreach ($rows as $row) {
                // 結果を行毎に処理
                $selRow = '<tr>';
                $selRow .= '<td>'.htmlspecialchars($row['id']).'</td>';
                $selRow .= '<td>'.htmlspecialchars($row['name']).'</td>';
                $selRow .= '<td>'.htmlspecialchars($row['memo']).'</td>';
                $selRow .= '<td>'.htmlspecialchars($row['created_at']).'</td>';
                $selRow .= '<td>'.htmlspecialchars($row['updated_at']).'</td></tr>';
                echo $selRow;
            }
            $selRet = null;
        ?>
    </table>

    <h2>6.複数件のデータ取得 (SQLに値をバインドする場合)</h2>
    <?php
        // プリペアドステートメントを使用する場合
        if($selStmt = $pdo->prepare('SELECT id, name FROM test_table WHERE id <= :id'))
        {
            // 検索条件の変数:idに値をバインド
            // PDOStatement::bindValue 値をパラメータにバインドします
            $selStmt->bindValue(':id', 2, PDO::PARAM_INT);
            // プリペアドステートメントを実行し、結果をPDOStatementオブジェクトで受け取ります
            $sel2Ret = $selStmt->execute();
            // カラム名を変数にバインドします
            $selStmt->bindColumn('id', $sel2_id);
            $selStmt->bindColumn('name', $sel2_name);
            // 取得データを表示します
            echo '<ul>';
            while ($selStmt->fetch(PDO::FETCH_BOUND)) {
                echo '<li>';
                // バインドした変数にベースから取得した値がセットされます
                echo 'id : '.$sel2_id.' - name : '.$sel2_name;
                echo '</li>';
            }
            echo '</ul>';
        }
    ?>

    <h2>7.データベースから切断</h2>
    <?php
        // PDO オブジェクトが存在する間、接続し続けるのでnullを代入して破棄します
        $pdo = null;
        echo 'SQLite3データベースへの接続を切断しました。';
    ?>
    
    <h2>8.トランザクション処理</h2>
    <?php
        try {
            $tpdo = new PDO('sqlite:../db/transaction.db');
        } catch(Exception $e) {
            echo $e->getMessage();
            goto owari;
        }
        try {
            // 例外のスローを有効にします
            $tpdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // PDO::beginTransaction トランザクションを開始します
            $tpdo->beginTransaction();
            // テーブルを作成します
            $tpdo->exec('CREATE TABLE temptb (name TEXT PRIMARY KEY)');
            // データを追加します
            $stmt = $tpdo->prepare('INSERT INTO temptb(name) VALUES (:name)');
            $stmt->bindValue(':name', 'firstname', PDO::PARAM_STR);
            $stmt->execute();
            $stmt->bindValue(':name', 'secondname', PDO::PARAM_STR);
            $stmt->execute();
            // コミット　処理を確定します
            $tpdo->commit();
            echo 'トランザクションをコミットしました。';
        } catch(Exception $e) {
            // ロールバック　処理の取り消しをします
            $tpdo->rollBack();
            // 例外内容を表示します
            echo '<pre>';
            print_r($e);
            echo '</pre>';
        } finally {
            // データベースを閉じます
            $stmt = null;
            $tpdo = null;
        }
        owari:
    ?>
</body>
</html>
