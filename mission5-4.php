<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>簡易掲示板</title>
</head>
<body>

<?php
    //DB接続設定
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    // 変数を初期化
    $name = "";
    $comment = "";
    $current_date = date("Y-m-d H:i:s");
    $Edit_number = !empty($_POST["Edit_number"]) ? $_POST["Edit_number"] : null; // 編集番号
    $Del_number = !empty($_POST["Del_number"]) ? $_POST["Del_number"] : null; // 削除番号
    $pass = isset($_POST["pass"]) ? $_POST["pass"] : ""; // 投稿パスワード
    $editpass = isset($_POST["editpass"]) ? $_POST["editpass"] : "";// 編集パスワード
    $delpass = isset($_POST["delpass"]) ? $_POST["delpass"] : "";// 削除パスワード

    // 編集ボタンが押された場合の処理(編集する投稿をフォームに表示)
    if (isset($_POST['Edit'])) {
    
        if (!empty($Edit_number) && !empty($editpass)) {
            
            // 編集対象の投稿番号が存在するか確認(IDとパスワードから確認)
            $sql = 'SELECT * FROM テーブル名 WHERE id = :id AND password =:password';
            $edit1 = $pdo->prepare($sql);
            $edit1->bindParam(':id', $Edit_number, PDO::PARAM_INT);
            $edit1->bindParam(':password', $editpass, PDO::PARAM_STR);
            $edit1->execute();
            $Past_result = $edit1->fetch();
                
                //編集対象の投稿番号とパスワードどちらもtrueだった時
                if ($Past_result) {
                    // 過去の投稿内容をフォームに表示
                    $name = $Past_result['name'];
                    $comment = $Past_result['comment'];
                    $pass = $Past_result['password']; // パスワードも表示(readonlyで変更不可)
                } 
            
                else {  //編集対象の投稿番号かパスワードがfalseだった時
                
                    $sql = 'SELECT * FROM テーブル名 WHERE id = :id ';
                    $edit1_dash = $pdo->prepare($sql);
                    $edit1_dash->bindParam(':id', $Edit_number, PDO::PARAM_INT);
                    $edit1_dash->execute();
                    $Past_result2 = $edit1_dash->fetch();
                        
                        if (!$Past_result2){    //編集対象の投稿番号がなかった場合
                        
                            //編集投稿番号指定・パスワードをリセット
                            $Edit_number = '';
                            $editpass = '';
                        
                            echo "該当する投稿が見つかりませんでした。<br>";
                        }
                    
                        else {   //編集対象の投稿番号はあったが、パスワードが違う場合
                        
                            //編集投稿番号指定・パスワードをリセット
                            $Edit_number = '';
                            $editpass = '';
                            
                            echo "パスワードが間違っています。<br>";
                        }
                }
        
        } elseif(empty($Edit_number) && !empty($editpass)) {    //投稿番号入力なし、パスワード入力あり
            //編集パスワードをリセット
                            $editpass = '';
                            
            echo "編集投稿番号を入力してください。<br>";
            
        } elseif(!empty($Edit_number) && empty($editpass)){     //投稿番号入力あり、パスワード入力なし
            //編集投稿番号指定をリセット
                            $Edit_number = '';
                            
            echo "編集パスワードを入力してください。<br>";
        }
        else{   //どちらも入力なし
            
            echo "編集投稿番号とパスワードを入力してください。<br>";
        }
    }
        
    
    
    // 投稿処理（送信ボタンが押された場合）
    if (isset($_POST['submit'])) {
        $name = isset($_POST["name"]) ? $_POST["name"] : "";
        $comment = isset($_POST["comment"]) ? $_POST["comment"] : "";
    
        if (!empty($name) && !empty($comment) && !empty($pass)) {
            
            if (!empty($Edit_number)) {
                // 編集処理
                $sql = 'UPDATE テーブル名 SET name=:name, comment=:comment, date=:date WHERE id=:id AND password=:password';
                $edit2 = $pdo->prepare($sql);
                $edit2->bindParam(':name', $name, PDO::PARAM_STR);
                $edit2->bindParam(':comment', $comment, PDO::PARAM_STR);
                $edit2->bindParam(':date', $current_date, PDO::PARAM_STR);
                $edit2->bindParam(':id', $Edit_number, PDO::PARAM_INT);
                $edit2->bindParam(':password', $pass, PDO::PARAM_STR);
                $edit2->execute();
                
                echo "投稿番号{$Edit_number}を編集しました<br>";
            } else {
                // 新規投稿処理
                $sql = 'INSERT INTO テーブル名 (name, comment, date, password) VALUES (:name, :comment, NOW(), :password)';
                $post = $pdo->prepare($sql);
                $post->bindParam(':name', $name, PDO::PARAM_STR);
                $post->bindParam(':comment', $comment, PDO::PARAM_STR);
                $post->bindParam(':password', $pass, PDO::PARAM_STR);
                $post->execute();
                echo "新規投稿が完了しました<br>";
            }
        } else {
            echo "名前とコメントとパスワードを入力してください<br>";
        }
        
        // フォームをリセット
        $name = '';
        $comment = '';
        $pass = '';
        $editpass = '';
        $Edit_number = '';
    }
    
    // 投稿削除処理
    if (isset($_POST['delete'])) {
    
        if (!empty($Del_number) && !empty($delpass)) {
            // 削除対象の投稿番号が存在するか確認
            $sql = 'SELECT * FROM テーブル名 WHERE id = :id AND password = :password';
            $del1 = $pdo->prepare($sql);
            $del1->bindParam(':id', $Del_number, PDO::PARAM_INT);
            $del1->bindParam(':password', $delpass, PDO::PARAM_STR);
            $del1->execute();
            $Del_result = $del1->fetchAll();
    
            if ($Del_result) {
                // 投稿が存在する場合、削除を実行
                $sql = 'DELETE FROM テーブル名 WHERE id = :id AND password = :password';
                $del2 = $pdo->prepare($sql);
                $del2->bindParam(':id', $Del_number, PDO::PARAM_INT);
                $del2->bindParam(':password', $delpass, PDO::PARAM_STR);
                $del2->execute();
                echo "投稿番号{$Del_number}を削除しました<br>";
                
            } else {  //削除対象の投稿番号とパスワードがfalseだった時
                
                    $sql = 'SELECT * FROM テーブル名 WHERE id = :id ';
                    $del2_dash = $pdo->prepare($sql);
                    $del2_dash->bindParam(':id', $Del_number, PDO::PARAM_INT);
                    $del2_dash->execute();
                    $Del_result2 = $del2_dash->fetch();
                        
                        if (!$Del_result2){    //削除対象の投稿番号がなかった場合
                        
                        //削除投稿番号指定・パスワードをリセット
                            $Del_number = '';
                            $Delpass = '';
                        
                            echo "該当する投稿が見つかりませんでした。<br>";
                        }
                    
                        else {   //削除対象の投稿番号はあったが、パスワードが違う場合
                        
                        //削除投稿番号指定・パスワードをリセット
                            $Del_number = '';
                            $Delpass = '';
                        
                            echo "パスワードが間違っています。<br>";
                        }
            }
        } elseif(empty($Del_number) && !empty($delpass)) {
            
            //削除パスワードをリセット
            $delpass = '';
            
            echo "削除投稿番号を入力してください。<br>";
            
        } elseif(!empty($Del_number) && empty($delpass)){
            
            //削除投稿番号をリセット
            $Del_number = '';
            
            echo "削除パスワードを入力してください。<br>";
        }
        else{
            echo "削除投稿番号とパスワードを入力してください。<br>";
        }
        // フォームをリセット
        $Del_number = '';
        $delpass = '';
    }
?>

<!-- フォーム部分 -->
<form action="" method="post">
    <h1>掲示板投稿フォーム</h1><br>
    
    <?php if (!empty($Edit_number)) : ?>
    <!-- 編集中は編集番号を表示するが、編集はできない（readonly） -->
    <input type="text" name="Edit_number_display" value="編集番号: <?php echo htmlspecialchars($Edit_number, ENT_QUOTES, 'UTF-8'); ?>" readonly><br>
    <!-- 編集時以外は編集番号をhiddenとして見えないようにする -->
    <input type="hidden" name="Edit_number" value="<?php echo htmlspecialchars($Edit_number, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <input type="text" name="name" placeholder="名前" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"><br>
    <input type="text" name="comment" placeholder="コメント" value="<?php echo htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'); ?>"><br>
    <input type="password" name="pass" placeholder="パスワードを入力" value="<?php echo htmlspecialchars($pass, ENT_QUOTES, 'UTF-8'); ?>" 
    <?php if(!empty($pass)){    //編集時のみパスワードがreadonly
            echo "readonly";
        }
    ?>><br>
    
    <input type="submit" name="submit" value="送信">
    <br>
    <br>
    
    <b>編集</b><br>
    <input type="number" name="Edit_number" placeholder="編集投稿番号指定" value="<?php echo htmlspecialchars($Edit_number, ENT_QUOTES, 'UTF-8'); ?>"><br>
    <input type="password" name="editpass" placeholder="パスワードを入力" value="<?php echo htmlspecialchars($editpass, ENT_QUOTES, 'UTF-8'); ?>"><br>
    <input type="submit" name="Edit" value="編集">
    <br>
    
    <b>削除</b><br>
    <input type="number" name="Del_number" placeholder="削除番号指定" value="<?php echo htmlspecialchars($Del_number, ENT_QUOTES, 'UTF-8'); ?>"><br>
    <input type="password" name="delpass" placeholder="パスワードを入力" value="<?php echo htmlspecialchars($delpass, ENT_QUOTES, 'UTF-8'); ?>"><br>
    <input type="submit" name="delete" value="削除">
</form>

<hr>

<?php
    // 過去の投稿を表示
    echo "<h2>過去の投稿</h2>";
    $sql = 'SELECT * FROM テーブル名';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    
    if (!empty($results)) {
        echo '<table border="1">';
        echo '<tr><th>投稿番号</th><th>名前</th><th>コメント</th><th>投稿日時</th></tr>';
    
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>'.$row['id'].'</td>';
            echo '<td>'.$row['name'].'</td>';
            echo '<td>'.$row['comment'].'</td>';
            echo '<td>'.$row['date'].'</td>';
            echo '</tr>';
        }
    
        echo '</table>';
    } else {
        echo 'まだ投稿がありません。';
    }
?>
